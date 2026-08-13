import { clamp, finite } from "./math.js";

function evalPoly(c, sRe, sIm) {
  // c[0] + c[1] s + c[2] s^2 ...
  let re = 0;
  let im = 0;
  let pRe = 1;
  let pIm = 0;
  for (let i = 0; i < c.length; i++) {
    re += c[i] * pRe;
    im += c[i] * pIm;
    const nRe = pRe * sRe - pIm * sIm;
    const nIm = pRe * sIm + pIm * sRe;
    pRe = nRe;
    pIm = nIm;
  }
  return [re, im];
}

function tf(num, den, sRe, sIm) {
  const [nRe, nIm] = evalPoly(num, sRe, sIm);
  const [dRe, dIm] = evalPoly(den, sRe, sIm);
  const mag2 = dRe * dRe + dIm * dIm || 1e-18;
  return [(nRe * dRe + nIm * dIm) / mag2, (nIm * dRe - nRe * dIm) / mag2];
}

export function bodeAndStep(input) {
  const wn = clamp(finite(input.wn, 6), 0.2, 80);
  const zeta = clamp(finite(input.zeta, 0.35), 0.01, 3);
  const k = clamp(finite(input.k, 1), 0.05, 10);
  const delay = clamp(finite(input.delay, 0), 0, 0.4);

  // G(s) = K wn^2 / (s^2 + 2ζwn s + wn^2)
  const num = [k * wn * wn];
  const den = [wn * wn, 2 * zeta * wn, 1];

  const nF = 220;
  const wMin = Math.log10(0.05);
  const wMax = Math.log10(200);
  const w = new Array(nF);
  const magDb = new Array(nF);
  const phaseDeg = new Array(nF);
  for (let i = 0; i < nF; i++) {
    const ww = 10 ** (wMin + ((wMax - wMin) * i) / (nF - 1));
    const [re, im] = tf(num, den, 0, ww);
    const cd = Math.cos(-ww * delay);
    const sd = Math.sin(-ww * delay);
    const reD = re * cd - im * sd;
    const imD = re * sd + im * cd;
    w[i] = ww;
    magDb[i] = 20 * Math.log10(Math.hypot(reD, imD) + 1e-18);
    phaseDeg[i] = (Math.atan2(imD, reD) * 180) / Math.PI;
  }

  // step response via RK4 of the same plant
  const tEnd = clamp(8 / Math.max(wn * 0.25, 0.4), 1.5, 16);
  const dt = 0.004;
  const steps = Math.min(4000, Math.ceil(tEnd / dt));
  const t = new Array(steps);
  const y = new Array(steps);
  let pos = 0;
  let vel = 0;
  for (let i = 0; i < steps; i++) {
    const tt = i * dt;
    const u = tt >= delay ? 1 : 0;
    const f = (p, v) => wn * wn * (k * u - p) - 2 * zeta * wn * v;
    const k1v = f(pos, vel);
    const k1p = vel;
    const k2v = f(pos + 0.5 * dt * k1p, vel + 0.5 * dt * k1v);
    const k2p = vel + 0.5 * dt * k1v;
    const k3v = f(pos + 0.5 * dt * k2p, vel + 0.5 * dt * k2v);
    const k3p = vel + 0.5 * dt * k2v;
    const k4v = f(pos + dt * k3p, vel + dt * k3v);
    const k4p = vel + dt * k3v;
    pos += (dt / 6) * (k1p + 2 * k2p + 2 * k3p + k4p);
    vel += (dt / 6) * (k1v + 2 * k2v + 2 * k3v + k4v);
    t[i] = tt;
    y[i] = pos;
  }

  let peak = -Infinity;
  let peakT = 0;
  for (let i = 0; i < y.length; i++) {
    if (y[i] > peak) {
      peak = y[i];
      peakT = t[i];
    }
  }
  const overshoot = Math.max(0, (peak - k) / Math.max(Math.abs(k), 1e-6)) * 100;
  let settle = t[t.length - 1];
  const band = 0.02 * Math.abs(k);
  for (let i = y.length - 1; i >= 0; i--) {
    if (Math.abs(y[i] - k) > band) {
      settle = t[Math.min(i + 1, t.length - 1)];
      break;
    }
  }

  const stride = Math.max(1, Math.floor(t.length / 700));
  const ts = [];
  const ys = [];
  for (let i = 0; i < t.length; i += stride) {
    ts.push(t[i]);
    ys.push(y[i]);
  }

  return {
    wn,
    zeta,
    k,
    delay,
    w,
    magDb,
    phaseDeg,
    t: ts,
    y: ys,
    metrics: {
      overshoot,
      peakTime: peakT,
      settling: settle,
      wn,
      zeta,
    },
  };
}
