import { WebSocketServer } from "ws";
import { config } from "./config.js";
import { originAllowed } from "./middleware/security.js";
import { VirtualBoard } from "./services/iotDevice.js";

const ipCount = new Map();

function clientIp(req) {
  const xf = String(req.headers["x-forwarded-for"] || "").split(",")[0].trim();
  return xf || req.socket.remoteAddress || "unknown";
}

export function attachWs(server) {
  const wss = new WebSocketServer({
    server,
    path: "/ws/lab",
    maxPayload: 4096,
    perMessageDeflate: false,
  });

  wss.on("connection", (ws, req) => {
    const origin = req.headers.origin;
    if (origin && !originAllowed(origin)) {
      ws.close(1008, "origin");
      return;
    }
    const ip = clientIp(req);
    const n = (ipCount.get(ip) || 0) + 1;
    if (n > config.wsMaxPerIp) {
      ws.close(1008, "busy");
      return;
    }
    ipCount.set(ip, n);

    const url = new URL(req.url, "http://localhost");
    const board = url.searchParams.get("board") === "stm32" ? "stm32" : "esp32";
    const device = new VirtualBoard(board);
    let alive = true;
    let lastMsg = Date.now();

    const timer = setInterval(() => {
      if (!alive || ws.readyState !== ws.OPEN) return;
      if (Date.now() - lastMsg > 45000) {
        ws.close(1000, "idle");
        return;
      }
      try {
        ws.send(JSON.stringify({ type: "telemetry", ...device.tick(0.1) }));
      } catch {
        /* ignore */
      }
    }, 100);

    ws.on("message", (buf) => {
      lastMsg = Date.now();
      if (buf.length > 4096) return;
      let msg;
      try {
        msg = JSON.parse(String(buf));
      } catch {
        return;
      }
      if (!msg || typeof msg !== "object" || typeof msg.type !== "string") return;
      if (msg.type === "ping") {
        ws.send(JSON.stringify({ type: "pong", t: Date.now() }));
        return;
      }
      device.applyClient(msg);
    });

    const cleanup = () => {
      if (!alive) return;
      alive = false;
      clearInterval(timer);
      ipCount.set(ip, Math.max(0, (ipCount.get(ip) || 1) - 1));
    };
    ws.on("close", cleanup);
    ws.on("error", cleanup);

    ws.send(JSON.stringify({ type: "hello", board, firmware: "physio-lab/1.4.2" }));
  });

  return wss;
}
