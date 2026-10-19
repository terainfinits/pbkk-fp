"""Trusted file operations using directory descriptors and O_NOFOLLOW.

An executing program may rename files concurrently. Keeping parent directories
open prevents a symlink swap from redirecting a PHP file operation outside its root.
"""
import errno
import json
import os
from pathlib import PurePosixPath
import stat
import sys

DIRECTORY = os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW
IGNORED = {'.git', 'vendor', 'node_modules', '.legacy-imported'}


def parent(root, parts, create=False):
    current = os.dup(root)
    try:
        for part in parts[:-1]:
            if create:
                try:
                    os.mkdir(part, mode=0o700, dir_fd=current)
                except FileExistsError:
                    pass
            child = os.open(part, DIRECTORY, dir_fd=current)
            os.close(current)
            current = child
        return current
    except Exception:
        os.close(current)
        raise


def tree(directory, prefix='', depth=0):
    if depth > 5:
        return []
    result = []
    for name in sorted(os.listdir(directory), key=str.lower)[:2000]:
        if name in IGNORED:
            continue
        info = os.stat(name, dir_fd=directory, follow_symlinks=False)
        if not (stat.S_ISDIR(info.st_mode) or stat.S_ISREG(info.st_mode)):
            continue
        path = prefix + name
        item = dict(name=name, path=path, isDir=stat.S_ISDIR(info.st_mode), extension=PurePosixPath(name).suffix.lstrip('.'))
        if item['isDir']:
            try:
                child = os.open(name, DIRECTORY, dir_fd=directory)
            except OSError:
                continue
            try:
                item['children'] = tree(child, path + '/', depth + 1)
            finally:
                os.close(child)
        else:
            item['size'] = info.st_size
        result.append(item)
    return sorted(result, key=lambda item: (not item['isDir'], item['name'].lower()))


def remove(directory, name):
    info = os.stat(name, dir_fd=directory, follow_symlinks=False)
    if stat.S_ISDIR(info.st_mode):
        child = os.open(name, DIRECTORY, dir_fd=directory)
        try:
            for entry in os.listdir(child):
                remove(child, entry)
        finally:
            os.close(child)
        os.rmdir(name, dir_fd=directory)
    else:
        os.unlink(name, dir_fd=directory)


def operate(request):
    path = request['path'].replace('\\', '/')
    parts = path.split('/') if path else []
    if any(part in ('', '.', '..') for part in parts) or path.startswith('/') or '\0' in path:
        raise ValueError('Invalid relative path.')
    root = os.open(request['root'], DIRECTORY)
    try:
        if request['action'] == 'tree':
            directory = root if not parts else parent(root, parts + ['.'])
            try:
                return dict(root='My workspace', currentPath='/' + path, tree=tree(directory, path + '/' if path else ''))
            finally:
                if directory != root:
                    os.close(directory)
        if not parts:
            raise ValueError('Workspace root cannot be changed.')
        directory = parent(root, parts, request['action'] in ('save', 'create'))
        try:
            name = parts[-1]
            if request['action'] == 'delete':
                remove(directory, name)
                return dict(path=path)
            if request['action'] == 'create' and request.get('type') == 'directory':
                os.mkdir(name, mode=0o700, dir_fd=directory)
                return dict(ok=True, path=path)
            flags = os.O_NOFOLLOW | os.O_NONBLOCK
            if request['action'] == 'read':
                flags |= os.O_RDONLY
            else:
                flags |= os.O_WRONLY | os.O_CREAT
                if request['action'] == 'create':
                    flags |= os.O_EXCL
            descriptor = os.open(name, flags, 0o600, dir_fd=directory)
            with os.fdopen(descriptor, 'rb' if request['action'] == 'read' else 'wb') as file:
                info = os.fstat(file.fileno())
                if not stat.S_ISREG(info.st_mode):
                    raise ValueError('Only regular files are supported.')
                if request['action'] == 'read':
                    if info.st_size > 1_000_000:
                        raise ValueError('File is too large for the editor (1 MB maximum).')
                    content = file.read(1_000_001).decode('utf-8', errors='replace')
                    return dict(path=path, filename=name, extension=PurePosixPath(name).suffix.lstrip('.'), content=content, size=info.st_size, lastModified=int(info.st_mtime))
                file.truncate(0)
                file.write(request.get('content', '').encode('utf-8'))
            return dict(ok=True, path=path, lastModified=int(os.stat(name, dir_fd=directory, follow_symlinks=False).st_mtime))
        finally:
            os.close(directory)
    finally:
        os.close(root)


if __name__ == '__main__':
    try:
        print(json.dumps(dict(success=True, **operate(json.load(sys.stdin)))))
    except (OSError, ValueError) as error:
        status = 404 if isinstance(error, FileNotFoundError) else 409 if isinstance(error, FileExistsError) else 403 if isinstance(error, OSError) and error.errno in (errno.ELOOP, errno.ENOTDIR, errno.EACCES) else 422
        print(json.dumps(dict(success=False, status=status, error='File operation denied.' if status == 403 else str(error))))
