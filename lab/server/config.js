const isProd = process.env.NODE_ENV === "production";

function intEnv(name, fallback) {
  const raw = process.env[name];
  if (raw == null || raw === "") return fallback;
  const n = Number.parseInt(raw, 10);
  return Number.isFinite(n) ? n : fallback;
}

export const config = {
  isProd,
  port: intEnv("PORT", 8787),
  host: process.env.HOST || "0.0.0.0",
  requestBytes: intEnv("REQUEST_BYTES", 32 * 1024),
  jsonLimit: process.env.JSON_LIMIT || "32kb",
  rateWindowMs: intEnv("RATE_WINDOW_MS", 60_000),
  rateMax: intEnv("RATE_MAX", 120),
  computeRateMax: intEnv("COMPUTE_RATE_MAX", 40),
  wsMaxPerIp: intEnv("WS_MAX_PER_IP", 3),
  computeBudgetMs: intEnv("COMPUTE_BUDGET_MS", 250),
  corsExtra: (process.env.CORS_ORIGINS || "")
    .split(",")
    .map((s) => s.trim())
    .filter(Boolean),
};
