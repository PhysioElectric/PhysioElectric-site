import helmet from "helmet";
import cors from "cors";
import rateLimit from "express-rate-limit";
import { config } from "../config.js";

const ORIGIN_OK = [
  /^https?:\/\/localhost(?::\d+)?$/i,
  /^https?:\/\/127\.0\.0\.1(?::\d+)?$/i,
  /^https?:\/\/0\.0\.0\.0(?::\d+)?$/i,
  /^https:\/\/[\w.-]+\.e2b\.app$/i,
  /^https:\/\/[\w.-]+\.arena\.ai$/i,
];

export function originAllowed(origin) {
  if (!origin) return true;
  if (config.corsExtra.includes(origin)) return true;
  return ORIGIN_OK.some((re) => re.test(origin));
}

export function corsMiddleware() {
  return cors({
    origin(origin, cb) {
      if (originAllowed(origin)) return cb(null, true);
      cb(new Error("Origin not allowed"));
    },
    credentials: false,
    maxAge: 600,
    methods: ["GET", "POST", "OPTIONS"],
    allowedHeaders: ["Content-Type", "X-Request-Id", "X-Lab-Client"],
  });
}

export function helmetMiddleware() {
  return helmet({
    frameguard: false,
    crossOriginEmbedderPolicy: false,
    crossOriginOpenerPolicy: false,
    crossOriginResourcePolicy: { policy: "cross-origin" },
    contentSecurityPolicy: {
      useDefaults: true,
      directives: {
        "default-src": ["'self'"],
        "script-src": ["'self'"],
        "style-src": ["'self'", "'unsafe-inline'"],
        "img-src": ["'self'", "data:", "blob:"],
        "font-src": ["'self'", "data:"],
        "connect-src": ["'self'", "ws:", "wss:"],
        "media-src": ["'self'"],
        "object-src": ["'none'"],
        "base-uri": ["'self'"],
        "form-action": ["'self'"],
        "frame-ancestors": ["*"],
        "upgrade-insecure-requests": null,
      },
    },
    referrerPolicy: { policy: "strict-origin-when-cross-origin" },
  });
}

export function apiLimiter() {
  return rateLimit({
    windowMs: config.rateWindowMs,
    max: config.rateMax,
    standardHeaders: true,
    legacyHeaders: false,
    message: { error: "rate_limited", message: "Too many requests" },
  });
}

export function computeLimiter() {
  return rateLimit({
    windowMs: config.rateWindowMs,
    max: config.computeRateMax,
    standardHeaders: true,
    legacyHeaders: false,
    message: { error: "compute_rate_limited", message: "Compute budget exceeded" },
  });
}

export function requestId(req, res, next) {
  const incoming = String(req.headers["x-request-id"] || "");
  const id = /^[a-zA-Z0-9_-]{8,64}$/.test(incoming) ? incoming : crypto.randomUUID();
  req.id = id;
  res.setHeader("X-Request-Id", id);
  next();
}

export function noCacheApi(req, res, next) {
  if (req.path.startsWith("/api")) {
    res.setHeader("Cache-Control", "no-store");
    res.setHeader("Pragma", "no-cache");
  }
  next();
}
