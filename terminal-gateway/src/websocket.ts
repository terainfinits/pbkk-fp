
import type { Server } from "node:http";
import { WebSocket, WebSocketServer } from "ws";
import { jwtVerify } from "jose";

import {
  attachTerminal,
  createTerminal,
  resizeTerminal,
  terminateTerminal,
  type TerminalSession,
  type DockerTerminalStream,
} from "./docker.js";

const sessions = new Map<string, TerminalSession>();

const jwtSecret = process.env.JWT_SECRET;

if (!jwtSecret) {
  throw new Error("JWT_SECRET is required");
}

const secret = new TextEncoder().encode(jwtSecret);

const issuer = process.env.JWT_ISSUER ?? "laravel-ide";
const audience = process.env.JWT_AUDIENCE ?? "terminal-gateway";

interface TicketPayload {
  sub: string;
  session_id: string;
  workspace_path: string;
  jti: string;
}

function sendJSON(ws: WebSocket, data: object): void {
  if (ws.readyState === WebSocket.OPEN) {
    ws.send(JSON.stringify(data));
  }
}

export function setupWebSocket(server: Server): void {
  const wss = new WebSocketServer({
    server,
    path: "/terminal",
    maxPayload: 1024 * 1024,
  });

  wss.on("connection", (ws) => {
    let session: TerminalSession | undefined;
    let sessionId: string | undefined;
    let authenticated = false;
    let activeStream: DockerTerminalStream | undefined;

    ws.on("message", async (data, isBinary) => {
      try {
        if (!authenticated) {
          if (isBinary) {
            ws.close(4001, "Authentication required");
            return;
          }

          const message = JSON.parse(data.toString());

          if (
            message.type !== "auth" ||
            typeof message.ticket !== "string"
          ) {
            ws.close(4001, "Invalid authentication message");
            return;
          }

          const { payload } = await jwtVerify(message.ticket, secret, {
            issuer,
            audience,
          });

          const claims = payload as typeof payload & TicketPayload;

          if (
            typeof claims.session_id !== "string" ||
            typeof claims.sub !== "string" ||
            typeof claims.workspace_path !== "string" ||
            typeof claims.jti !== "string"
          ) {
            ws.close(4001, "Invalid ticket claims");
            return;
          }

          sessionId = claims.session_id;

          session = sessions.get(sessionId);

          if (!session) {
            session = await createTerminal(claims.workspace_path);
            sessions.set(sessionId, session);
          } else {
            // Close the previous Docker attachment, not the container.
            session.stream?.destroy();
            session.stream = undefined;

            await attachTerminal(session);
          }

          authenticated = true;

          activeStream = session.stream!;

          activeStream.on("data", (output: Buffer) => {
            if (ws.readyState === WebSocket.OPEN) {
              ws.send(output, { binary: true });
            }
          });

          activeStream.on("end", () => {
            sendJSON(ws, { type: "exit" });
          });

          activeStream.on("error", () => {
            sendJSON(ws, {
              type: "error",
              message: "Docker terminal stream failed",
            });
          });

          sendJSON(ws, {
            type: "attached",
            sessionId,
          });

          return;
        }

        if (!session || !sessionId || !activeStream) {
          return;
        }

        if (isBinary) {
          const input = Buffer.isBuffer(data)
            ? data
            : Array.isArray(data)
              ? Buffer.concat(data)
              : Buffer.from(data);

          activeStream.write(input);
          return;
        }

        const message = JSON.parse(data.toString());

        switch (message.type) {
          case "resize": {
            const cols = Number(message.cols);
            const rows = Number(message.rows);

            if (
              Number.isInteger(cols) &&
              Number.isInteger(rows) &&
              cols >= 1 &&
              rows >= 1 &&
              cols <= 500 &&
              rows <= 300
            ) {
              await resizeTerminal(session.container, cols, rows);
            }

            break;
          }

          case "terminate": {
            await terminateTerminal(session);
            sessions.delete(sessionId);

            session = undefined;
            activeStream = undefined;

            sendJSON(ws, { type: "terminated" });
            ws.close();

            break;
          }

          case "detach": {
            ws.close();
            break;
          }

          case "ping": {
            sendJSON(ws, { type: "pong" });
            break;
          }
        }
      } catch (error) {
        console.error("Terminal WebSocket error:", error);

        sendJSON(ws, {
          type: "error",
          message: "Terminal operation failed",
        });
      }
    });

    ws.on("close", () => {
      // Preserve the Docker container for a later reconnect.
      activeStream?.destroy();

      // Only clear the session stream if it still belongs to this connection.
      if (session && session.stream === activeStream) {
        session.stream = undefined;
      }
    });
  });
}
