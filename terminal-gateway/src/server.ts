import "dotenv/config";

import http from "node:http";
import { setupWebSocket } from "./websocket.js";
import { cleanupOrphanedTerminals } from "./docker.js";

const host = process.env.TERMINAL_HOST ?? "127.0.0.1";
const port = Number(process.env.PORT ?? 8081);

async function startServer() {
    await cleanupOrphanedTerminals();

    const server = http.createServer((req, res) => {
        if (req.url === "/health") {
            res.writeHead(200, {
                "Content-Type": "application/json",
            });

            res.end(JSON.stringify({ status: "ok" }));
            return;
        }

        res.writeHead(404);
        res.end();
    });

    setupWebSocket(server);

    server.listen(port, host, () => {
        console.log(`Terminal gateway listening on ${host}:${port}`);
    });
}

startServer().catch((error) => {
    console.error("Failed to start terminal gateway:", error);
    process.exit(1);
});
