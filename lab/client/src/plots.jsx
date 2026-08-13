import { useEffect, useRef } from "react";

function prep(canvas) {
  const dpr = Math.min(2, window.devicePixelRatio || 1);
  const rect = canvas.getBoundingClientRect();
  const w = Math.max(1, Math.floor(rect.width * dpr));
  const h = Math.max(1, Math.floor(rect.height * dpr));
  if (canvas.width !== w || canvas.height !== h) {
    canvas.width = w;
    canvas.height = h;
  }
  const ctx = canvas.getContext("2d");
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  return { ctx, w: rect.width, h: rect.height, dpr };
}

function grid(ctx, w, h, pad) {
  ctx.save();
  ctx.strokeStyle = "rgba(255,255,255,0.06)";
  ctx.lineWidth = 1;
  for (let x = pad; x < w - 8; x += 36) {
    ctx.beginPath();
    ctx.moveTo(x, 10);
    ctx.lineTo(x, h - pad);
    ctx.stroke();
  }
  for (let y = 10; y < h - pad; y += 28) {
    ctx.beginPath();
    ctx.moveTo(pad, y);
    ctx.lineTo(w - 10, y);
    ctx.stroke();
  }
  ctx.restore();
}

function bounds(arr) {
  let lo = Infinity;
  let hi = -Infinity;
  for (const v of arr) {
    if (v < lo) lo = v;
    if (v > hi) hi = v;
  }
  if (!Number.isFinite(lo)) return { lo: -1, hi: 1 };
  if (lo === hi) return { lo: lo - 1, hi: hi + 1 };
  const p = (hi - lo) * 0.08;
  return { lo: lo - p, hi: hi + p };
}

function mapX(i, n, pad, w) {
  return pad + (i / Math.max(1, n - 1)) * (w - pad - 12);
}
function mapY(v, lo, hi, h, pad) {
  return 12 + (1 - (v - lo) / (hi - lo)) * (h - pad - 16);
}

export function LinePlot({ series = [], colors, title, height = 280, className = "" }) {
  const ref = useRef(null);
  useEffect(() => {
    const canvas = ref.current;
    if (!canvas) return;
    const { ctx, w, h } = prep(canvas);
    ctx.clearRect(0, 0, w, h);
    const pad = 36;
    grid(ctx, w, h, pad);
    const palette = colors || ["#7ef0dc", "#7ab0ff", "#ffc37a", "#c4b2ff", "#ff8fa8"];
    const all = series.flatMap((s) => s.y || []);
    const { lo, hi } = bounds(all.length ? all : [0, 1]);

    ctx.fillStyle = "rgba(255,255,255,0.35)";
    ctx.font = "11px Geist Variable, sans-serif";
    ctx.fillText(title || "", pad, 16);
    ctx.fillText(hi.toFixed(2), 6, 22);
    ctx.fillText(lo.toFixed(2), 6, h - pad + 2);

    series.forEach((s, si) => {
      const y = s.y || [];
      if (y.length < 2) return;
      ctx.save();
      ctx.beginPath();
      y.forEach((v, i) => {
        const x = mapX(i, y.length, pad, w);
        const yy = mapY(v, lo, hi, h, pad);
        if (i === 0) ctx.moveTo(x, yy);
        else ctx.lineTo(x, yy);
      });
      ctx.strokeStyle = palette[si % palette.length];
      ctx.lineWidth = 2;
      ctx.shadowColor = palette[si % palette.length];
      ctx.shadowBlur = 12;
      ctx.stroke();
      ctx.restore();
    });
  }, [series, colors, title, height]);

  return <canvas ref={ref} className={`plot-canvas ${className}`} style={{ height }} />;
}

export function PhasePlot({ x = [], y = [], title }) {
  const ref = useRef(null);
  useEffect(() => {
    const canvas = ref.current;
    if (!canvas) return;
    const { ctx, w, h } = prep(canvas);
    ctx.clearRect(0, 0, w, h);
    const pad = 28;
    grid(ctx, w, h, pad);
    const bx = bounds(x);
    const by = bounds(y);
    ctx.fillStyle = "rgba(255,255,255,0.35)";
    ctx.font = "11px Geist Variable, sans-serif";
    ctx.fillText(title || "phase", pad, 16);
    const n = Math.min(x.length, y.length);
    for (let i = 1; i < n; i++) {
      const u = i / n;
      ctx.strokeStyle = `rgba(126,240,220,${0.08 + u * 0.75})`;
      ctx.beginPath();
      ctx.moveTo(
        pad + ((x[i - 1] - bx.lo) / (bx.hi - bx.lo)) * (w - pad - 12),
        12 + (1 - (y[i - 1] - by.lo) / (by.hi - by.lo)) * (h - pad - 16),
      );
      ctx.lineTo(
        pad + ((x[i] - bx.lo) / (bx.hi - bx.lo)) * (w - pad - 12),
        12 + (1 - (y[i] - by.lo) / (by.hi - by.lo)) * (h - pad - 16),
      );
      ctx.stroke();
    }
  }, [x, y, title]);
  return <canvas ref={ref} className="plot-canvas" />;
}

export function MiniSpark({ fn, color = "#7ef0dc" }) {
  const ref = useRef(null);
  useEffect(() => {
    let raf;
    const canvas = ref.current;
    const loop = (t) => {
      if (!canvas) return;
      const { ctx, w, h } = prep(canvas);
      ctx.clearRect(0, 0, w, h);
      ctx.beginPath();
      for (let i = 0; i < 80; i++) {
        const x = (i / 79) * w;
        const y = h * 0.55 + fn(i, t) * h * 0.28;
        if (i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
      }
      ctx.strokeStyle = color;
      ctx.lineWidth = 1.6;
      ctx.shadowColor = color;
      ctx.shadowBlur = 8;
      ctx.stroke();
      raf = requestAnimationFrame(loop);
    };
    raf = requestAnimationFrame(loop);
    return () => cancelAnimationFrame(raf);
  }, [fn, color]);
  return <canvas ref={ref} />;
}

export function MiniHeat() {
  const ref = useRef(null);
  useEffect(() => {
    let raf;
    const canvas = ref.current;
    const off = document.createElement("canvas");
    const loop = (t) => {
      if (!canvas) return;
      const dpr = Math.min(2, window.devicePixelRatio || 1);
      const rect = canvas.getBoundingClientRect();
      const w = Math.max(1, Math.floor(rect.width * dpr));
      const h = Math.max(1, Math.floor(rect.height * dpr));
      if (canvas.width !== w || canvas.height !== h) {
        canvas.width = w;
        canvas.height = h;
      }
      const ctx = canvas.getContext("2d");
      const cw = 96;
      const ch = 48;
      if (off.width !== cw) {
        off.width = cw;
        off.height = ch;
      }
      const octx = off.getContext("2d");
      const img = octx.createImageData(cw, ch);
      const s = t * 0.001;
      for (let y = 0; y < ch; y++) {
        for (let x = 0; x < cw; x++) {
          const u = Math.sin(x * 0.18 + s) + Math.cos(y * 0.22 - s * 0.7) + Math.sin((x + y) * 0.1 + s);
          const v = (u + 3) / 6;
          const i = (y * cw + x) * 4;
          img.data[i] = Math.floor(40 + v * 220);
          img.data[i + 1] = Math.floor(20 + v * 90);
          img.data[i + 2] = Math.floor(80 + (1 - v) * 140);
          img.data[i + 3] = 255;
        }
      }
      octx.putImageData(img, 0, 0);
      ctx.imageSmoothingEnabled = true;
      ctx.drawImage(off, 0, 0, w, h);
      raf = requestAnimationFrame(loop);
    };
    raf = requestAnimationFrame(loop);
    return () => cancelAnimationFrame(raf);
  }, []);
  return <canvas ref={ref} />;
}
