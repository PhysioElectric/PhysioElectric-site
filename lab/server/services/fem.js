import { clamp, finite } from "./math.js";

const MATERIALS = {
  copper: { alpha: 1.17e-4, k: 400, name: "Copper" },
  aluminum: { alpha: 9.7e-5, k: 237, name: "Aluminum" },
  silicon: { alpha: 8.8e-5, k: 148, name: "Silicon" },
  steel: { alpha: 1.17e-5, k: 45, name: "Steel" },
  glass: { alpha: 3.4e-7, k: 1.0, name: "Glass" },
  air: { alpha: 2.2e-5, k: 0.026, name: "Air" },
  fr4: { alpha: 2.0e-7, k: 0.3, name: "FR-4" },
};

function idx(i, j, ny) {
  return i * ny + j;
}

export function heatStep(input, overtime = () => false) {
  const nx = clamp(Math.round(finite(input.nx, 48)), 16, 80);
  const ny = clamp(Math.round(finite(input.ny, 48)), 16, 80);
  const steps = clamp(Math.round(finite(input.steps, 8)), 1, 24);
  const material = MATERIALS[input.material] || MATERIALS.copper;
  const scale = clamp(finite(input.diffusivity, 1), 0.05, 8);
  const amb = clamp(finite(input.ambient, 25), -20, 80);
  const sources = Array.isArray(input.sources) ? input.sources.slice(0, 8) : [];

  let field;
  if (Array.isArray(input.field) && input.field.length === nx * ny) {
    field = input.field.map((v) => clamp(finite(v, amb), -50, 1200));
  } else {
    field = new Array(nx * ny).fill(amb);
  }

  // Explicit FTCS on unit square. dx=1/nx. Choose dt for stability: Fo <= 0.24
  const dx = 1 / Math.max(nx, ny);
  const alpha = material.alpha * scale * 18000;
  const dt = (0.22 * dx * dx) / Math.max(alpha, 1e-9);

  const next = new Array(nx * ny);
  for (let s = 0; s < steps; s++) {
    if (s && overtime()) break;
    for (let i = 0; i < nx; i++) {
      for (let j = 0; j < ny; j++) {
        const id = idx(i, j, ny);
        if (i === 0 || j === 0 || i === nx - 1 || j === ny - 1) {
          next[id] = amb;
          continue;
        }
        const c = field[id];
        const lap =
          field[idx(i + 1, j, ny)] +
          field[idx(i - 1, j, ny)] +
          field[idx(i, j + 1, ny)] +
          field[idx(i, j - 1, ny)] -
          4 * c;
        next[id] = c + (alpha * dt * lap) / (dx * dx);
      }
    }
    for (const src of sources) {
      const x = clamp(finite(src.x, 0.5), 0.02, 0.98);
      const y = clamp(finite(src.y, 0.5), 0.02, 0.98);
      const power = clamp(finite(src.power, 8), -40, 80);
      const rad = clamp(finite(src.radius, 0.06), 0.02, 0.2);
      const si = Math.round(x * (nx - 1));
      const sj = Math.round(y * (ny - 1));
      const rPix = Math.max(1, Math.round(rad * Math.max(nx, ny)));
      for (let i = Math.max(1, si - rPix); i <= Math.min(nx - 2, si + rPix); i++) {
        for (let j = Math.max(1, sj - rPix); j <= Math.min(ny - 2, sj + rPix); j++) {
          const d = Math.hypot((i - si) / nx, (j - sj) / ny);
          if (d <= rad) {
            const w = Math.exp((-4 * d * d) / (rad * rad));
            next[idx(i, j, ny)] += power * w * dt * 55;
          }
        }
      }
    }
    for (let k = 0; k < next.length; k++) {
      next[k] = clamp(next[k], -40, 900);
    }
    field = next.slice();
  }

  let min = Infinity;
  let max = -Infinity;
  let sum = 0;
  for (const v of field) {
    if (v < min) min = v;
    if (v > max) max = v;
    sum += v;
  }

  return {
    nx,
    ny,
    field,
    min,
    max,
    mean: sum / field.length,
    material: { id: input.material || "copper", ...material },
    dt,
    steps,
    ambient: amb,
  };
}

export function electrostatics(input, overtime = () => false) {
  const n = clamp(Math.round(finite(input.n, 48)), 16, 72);
  const iters = clamp(Math.round(finite(input.iters, 80)), 10, 160);
  const electrodes = Array.isArray(input.electrodes) ? input.electrodes.slice(0, 6) : [
    { x: 0.22, y: 0.5, v: 12, r: 0.05 },
    { x: 0.78, y: 0.5, v: -12, r: 0.05 },
  ];

  const V = new Array(n * n).fill(0);
  const fixed = new Uint8Array(n * n);
  for (const e of electrodes) {
    const x = clamp(finite(e.x, 0.5), 0.02, 0.98);
    const y = clamp(finite(e.y, 0.5), 0.02, 0.98);
    const r = clamp(finite(e.r, 0.05), 0.02, 0.14);
    const volt = clamp(finite(e.v, 5), -100, 100);
    const ci = Math.round(x * (n - 1));
    const cj = Math.round(y * (n - 1));
    const rp = Math.max(1, Math.round(r * n));
    for (let i = Math.max(0, ci - rp); i <= Math.min(n - 1, ci + rp); i++) {
      for (let j = Math.max(0, cj - rp); j <= Math.min(n - 1, cj + rp); j++) {
        if (Math.hypot(i - ci, j - cj) <= rp) {
          const id = i * n + j;
          V[id] = volt;
          fixed[id] = 1;
        }
      }
    }
  }

  // Gauss-Seidel Laplace
  for (let it = 0; it < iters; it++) {
    if (it % 8 === 0 && overtime()) break;
    for (let i = 1; i < n - 1; i++) {
      for (let j = 1; j < n - 1; j++) {
        const id = i * n + j;
        if (fixed[id]) continue;
        V[id] = 0.25 * (V[id - n] + V[id + n] + V[id - 1] + V[id + 1]);
      }
    }
  }

  const Ex = new Array(n * n).fill(0);
  const Ey = new Array(n * n).fill(0);
  let emax = 1e-6;
  for (let i = 1; i < n - 1; i++) {
    for (let j = 1; j < n - 1; j++) {
      const id = i * n + j;
      const ex = -0.5 * (V[id + n] - V[id - n]) * (n - 1);
      const ey = -0.5 * (V[id + 1] - V[id - 1]) * (n - 1);
      Ex[id] = ex;
      Ey[id] = ey;
      const mag = Math.hypot(ex, ey);
      if (mag > emax) emax = mag;
    }
  }

  return { n, V, Ex, Ey, emax, electrodes, iters };
}

export function waveStep(input, overtime = () => false) {
  const n = clamp(Math.round(finite(input.n, 56)), 24, 72);
  const steps = clamp(Math.round(finite(input.steps, 4)), 1, 12);
  const c = clamp(finite(input.c, 0.35), 0.05, 0.48);
  const damp = clamp(finite(input.damp, 0.0015), 0, 0.02);

  let u = Array.isArray(input.u) && input.u.length === n * n ? input.u.slice() : new Array(n * n).fill(0);
  let p = Array.isArray(input.prev) && input.prev.length === n * n ? input.prev.slice() : u.slice();
  const sources = Array.isArray(input.sources) ? input.sources.slice(0, 6) : [];

  const next = new Array(n * n);
  const c2 = c * c;

  for (let s = 0; s < steps; s++) {
    if (s && overtime()) break;
    for (let i = 0; i < n; i++) {
      for (let j = 0; j < n; j++) {
        const id = i * n + j;
        if (i === 0 || j === 0 || i === n - 1 || j === n - 1) {
          next[id] = 0;
          continue;
        }
        const lap = u[id - n] + u[id + n] + u[id - 1] + u[id + 1] - 4 * u[id];
        next[id] = (2 - damp) * u[id] - (1 - damp) * p[id] + c2 * lap;
      }
    }
    for (const src of sources) {
      const x = clamp(finite(src.x, 0.5), 0.05, 0.95);
      const y = clamp(finite(src.y, 0.5), 0.05, 0.95);
      const amp = clamp(finite(src.amp, 1), -4, 4);
      const freq = clamp(finite(src.freq, 0.12), 0.01, 0.45);
      const phase = finite(src.phase, 0);
      const t = finite(src.t, 0) + s;
      const i = Math.round(x * (n - 1));
      const j = Math.round(y * (n - 1));
      next[i * n + j] += amp * Math.sin(2 * Math.PI * freq * t + phase);
    }
    p = u;
    u = next.slice();
  }

  let min = Infinity;
  let max = -Infinity;
  for (const v of u) {
    if (v < min) min = v;
    if (v > max) max = v;
  }
  return { n, u, prev: p, min, max, c, steps };
}

export const materials = MATERIALS;
