import express from "express";
import compression from "compression";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { config } from "./config.js";
import {
  helmetMiddleware,
  corsMiddleware,
  apiLimiter,
  requestId,
  noCacheApi,
} from "./middleware/security.js";
import { errorHandler } from "./middleware/errors.js";
import api from "./routes/api.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dist = path.resolve(__dirname, "../dist");

export function createApp() {
  const app = express();
  app.disable("x-powered-by");
  app.set("trust proxy", 1);

  app.use(requestId);
  app.use(helmetMiddleware());
  app.use(corsMiddleware());
  app.use(compression());
  app.use(express.json({ limit: config.jsonLimit, strict: true }));
  app.use((req, res, next) => {
    if (req.method === "POST" && req.headers["content-type"] && !/application\/json/i.test(String(req.headers["content-type"]))) {
      return res.status(415).json({ error: "unsupported_media", requestId: req.id });
    }
    next();
  });
  app.use(noCacheApi);

  app.use("/api", apiLimiter(), api);

  if (config.isProd) {
    const siteRoot = path.resolve(__dirname, "../..");
    // Serve the surrounding Sampatec pages (index/page3) plus the lab build.
    app.use(express.static(siteRoot, { maxAge: "1h", extensions: ["html"] }));
    app.use("/lab", express.static(dist, { maxAge: "1h", extensions: ["html"] }));
    app.use(express.static(dist, { maxAge: "1h", extensions: ["html"] }));
    app.get(["/lab", "/lab/*"], (req, res, next) => {
      res.sendFile(path.join(dist, "index.html"), (err) => (err ? next(err) : undefined));
    });
    app.get("*", (req, res, next) => {
      if (req.path.startsWith("/api") || req.path.startsWith("/ws")) return next();
      if (req.path.startsWith("/lab")) {
        return res.sendFile(path.join(dist, "index.html"), (err) => (err ? next(err) : undefined));
      }
      const siteIndex = path.join(siteRoot, "index.html");
      res.sendFile(siteIndex, (err) => (err ? next(err) : undefined));
    });
  }

  app.use(errorHandler);
  return app;
}
