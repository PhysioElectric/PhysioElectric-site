import { spawn } from "node:child_process";

const procs = [
  spawn("node", ["--watch", "server/index.js"], { stdio: "inherit", env: process.env }),
  spawn("npx", ["vite", "--host", "0.0.0.0", "--port", "5173"], { stdio: "inherit", env: process.env }),
];

function die(code) {
  for (const p of procs) {
    try { p.kill("SIGTERM"); } catch { /* */ }
  }
  process.exit(code ?? 0);
}
process.on("SIGINT", () => die(0));
process.on("SIGTERM", () => die(0));
for (const p of procs) p.on("exit", (c) => die(c ?? 0));
