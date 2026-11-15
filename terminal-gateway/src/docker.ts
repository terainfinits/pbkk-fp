import Docker from "dockerode";
import fs from "node:fs";

const docker =
  process.platform === "win32"
    ? new Docker({ socketPath: "//./pipe/docker_engine" })
    : new Docker({
        socketPath: process.env.DOCKER_SOCKET ?? "/var/run/docker.sock",
      });

const image = process.env.DOCKER_IMAGE ?? "sandbox-runner:latest";

const TERMINAL_LABEL = "com.laravel-ide.terminal";

export type DockerTerminalStream = NodeJS.ReadWriteStream & {
  destroy(): void;
};

export interface TerminalSession {
  container: Docker.Container;
  stream?: DockerTerminalStream;
  workspacePath: string;
  userId: string;
  sessionId: string;
  lastDetachedAt: number | null;
}

function validateWorkspacePath(input: string): string {
  if (!input) {
    throw new Error("Workspace path is required");
  }

  if (!fs.existsSync(input)) {
    throw new Error("Workspace directory does not exist");
  }

  const resolved = fs.realpathSync(input);

  if (!fs.statSync(resolved).isDirectory()) {
    throw new Error("Workspace path is not a directory");
  }

  return resolved;
}

export async function createTerminal(
  inputWorkspacePath: string,
  userId: string,
  sessionId: string
): Promise<TerminalSession> {
  const workspacePath = validateWorkspacePath(inputWorkspacePath);

  const container = await docker.createContainer({
    Image: image,
    Cmd: ["sh"],
    WorkingDir: "/workspace",

    Tty: true,
    OpenStdin: true,
    StdinOnce: false,
    AttachStdin: true,
    AttachStdout: true,
    AttachStderr: true,

    Labels: {
      [TERMINAL_LABEL]: "true",
      "com.laravel-ide.user-id": userId,
      "com.laravel-ide.session-id": sessionId,
    },

    HostConfig: {
      NetworkMode: "none",

      Memory: 258 * 1024 * 1024,
      NanoCpus: 500_000_000,
      PidsLimit: 64,

      CapDrop: ["ALL"],
      SecurityOpt: ["no-new-privileges"],

      Mounts: [
        {
          Type: "bind",
          Source: workspacePath,
          Target: "/workspace",
        },
      ],
    },
  });

  try {
    const stream = (await container.attach({
      stream: true,
      stdin: true,
      stdout: true,
      stderr: true,
      hijack: true,
    })) as DockerTerminalStream;

    await container.start();

    return {
      container,
      stream,
      workspacePath,
      userId,
      sessionId,
      lastDetachedAt: null,
    };
  } catch (error) {
    try {
      await container.remove({ force: true });
    } catch {
      // Ignore cleanup errors.
    }

    throw error;
  }
}

export async function attachTerminal(
  session: TerminalSession
): Promise<DockerTerminalStream> {
  const stream = (await session.container.attach({
    stream: true,
    stdin: true,
    stdout: true,
    stderr: true,
    hijack: true,
  })) as DockerTerminalStream;

  session.stream = stream;

  return stream;
}

export async function resizeTerminal(
  container: Docker.Container,
  cols: number,
  rows: number
): Promise<void> {
  if (
    !Number.isInteger(cols) ||
    !Number.isInteger(rows) ||
    cols < 1 ||
    rows < 1 ||
    cols > 500 ||
    rows > 300
  ) {
    throw new Error("Invalid terminal dimensions");
  }

  await container.resize({
    w: cols,
    h: rows,
  });
}

export async function terminateTerminal(
  session: TerminalSession
): Promise<void> {
  session.stream?.destroy();
  session.stream = undefined;

  try {
    await session.container.stop({ t: 2 });
  } catch {
    // Ignore stop errors.
  }

  try {
    await session.container.remove({ force: true });
  } catch {
    // Ignore removal errors.
  }
}

export async function cleanupOrphanedTerminals(): Promise<void> {
  const containers = await docker.listContainers({
    all: true,
    filters: JSON.stringify({
      label: [`${TERMINAL_LABEL}=true`],
    }),
  });

  for (const info of containers) {
    try {
      const container = docker.getContainer(info.Id);

      await container.remove({ force: true });

      console.log(`Removed orphaned terminal container: ${info.Id}`);
    } catch (error) {
      console.error(
        `Failed to remove orphaned container ${info.Id}:`,
        error
      );
    }
  }
}
