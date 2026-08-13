import { clamp, finite, linspace, downsampleMulti } from "./math.js";

function rk4(f, state, t, dt) {
  const n = state.length;
  const k1 = f(t, state);
  const s2 = new Array(n);
  const s3 = new Array(n);
  const s4 = new Array(n);
  for (let i = 0; i < n; i++) s2[i] = state[i] + 0.5 * dt * k1[i];
  const k2 = f(t + 0.5 * dt, s2);
  for (let i = 0; i < n; i++) s3[i] = state[i] + 0.5 * dt * k2[i];
  const k3 = f(t + 0.5 * dt, s3);
  for (let i = 0; i < n; i++) s4[i] = state[i] + dt * k3[i];
  const k4 = f(t + dt, s4);
  const next = new Array(n);
  for (let i = 0; i < n; i++) {
    next[i] = state[i] + (dt / 6) * (k1[i] + 2 * k2[i] + 2 * k3[i] + k4[i]);
  }
  return next;
}

function integrate(f, y0, tEnd, dt, overtime) {
  const steps = Math.min(20000, Math.max(20, Math.ceil(tEnd / dt)));
  const t = new Array(steps + 1);
  const series = y0.map(() => new Array(steps + 1));
  let y = y0.slice();
  t[0] = 0;
  y0.forEach((v, i) => {
    series[i][0] = v;
  });
  for (let k = 1; k <= steps; k++) {
    if (k % 256 === 0 && overtime()) {
      const err = new Error("Compute budget exceeded");
      err.status = 429;
      err.code = "compute_timeout";
      err.expose = true;
      throw err;
    }
    const tk = (k - 1) * dt;
    y = rk4(f, y, tk, dt);
    for (let i = 0; i < y.length; i++) {
      if (!Number.isFinite(y[i]) || Math.abs(y[i]) > 1e8) y[i] = clamp(y[i], -1e8, 1e8);
      series[i][k] = y[i];
    }
    t[k] = k * dt;
  }
  return { t, series };
}

export function solveOde(input, overtime = () => false) {
  const system = input.system;
  const p = input.params || {};
  const tEnd = clamp(finite(input.tEnd, 20), 0.5, 80);
  const dt = clamp(finite(input.dt, 0.01), 0.001, 0.05);

  let y0;
  let f;
  let labels;
  let title;
  let meta = {};

  if (system === "lorenz") {
    const sigma = clamp(finite(p.sigma, 10), 0.1, 40);
    const rho = clamp(finite(p.rho, 28), 0.1, 60);
    const beta = clamp(finite(p.beta, 8 / 3), 0.1, 12);
    y0 = [clamp(finite(p.x0, 1), -40, 40), clamp(finite(p.y0, 1), -40, 40), clamp(finite(p.z0, 1), -40, 40)];
    f = (_t, [x, y, z]) => [sigma * (y - x), x * (rho - z) - y, x * y - beta * z];
    labels = ["x", "y", "z"];
    title = "Lorenz attractor";
    meta = { sigma, rho, beta };
  } else if (system === "vanderpol") {
    const mu = clamp(finite(p.mu, 2), 0.05, 12);
    y0 = [clamp(finite(p.x0, 1), -8, 8), clamp(finite(p.v0, 0), -8, 8)];
    f = (_t, [x, v]) => [v, mu * (1 - x * x) * v - x];
    labels = ["x", "ẋ"];
    title = "Van der Pol oscillator";
    meta = { mu };
  } else if (system === "harmonic") {
    const wn = clamp(finite(p.wn, 4), 0.2, 30);
    const zeta = clamp(finite(p.zeta, 0.15), 0, 2.5);
    y0 = [clamp(finite(p.x0, 1), -5, 5), clamp(finite(p.v0, 0), -20, 20)];
    f = (_t, [x, v]) => [v, -2 * zeta * wn * v - wn * wn * x];
    labels = ["x", "ẋ"];
    title = "Damped harmonic oscillator";
    meta = { wn, zeta };
  } else if (system === "pid") {
    const kp = clamp(finite(p.kp, 2.4), 0, 20);
    const ki = clamp(finite(p.ki, 1.1), 0, 12);
    const kd = clamp(finite(p.kd, 0.35), 0, 4);
    const wn = clamp(finite(p.wn, 3.2), 0.4, 18);
    const zeta = clamp(finite(p.zeta, 0.45), 0.05, 2);
    const sp = clamp(finite(p.setpoint, 1), -5, 5);
    // plant:  wn^2 / (s^2 + 2ζwn s + wn^2)
    // state: [y, dy, integral]
    y0 = [0, 0, 0];
    f = (_t, [y, dy, integ]) => {
      const err = sp - y;
      const u = kp * err + ki * integ + kd * -dy;
      const ddy = wn * wn * (u - y) - 2 * zeta * wn * dy;
      return [dy, ddy, err];
    };
    labels = ["y", "ẏ", "∫e"];
    title = "PID on 2nd-order plant";
    meta = { kp, ki, kd, wn, zeta, setpoint: sp };
  } else if (system === "rc") {
    const R = clamp(finite(p.R, 1000), 10, 1e6);
    const C = clamp(finite(p.C, 1e-4), 1e-7, 0.05);
    const vin = clamp(finite(p.vin, 5), -24, 24);
    y0 = [0];
    f = (_t, [vc]) => [(vin - vc) / (R * C)];
    labels = ["Vc"];
    title = "RC step charge";
    meta = { R, C, vin, tau: R * C };
  } else {
    const err = new Error("Unknown ODE system");
    err.status = 400;
    err.expose = true;
    throw err;
  }

  const raw = integrate(f, y0, tEnd, dt, overtime);
  const slim = downsampleMulti(raw.t, raw.series, 1600);
  return {
    system,
    title,
    labels,
    meta,
    t: slim.t,
    series: slim.series,
    dt,
    tEnd,
    points: slim.t.length,
  };
}

export function samplePreview(system) {
  const t = linspace(0, 8, 80);
  return { t, hint: system };
}
