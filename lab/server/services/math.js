export function clamp(n, lo, hi) {
  return Math.min(hi, Math.max(lo, n));
}

export function finite(n, fallback = 0) {
  return Number.isFinite(n) ? n : fallback;
}

export function nowMs() {
  return Number(process.hrtime.bigint() / 1000000n);
}

export function withBudget(budgetMs, fn) {
  const t0 = nowMs();
  const result = fn(() => nowMs() - t0 > budgetMs);
  return { result, ms: nowMs() - t0 };
}

export function linspace(a, b, n) {
  const out = new Array(n);
  if (n === 1) {
    out[0] = a;
    return out;
  }
  const step = (b - a) / (n - 1);
  for (let i = 0; i < n; i++) out[i] = a + i * step;
  return out;
}

export function downsample(xs, ys, maxPoints) {
  if (xs.length <= maxPoints) return { x: xs, y: ys };
  const stride = Math.ceil(xs.length / maxPoints);
  const x = [];
  const y = [];
  for (let i = 0; i < xs.length; i += stride) {
    x.push(xs[i]);
    y.push(ys[i]);
  }
  return { x, y };
}

export function downsampleMulti(t, series, maxPoints) {
  if (t.length <= maxPoints) return { t, series };
  const stride = Math.ceil(t.length / maxPoints);
  const tt = [];
  const ss = series.map(() => []);
  for (let i = 0; i < t.length; i += stride) {
    tt.push(t[i]);
    series.forEach((s, k) => ss[k].push(s[i]));
  }
  return { t: tt, series: ss };
}
