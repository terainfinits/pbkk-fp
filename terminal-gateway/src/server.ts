import "dotenv/config";

import { createServer } from "node:http";
import { setupWebSocket } from "./websocket.js";

const port = Number(process.env.TERMINAL_PORT ?? 8081);
const host = process.env.TERMINAL_HOST ?? "127.0.0.1";

const server = createServer((req, res) => {
  if (req.url === "/health") {
    res.writeHead(200, {
      "Content-Type": "application/json",
    });

    res.end(JSON.stringify({ status: "ok" }));
    return;
  }

  res.writeHead(404);
  res.end("Not found");
});

setupWebSocket(server);

server.listen(port, host, () => {
  console.log(`Terminal gateway listening on ${host}:${port}`);
});
