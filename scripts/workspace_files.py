"""Cross-platform trusted file operations.

Uses POSIX directory descriptors and O_NOFOLLOW on Linux/macOS for maximum security,
and falls back to safe path-confinement checks on Windows.
"""
import errno
import json
import os
from pathlib import PurePosixPath
import stat
import sys

IGNORED = {'.git', 'vendor', 'node_modules', '.legacy-imported'}

# Detect if the OS supports directory descriptors (dir_fd)
HAS_DIR_FD = hasattr(os, 'supports_dir_fd') and os.supports_dir_fd

O_DIRECTORY = getattr(os, 'O_DIRECTORY', 0)
O_NOFOLLOW = getattr(os, 'O_NOFOLLOW', 0)
O_NONBLOCK = getattr(os, 'O_NONBLOCK', 0)
DIRECTORY_FLAGS = os.O_RDONLY | O_DIRECTORY | O_NOFOLLOW


def validate_path(path, action):
    """Safely validate and split relative paths."""
    path = path.replace('\\', '/')
    if path == '':
        if action != 'tree':
            raise ValueError('Invalid relative path.')
        return []

    if path.startswith('/') or '\0' in path:
        raise ValueError('Invalid relative path.')

    parts = path.split('/')
    for part in parts:
        if part in ('', '.', '..'):
            raise ValueError('Invalid relative path.')
    return parts


# ==========================================
# WINDOWS / PATH-BASED FALLBACK IMPLEMENTATION
# ==========================================
def _safe_join(root, parts):
    resolved = os.path.abspath(root)
    for part in parts:
        resolved = os.path.join(resolved, part)
    if not os.path.abspath(resolved).startswith(os.path.abspath(root)):
        raise ValueError('Invalid relative path.')
    return resolved


def _windows_tree(root_dir, current_dir, prefix='', depth=0):
    if depth > 5:
        return []
    result = []
    try:
        entries = sorted(os.listdir(current_dir), key=str.lower)[:2000]
    except OSError:
        return []

    for name in entries:
        if name in IGNORED:
            continue
        full_path = os.path.join(current_dir, name)
        try:
            info = os.lstat(full_path)
        except OSError:
            continue

        if not (stat.S_ISDIR(info.st_mode) or stat.S_ISREG(info.st_mode)):
            continue

        path = prefix + name
        item = dict(
            name=name,
            path=path,
            isDir=stat.S_ISDIR(info.st_mode),
            extension=PurePosixPath(name).suffix.lstrip('.')
        )
        if item['isDir']:
            item['children'] = _windows_tree(root_dir, full_path, path + '/', depth + 1)
        else:
            item['size'] = info.st_size
        result.append(item)
    return sorted(result, key=lambda item: (not item['isDir'], item['name'].lower()))


def _windows_remove(full_path):
    if os.path.isdir(full_path) and not os.path.islink(full_path):
        for entry in os.listdir(full_path):
            _windows_remove(os.path.join(full_path, entry))
        os.rmdir(full_path)
    else:
        os.unlink(full_path)


def operate_windows(request):
    root = request['root']
    action = request['action']
    parts = validate_path(request.get('path', ''), action)
    full_path = _safe_join(root, parts)
    rel_path = '/'.join(parts)

    if action == 'tree':
        target_dir = root if not parts else full_path
        return dict(root='My workspace', currentPath='/' + rel_path, tree=_windows_tree(root, target_dir, rel_path + '/' if rel_path else ''))

    if not parts:
        raise ValueError('Workspace root cannot be changed.')

    parent_dir = os.path.dirname(full_path)

    if action == 'delete':
        _windows_remove(full_path)
        return dict(path=rel_path)

    if action == 'create':
        os.makedirs(parent_dir, exist_ok=True)
        if request.get('type') == 'directory':
            os.makedirs(full_path, exist_ok=True)
            return dict(ok=True, path=rel_path)
        else:
            with open(full_path, 'x', encoding='utf-8') as f:
                f.write(request.get('content', ''))
            info = os.stat(full_path)
            return dict(ok=True, path=rel_path, lastModified=int(info.st_mtime))

    if action == 'save':
        os.makedirs(parent_dir, exist_ok=True)
        with open(full_path, 'w', encoding='utf-8') as f:
            f.write(request.get('content', ''))
        info = os.stat(full_path)
        return dict(ok=True, path=rel_path, lastModified=int(info.st_mtime))

    if action == 'read':
        if not os.path.exists(full_path) or not os.path.isfile(full_path):
            raise FileNotFoundError('File not found.')
        info = os.stat(full_path)
        if info.st_size > 1_000_000:
            raise ValueError('File is too large for the editor (1 MB maximum).')
        with open(full_path, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read(1_000_001)
        name = os.path.basename(full_path)
        return dict(
            path=rel_path,
            filename=name,
            extension=PurePosixPath(name).suffix.lstrip('.'),
            content=content,
            size=info.st_size,
            lastModified=int(info.st_mtime)
        )

    raise ValueError(f'Unknown action: {action}')


# ==========================================
# LINUX / POSIX DIR_FD IMPLEMENTATION
# ==========================================
def linux_parent(root, parts, create=False):
    current = os.dup(root)
    try:
        for part in parts[:-1]:
            if create:
                try:
                    os.mkdir(part, mode=0o700, dir_fd=current)
                except FileExistsError:
                    pass
            child = os.open(part, DIRECTORY_FLAGS, dir_fd=current)
            os.close(current)
            current = child
        return current
    except Exception:
        os.close(current)
        raise


def linux_tree(directory, prefix='', depth=0):
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
                child = os.open(name, DIRECTORY_FLAGS, dir_fd=directory)
            except OSError:
                continue
            try:
                item['children'] = linux_tree(child, path + '/', depth + 1)
            finally:
                os.close(child)
        else:
            item['size'] = info.st_size
        result.append(item)
    return sorted(result, key=lambda item: (not item['isDir'], item['name'].lower()))


def linux_remove(directory, name):
    info = os.stat(name, dir_fd=directory, follow_symlinks=False)
    if stat.S_ISDIR(info.st_mode):
        child = os.open(name, DIRECTORY_FLAGS, dir_fd=directory)
        try:
            for entry in os.listdir(child):
                linux_remove(child, entry)
        finally:
            os.close(child)
        os.rmdir(name, dir_fd=directory)
    else:
        os.unlink(name, dir_fd=directory)


def operate_linux(request):
    action = request['action']
    parts = validate_path(request.get('path', ''), action)
    path = '/'.join(parts)

    root = os.open(request['root'], DIRECTORY_FLAGS)
    try:
        if action == 'tree':
            directory = root if not parts else linux_parent(root, parts + ['.'])
            try:
                return dict(root='My workspace', currentPath='/' + path, tree=linux_tree(directory, path + '/' if path else ''))
            finally:
                if directory != root:
                    os.close(directory)
        if not parts:
            raise ValueError('Workspace root cannot be changed.')
        directory = linux_parent(root, parts, action in ('save', 'create'))
        try:
            name = parts[-1]
            if action == 'delete':
                linux_remove(directory, name)
                return dict(path=path)
            if action == 'create' and request.get('type') == 'directory':
                os.mkdir(name, mode=0o700, dir_fd=directory)
                return dict(ok=True, path=path)
            flags = O_NOFOLLOW | O_NONBLOCK
            if action == 'read':
                flags |= os.O_RDONLY
            else:
                flags |= os.O_WRONLY | os.O_CREAT
                if action == 'create':
                    flags |= os.O_EXCL
            descriptor = os.open(name, flags, 0o600, dir_fd=directory)
            with os.fdopen(descriptor, 'rb' if action == 'read' else 'wb') as file:
                info = os.fstat(file.fileno())
                if not stat.S_ISREG(info.st_mode):
                    raise ValueError('Only regular files are supported.')
                if action == 'read':
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


# ==========================================
# MAIN ENTRY POINT
# ==========================================
def operate(request):
    if HAS_DIR_FD and os.name != 'nt':
        return operate_linux(request)
    else:
        return operate_windows(request)


if __name__ == '__main__':
    try:
        print(json.dumps(dict(success=True, **operate(json.load(sys.stdin)))))
    except (OSError, ValueError) as error:
        status = 404 if isinstance(error, FileNotFoundError) else 409 if isinstance(error, FileExistsError) else 403 if isinstance(error, OSError) and error.errno in (errno.ELOOP, errno.ENOTDIR, errno.EACCES) else 422
        print(json.dumps(dict(success=False, status=status, error='File operation denied.' if status == 403 else str(error))))
