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
const userSessions = new Map<string, string>();
const activeSockets = new Map<string, WebSocket>();

const configuredIdleTimeout = Number(
    process.env.TERMINAL_IDLE_TIMEOUT_MS ?? 900000,
);

const SESSION_IDLE_TIMEOUT_MS =
    Number.isFinite(configuredIdleTimeout) && configuredIdleTimeout > 0
        ? configuredIdleTimeout
        : 900000;

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

async function cleanupIdleSessions(): Promise<void> {
    const now = Date.now();

    for (const [sessionId, session] of sessions) {
        const isConnected = activeSockets.has(session.userId);

        if (isConnected || session.lastDetachedAt === null) {
            continue;
        }

        const idleTime = now - session.lastDetachedAt;

        if (idleTime < SESSION_IDLE_TIMEOUT_MS) {
            continue;
        }

        console.log(
            `Cleaning idle terminal session ${sessionId} for user ${session.userId}`,
        );

        await terminateTerminal(session);

        sessions.delete(sessionId);

        if (userSessions.get(session.userId) === sessionId) {
            userSessions.delete(session.userId);
        }

        const socket = activeSockets.get(session.userId);

        if (socket) {
            activeSockets.delete(session.userId);
        }
    }
}

export function setupWebSocket(server: Server): void {
    const wss = new WebSocketServer({
        server,
        path: "/terminal",
        maxPayload: 1024 * 1024,
    });

    const cleanupInterval = setInterval(() => {
        void cleanupIdleSessions().catch((error) => {
            console.error("Terminal cleanup failed:", error);
        });
    }, 60_000);

    cleanupInterval.unref();

    wss.on("connection", (ws) => {
        let session: TerminalSession | undefined;
        let sessionId: string | undefined;
        let authenticated = false;
        let authenticating = false;
        let activeStream: DockerTerminalStream | undefined;
        let createdSession = false;

        ws.on("message", async (data, isBinary) => {
            try {
                if (!authenticated) {
                    if (authenticating) return;

                    authenticating = true;

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

                    const { payload } = await jwtVerify(
                        message.ticket,
                        secret,
                        {
                            issuer,
                            audience,
                        },
                    );

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

                    const userId = claims.sub;

                    // Reuse this user's existing session, even if an older
                    // ticket contains a different session ID.
                    const existingSessionId = userSessions.get(userId);

                    if (existingSessionId) {
                        const existingSession = sessions.get(existingSessionId);

                        if (existingSession) {
                            session = existingSession;
                            sessionId = existingSessionId;
                        } else {
                            userSessions.delete(userId);
                        }
                    }

                    // Create a container only when the user has no session.
                    if (!session) {
                        sessionId = claims.session_id;

                        session = sessions.get(sessionId);

                        if (session && session.userId !== userId) {
                            throw new Error(
                                "Terminal session ownership mismatch",
                            );
                        }

                        if (!session) {
                            session = await createTerminal(
                                claims.workspace_path,
                                userId,
                                sessionId,
                            );

                            sessions.set(sessionId, session);
                            createdSession = true;
                        }

                        userSessions.set(userId, sessionId);
                    }

                    // A user can have only one active WebSocket connection.
                    const previousSocket = activeSockets.get(userId);

                    if (previousSocket && previousSocket !== ws) {
                        previousSocket.close(
                            4002,
                            "Terminal attached elsewhere",
                        );
                    }

                    if (createdSession) {
                        activeStream = session.stream;
                    } else {
                        session.stream?.destroy();
                        session.stream = undefined;

                        activeStream = await attachTerminal(session);
                    }

                    if (!activeStream) {
                        throw new Error("Unable to attach to Docker terminal");
                    }

                    session.lastDetachedAt = null;

                    activeSockets.set(userId, ws);
                    authenticated = true;
                    authenticating = false;

                    activeStream.on("data", (output: Buffer) => {
                        if (
                            ws.readyState === WebSocket.OPEN &&
                            activeSockets.get(userId) === ws
                        ) {
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

                if (
                    !session ||
                    !sessionId ||
                    !activeStream ||
                    activeSockets.get(session.userId) !== ws
                ) {
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
                        userSessions.delete(session.userId);
                        activeSockets.delete(session.userId);

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

                authenticating = false;

                sendJSON(ws, {
                    type: "error",
                    message: "Terminal operation failed",
                });

                if (!authenticated) {
                    ws.close(4001, "Terminal authentication failed");
                }
            }
        });

        ws.on("close", () => {
            if (activeStream) {
                activeStream.destroy();
            }

            if (
                session &&
                session.stream === activeStream &&
                activeSockets.get(session.userId) === ws
            ) {
                session.stream = undefined;
            }

            if (session && activeSockets.get(session.userId) === ws) {
                activeSockets.delete(session.userId);
                session.lastDetachedAt = Date.now();

                console.log(`Terminal detached for user ${session.userId}`);
            }
        });

        ws.on("error", (error) => {
            console.error("WebSocket connection error:", error);
        });
    });
}
