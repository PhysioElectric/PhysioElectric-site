import http from "node:http";
import { config } from "./config.js";
import { createApp } from "./app.js";
import { attachWs } from "./ws.js";

const app = createApp();
const server = http.createServer(app);
server.headersTimeout = 10_000;
server.requestTimeout = 12_000;
server.keepAliveTimeout = 5_000;
server.maxHeadersCount = 40;

attachWs(server);

server.listen(config.port, config.host, () => {
  console.log(JSON.stringify({
    level: "info",
    msg: "physio-electric lab listening",
    host: config.host,
    port: config.port,
    env: config.isProd ? "production" : "development",
  }));
});

function shutdown(sig) {
  console.log(JSON.stringify({ level: "info", msg: "shutdown", sig }));
  server.close(() => process.exit(0));
  setTimeout(() => process.exit(1), 4000).unref();
}
process.on("SIGINT", () => shutdown("SIGINT"));
process.on("SIGTERM", () => shutdown("SIGTERM"));
