import { clamp, finite } from "./math.js";

function nextPow2(n) {
  let p = 1;
  while (p < n) p <<= 1;
  return p;
}

function fftRadix2(re, im) {
  const n = re.length;
  for (let i = 1, j = 0; i < n; i++) {
    let bit = n >> 1;
    for (; j & bit; bit >>= 1) j ^= bit;
    j ^= bit;
    if (i < j) {
      [re[i], re[j]] = [re[j], re[i]];
      [im[i], im[j]] = [im[j], im[i]];
    }
  }
  for (let len = 2; len <= n; len <<= 1) {
    const ang = (-2 * Math.PI) / len;
    const wlenRe = Math.cos(ang);
    const wlenIm = Math.sin(ang);
    for (let i = 0; i < n; i += len) {
      let wRe = 1;
      let wIm = 0;
      for (let j = 0; j < len / 2; j++) {
        const uRe = re[i + j];
        const uIm = im[i + j];
        const vRe = re[i + j + len / 2] * wRe - im[i + j + len / 2] * wIm;
        const vIm = re[i + j + len / 2] * wIm + im[i + j + len / 2] * wRe;
        re[i + j] = uRe + vRe;
        im[i + j] = uIm + vIm;
        re[i + j + len / 2] = uRe - vRe;
        im[i + j + len / 2] = uIm - vIm;
        const nWRe = wRe * wlenRe - wIm * wlenIm;
        wIm = wRe * wlenIm + wIm * wlenRe;
        wRe = nWRe;
      }
    }
  }
}

function generateWave(kind, n, freq, fs, amp, phase, noise, duty) {
  const y = new Array(n);
  for (let i = 0; i < n; i++) {
    const t = i / fs;
    const w = 2 * Math.PI * freq * t + phase;
    let v = 0;
    if (kind === "sine") v = Math.sin(w);
    else if (kind === "square") v = Math.sin(w) >= 0 ? 1 : -1;
    else if (kind === "saw") v = 2 * ((freq * t + phase / (2 * Math.PI)) % 1) - 1;
    else if (kind === "triangle") {
      const u = ((freq * t + phase / (2 * Math.PI)) % 1 + 1) % 1;
      v = 4 * Math.abs(u - 0.5) - 1;
    } else if (kind === "pwm") v = ((freq * t) % 1) < duty ? 1 : -1;
    else if (kind === "chirp") {
      const f0 = freq;
      const f1 = freq * 8;
      const k = (f1 - f0) / Math.max(n / fs, 1e-6);
      v = Math.sin(2 * Math.PI * (f0 * t + 0.5 * k * t * t));
    } else if (kind === "noise") v = 0;
    else if (kind === "sum") {
      v = Math.sin(w) + 0.45 * Math.sin(2 * w + 0.4) + 0.22 * Math.sin(3 * w + 1.1);
    } else v = Math.sin(w);
    v = amp * v + (Math.random() * 2 - 1) * noise;
    y[i] = v;
  }
  return y;
}

export function analyzeSignal(input) {
  const kind = input.kind;
  const nReq = clamp(finite(input.n, 1024), 64, 4096);
  const n = nextPow2(nReq);
  const fs = clamp(finite(input.fs, 1000), 32, 48000);
  const freq = clamp(finite(input.freq, 50), 0.1, fs / 2.2);
  const amp = clamp(finite(input.amp, 1), 0.05, 10);
  const phase = clamp(finite(input.phase, 0), -Math.PI, Math.PI);
  const noise = clamp(finite(input.noise, 0.04), 0, 1.2);
  const duty = clamp(finite(input.duty, 0.5), 0.05, 0.95);

  const y = generateWave(kind, n, freq, fs, amp, phase, noise, duty);
  const re = y.slice();
  const im = new Array(n).fill(0);
  fftRadix2(re, im);

  const half = n / 2;
  const mag = new Array(half);
  const freqAxis = new Array(half);
  for (let k = 0; k < half; k++) {
    mag[k] = (2 / n) * Math.hypot(re[k], im[k]);
    freqAxis[k] = (k * fs) / n;
  }
  mag[0] /= 2;

  const timeStride = Math.max(1, Math.floor(n / 800));
  const t = [];
  const yt = [];
  for (let i = 0; i < n; i += timeStride) {
    t.push(i / fs);
    yt.push(y[i]);
  }

  const specStride = Math.max(1, Math.floor(half / 700));
  const f = [];
  const m = [];
  for (let i = 0; i < half; i += specStride) {
    f.push(freqAxis[i]);
    m.push(mag[i]);
  }

  let peakF = 0;
  let peakM = -1;
  for (let i = 1; i < half; i++) {
    if (mag[i] > peakM) {
      peakM = mag[i];
      peakF = freqAxis[i];
    }
  }

  return {
    kind,
    n,
    fs,
    freq,
    amp,
    noise,
    t,
    y: yt,
    f,
    mag: m,
    peakHz: peakF,
    peakAmp: peakM,
  };
}

export function fourierSeries(input) {
  const kind = input.wave || "square";
  const harmonics = clamp(Math.round(finite(input.harmonics, 7)), 1, 40);
  const cycles = clamp(finite(input.cycles, 2), 1, 6);
  const n = 720;
  const x = new Array(n);
  const approx = new Array(n);
  const ideal = new Array(n);
  for (let i = 0; i < n; i++) {
    const t = (cycles * i) / (n - 1);
    const th = 2 * Math.PI * t;
    x[i] = t;
    let s = 0;
    if (kind === "square") {
      for (let k = 0; k < harmonics; k++) {
        const nH = 2 * k + 1;
        s += (4 / (Math.PI * nH)) * Math.sin(nH * th);
      }
      ideal[i] = Math.sin(th) >= 0 ? 1 : -1;
    } else if (kind === "saw") {
      for (let k = 1; k <= harmonics; k++) s += ((2 * Math.pow(-1, k + 1)) / (k * Math.PI)) * Math.sin(k * th);
      ideal[i] = 2 * ((t % 1 + 1) % 1) - 1;
    } else {
      for (let k = 0; k < harmonics; k++) {
        const nH = 2 * k + 1;
        s += ((8 / (Math.PI * Math.PI)) * (k % 2 === 0 ? 1 : -1) * Math.sin(nH * th)) / (nH * nH);
      }
      const u = (t % 1 + 1) % 1;
      ideal[i] = 4 * Math.abs(u - 0.5) - 1;
    }
    approx[i] = s;
  }
  return { kind, harmonics, x, approx, ideal };
}
