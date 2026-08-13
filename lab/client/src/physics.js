export function clamp(n, lo, hi) {
  return Math.min(hi, Math.max(lo, n));
}

export function lerp(a, b, t) {
  return a + (b - a) * t;
}

export function tempPalette(t, lo = 18, hi = 90) {
  const u = clamp((t - lo) / (hi - lo), 0, 1);
  if (u < 0.25) return mix("#3d7cff", "#3ce0c8", u / 0.25);
  if (u < 0.5) return mix("#3ce0c8", "#d9f36a", (u - 0.25) / 0.25);
  if (u < 0.75) return mix("#d9f36a", "#ff9d4a", (u - 0.5) / 0.25);
  return mix("#ff9d4a", "#ff3b4a", (u - 0.75) / 0.25);
}

function hexToRgb(h) {
  const n = h.replace("#", "");
  return [parseInt(n.slice(0, 2), 16), parseInt(n.slice(2, 4), 16), parseInt(n.slice(4, 6), 16)];
}

export function mix(a, b, t) {
  const A = hexToRgb(a);
  const B = hexToRgb(b);
  const r = Math.round(lerp(A[0], B[0], t));
  const g = Math.round(lerp(A[1], B[1], t));
  const bl = Math.round(lerp(A[2], B[2], t));
  return `rgb(${r},${g},${bl})`;
}

export function turbo(u) {
  const x = clamp(u, 0, 1);
  const r = clamp(0.135 + 4.6 * x - 6.8 * x * x + 3.1 * x * x * x, 0, 1);
  const g = clamp(0.09 + 2.2 * x - 1.4 * x * x - 0.6 * x * x * x, 0, 1);
  const b = clamp(0.55 + 0.4 * Math.sin(x * 4.2) - 1.1 * x * x, 0, 1);
  return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

export function inferno(u) {
  const x = clamp(u, 0, 1);
  const r = clamp(-0.05 + 2.8 * x - 1.6 * x * x, 0, 1);
  const g = clamp(-0.15 + 0.4 * x + 1.7 * x * x - 1.1 * x * x * x, 0, 1);
  const b = clamp(0.02 + 1.6 * x - 3.4 * x * x + 1.9 * x * x * x, 0, 1);
  return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

export function viridis(u) {
  const x = clamp(u, 0, 1);
  const r = clamp(0.27 + x * -0.1 + x * x * 0.95, 0, 1);
  const g = clamp(0.0 + 1.4 * x - 0.55 * x * x, 0, 1);
  const b = clamp(0.33 + 0.7 * x - 1.15 * x * x, 0, 1);
  return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

const ALPHA = {
  copper: 1.17e-4,
  aluminum: 9.7e-5,
  silicon: 8.8e-5,
  steel: 1.17e-5,
  glass: 3.4e-7,
  air: 2.2e-5,
  fr4: 2.0e-7,
};

export function makeGrid(n, fill) {
  return new Float32Array(n * n).fill(fill);
}

export function stepHeat(field, n, { ambient, material, scale, sources, steps = 2 }) {
  const alpha0 = (ALPHA[material] || ALPHA.copper) * scale * 18000;
  const dx = 1 / n;
  const dt = (0.2 * dx * dx) / Math.max(alpha0, 1e-9);
  let cur = field;
  let next = new Float32Array(n * n);
  for (let s = 0; s < steps; s++) {
    for (let i = 0; i < n; i++) {
      for (let j = 0; j < n; j++) {
        const id = i * n + j;
        if (i === 0 || j === 0 || i === n - 1 || j === n - 1) {
          next[id] = ambient;
          continue;
        }
        const c = cur[id];
        const lap = cur[id + n] + cur[id - n] + cur[id + 1] + cur[id - 1] - 4 * c;
        next[id] = c + (alpha0 * dt * lap) / (dx * dx);
      }
    }
    for (const src of sources) {
      const si = Math.round(src.x * (n - 1));
      const sj = Math.round(src.y * (n - 1));
      const rad = src.radius || 0.07;
      const rPix = Math.max(1, Math.round(rad * n));
      for (let i = Math.max(1, si - rPix); i <= Math.min(n - 2, si + rPix); i++) {
        for (let j = Math.max(1, sj - rPix); j <= Math.min(n - 2, sj + rPix); j++) {
          const d = Math.hypot((i - si) / n, (j - sj) / n);
          if (d <= rad) {
            const w = Math.exp((-4 * d * d) / (rad * rad));
            next[i * n + j] += src.power * w * dt * 55;
          }
        }
      }
    }
    const tmp = cur;
    cur = next;
    next = tmp;
  }
  return cur;
}

export function stepWave(u, prev, n, { c, damp, sources, t }) {
  const next = new Float32Array(n * n);
  const c2 = c * c;
  for (let i = 0; i < n; i++) {
    for (let j = 0; j < n; j++) {
      const id = i * n + j;
      if (i === 0 || j === 0 || i === n - 1 || j === n - 1) {
        next[id] = 0;
        continue;
      }
      const lap = u[id - n] + u[id + n] + u[id - 1] + u[id + 1] - 4 * u[id];
      next[id] = (2 - damp) * u[id] - (1 - damp) * prev[id] + c2 * lap;
    }
  }
  for (const src of sources) {
    const i = Math.round(src.x * (n - 1));
    const j = Math.round(src.y * (n - 1));
    next[i * n + j] += src.amp * Math.sin(2 * Math.PI * src.freq * t + (src.phase || 0));
  }
  return next;
}

export function distanceToRect(x, y, rect) {
  const cx = clamp(x, rect.left, rect.right);
  const cy = clamp(y, rect.top, rect.bottom);
  return Math.hypot(x - cx, y - cy);
}

export function sensorThermalLocal({ ambient = 24.5, temp, humidity = 42, dt = 0.08, tau = 1.35, tools = [] }) {
  let coupling = 0;
  let targetBoost = 0;
  for (const tool of tools) {
    const d = Math.max(0.004, tool.distance || 1);
    const intensity = clamp(tool.intensity ?? 0, 0, 1);
    const kind = tool.kind || "flame";
    const tSrc = kind === "flame" ? 420 : kind === "heatgun" ? 260 : kind === "iron" ? 330 : kind === "hand" ? 34.5 : 80;
    const reach = kind === "hand" ? 0.09 : kind === "heatgun" ? 0.22 : 0.16;
    const w = intensity * Math.exp(-d / reach);
    coupling += w;
    targetBoost += w * (tSrc - ambient);
  }
  const Tinf = ambient + targetBoost / (1 + coupling * 0.15);
  const next = temp + (Tinf - temp) * (1 - Math.exp(-dt / tau));
  const noise = (Math.random() - 0.5) * (0.04 + coupling * 0.08);
  const rhTarget = humidity - Math.max(0, next - ambient) * 0.35;
  const rh = humidity + (clamp(rhTarget, 8, 98) - humidity) * 0.08;
  return {
    temp: clamp(next + noise, -30, 180),
    humidity: clamp(rh + (Math.random() - 0.5) * 0.12, 5, 99),
    coupling,
  };
}
