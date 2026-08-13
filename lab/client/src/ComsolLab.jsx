import { useEffect, useRef, useState } from "react";
import { Glass } from "./ui.jsx";
import { api } from "./api.js";
import { makeGrid, stepHeat, stepWave, inferno, viridis, turbo, clamp } from "./physics.js";

const N = 64;
const MATERIALS = ["copper", "aluminum", "silicon", "steel", "glass", "air", "fr4"];

export function ComsolLab({ t }) {
  const [mode, setMode] = useState("heat");
  const [playing, setPlaying] = useState(true);
  const [mesh, setMesh] = useState(true);
  const [iso, setIso] = useState(true);
  const [material, setMaterial] = useState("copper");
  const [power, setPower] = useState(14);
  const [speed, setSpeed] = useState(0.32);
  const [damp, setDamp] = useState(0.002);
  const [scale, setScale] = useState(1.4);
  const [stats, setStats] = useState({ min: 25, max: 25, mean: 25 });
  const [coreMs, setCoreMs] = useState(null);
  const sources = useRef([{ x: 0.5, y: 0.5, power: 14, radius: 0.07, amp: 1.2, freq: 0.11 }]);
  const field = useRef(makeGrid(N, 25));
  const prev = useRef(makeGrid(N, 0));
  const wave = useRef(makeGrid(N, 0));
  const tick = useRef(0);
  const canvasRef = useRef(null);
  const playRef = useRef(true);
  playRef.current = playing;

  const reset = (m = mode) => {
    field.current = makeGrid(N, 25);
    wave.current = makeGrid(N, 0);
    prev.current = makeGrid(N, 0);
    tick.current = 0;
    if (m === "electro") solveElectro();
  };

  const solveElectro = async () => {
    try {
      const res = await api("/api/sim/electro", {
        n: 56,
        iters: 90,
        electrodes: sources.current.slice(0, 4).map((s, i) => ({
          x: s.x,
          y: s.y,
          v: i % 2 === 0 ? 12 : -12,
          r: 0.05,
        })),
      });
      setCoreMs(res.computeMs);
      drawElectro(canvasRef.current, res.data, mesh);
      const V = res.data.V;
      let min = Infinity;
      let max = -Infinity;
      let sum = 0;
      for (const v of V) {
        if (v < min) min = v;
        if (v > max) max = v;
        sum += v;
      }
      setStats({ min, max, mean: sum / V.length });
    } catch {
      /* */
    }
  };

  useEffect(() => {
    reset(mode);
    if (mode === "electro") solveElectro();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mode]);

  useEffect(() => {
    let raf;
    const loop = () => {
      const canvas = canvasRef.current;
      if (canvas && playRef.current) {
        if (mode === "heat") {
          sources.current = sources.current.map((s) => ({ ...s, power }));
          field.current = stepHeat(field.current, N, {
            ambient: 25,
            material,
            scale,
            sources: sources.current,
            steps: 2,
          });
          const st = drawScalar(canvas, field.current, N, inferno, mesh, iso, 20, 120);
          setStats(st);
        } else if (mode === "wave") {
          const next = stepWave(wave.current, prev.current, N, {
            c: speed,
            damp,
            sources: sources.current.map((s) => ({ ...s, amp: 1.15, freq: 0.1, t: tick.current })),
            t: tick.current,
          });
          prev.current = wave.current;
          wave.current = next;
          tick.current += 1;
          const st = drawScalar(canvas, next, N, turbo, mesh, false, -2.2, 2.2);
          setStats(st);
        }
      }
      raf = requestAnimationFrame(loop);
    };
    raf = requestAnimationFrame(loop);
    return () => cancelAnimationFrame(raf);
  }, [mode, material, power, speed, damp, scale, mesh, iso]);

  const onClick = (e) => {
    const r = e.currentTarget.getBoundingClientRect();
    const x = clamp((e.clientX - r.left) / r.width, 0.04, 0.96);
    const y = clamp((e.clientY - r.top) / r.height, 0.04, 0.96);
    sources.current = [...sources.current.slice(-4), { x, y, power, radius: 0.07, amp: 1.2, freq: 0.12 }];
    if (mode === "electro") solveElectro();
  };

  const palette = mode === "heat" ? inferno : mode === "wave" ? turbo : viridis;

  return (
    <div className="workspace">
      <div className="ws-head">
        <div>
          <h2>{t.comsolTitle}</h2>
          <p className="hint">{t.clickField}</p>
        </div>
        <Glass className="seg" thin>
          {["heat", "electro", "wave"].map((m) => (
            <button key={m} type="button" className={mode === m ? "on" : ""} onClick={() => setMode(m)}>
              {t[m]}
            </button>
          ))}
        </Glass>
      </div>

      <div className="split" style={{ gridTemplateColumns: "minmax(0,1fr) 280px" }}>
        <Glass className="plot-wrap" dense>
          <div className="field-wrap">
            <canvas ref={canvasRef} className="tall" onClick={onClick} />
            <div
              className="legend"
              style={{
                background: `linear-gradient(180deg, ${cssStops(palette)})`,
              }}
            />
          </div>
        </Glass>

        <Glass className="side-panel" dense>
          <div className="kv">
            <span>min</span>
            <b>{stats.min.toFixed(2)}</b>
          </div>
          <div className="kv">
            <span>max</span>
            <b>{stats.max.toFixed(2)}</b>
          </div>
          <div className="kv">
            <span>mean</span>
            <b>{stats.mean.toFixed(2)}</b>
          </div>
          {coreMs != null && (
            <div className="kv">
              <span>{t.compute}</span>
              <b>{coreMs}ms</b>
            </div>
          )}

          {mode === "heat" && (
            <>
              <label className="slider">
                <span>{t.material}</span>
                <select
                  value={material}
                  onChange={(e) => setMaterial(e.target.value)}
                  style={{ background: "rgba(255,255,255,0.06)", border: "1px solid var(--stroke-2)", borderRadius: 10, padding: 8 }}
                >
                  {MATERIALS.map((m) => (
                    <option key={m} value={m}>{m}</option>
                  ))}
                </select>
              </label>
              <label className="slider">
                <span>{t.power} · {power.toFixed(1)}</span>
                <input type="range" min="2" max="40" step="0.5" value={power} onChange={(e) => setPower(Number(e.target.value))} />
              </label>
              <label className="slider">
                <span>diffusivity · {scale.toFixed(2)}</span>
                <input type="range" min="0.2" max="5" step="0.05" value={scale} onChange={(e) => setScale(Number(e.target.value))} />
              </label>
            </>
          )}
          {mode === "wave" && (
            <>
              <label className="slider">
                <span>{t.speed} · {speed.toFixed(2)}</span>
                <input type="range" min="0.12" max="0.46" step="0.01" value={speed} onChange={(e) => setSpeed(Number(e.target.value))} />
              </label>
              <label className="slider">
                <span>{t.damp} · {damp.toFixed(4)}</span>
                <input type="range" min="0" max="0.02" step="0.0005" value={damp} onChange={(e) => setDamp(Number(e.target.value))} />
              </label>
            </>
          )}

          <button className="btn" type="button" onClick={() => setPlaying((p) => !p)}>
            {playing ? t.pause : t.play}
          </button>
          <button className="btn ghost" type="button" onClick={() => { sources.current = []; reset(mode); }}>
            {t.clear}
          </button>
          <button className="btn ghost" type="button" onClick={() => setMesh((m) => !m)}>
            {t.mesh} {mesh ? "· on" : "· off"}
          </button>
          <button className="btn ghost" type="button" onClick={() => setIso((m) => !m)}>
            {t.isolines} {iso ? "· on" : "· off"}
          </button>
          {mode === "electro" && (
            <button className="btn primary" type="button" onClick={solveElectro}>
              {t.run}
            </button>
          )}
        </Glass>
      </div>
    </div>
  );
}

function cssStops(pal) {
  return [0, 0.25, 0.5, 0.75, 1].map((u) => {
    const [r, g, b] = pal(1 - u);
    return `rgb(${r},${g},${b}) ${u * 100}%`;
  }).join(",");
}

function drawScalar(canvas, field, n, pal, mesh, iso, lo, hi) {
  const dpr = Math.min(2, window.devicePixelRatio || 1);
  const rect = canvas.getBoundingClientRect();
  const w = Math.max(1, Math.floor(rect.width * dpr));
  const h = Math.max(1, Math.floor(rect.height * dpr));
  if (canvas.width !== w || canvas.height !== h) {
    canvas.width = w;
    canvas.height = h;
  }
  const ctx = canvas.getContext("2d");
  let min = Infinity;
  let max = -Infinity;
  let sum = 0;
  for (const v of field) {
    if (v < min) min = v;
    if (v > max) max = v;
    sum += v;
  }
  const span = Math.max(1e-6, (hi ?? max) - (lo ?? min));
  const baseLo = lo ?? min;
  const img = ctx.createImageData(n, n);
  for (let j = 0; j < n; j++) {
    for (let i = 0; i < n; i++) {
      const v = field[i * n + j];
      const u = clamp((v - baseLo) / span, 0, 1);
      const [r, g, b] = pal(u);
      const p = (j * n + i) * 4;
      img.data[p] = r;
      img.data[p + 1] = g;
      img.data[p + 2] = b;
      img.data[p + 3] = 255;
    }
  }
  if (!drawScalar._off) drawScalar._off = document.createElement("canvas");
  const off = drawScalar._off;
  off.width = n;
  off.height = n;
  off.getContext("2d").putImageData(img, 0, 0);
  ctx.imageSmoothingEnabled = true;
  ctx.drawImage(off, 0, 0, w, h);
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  const cw = rect.width;
  const ch = rect.height;
  if (mesh) {
    ctx.strokeStyle = "rgba(255,255,255,0.08)";
    ctx.lineWidth = 0.6;
    const step = cw / 16;
    ctx.beginPath();
    for (let x = 0; x <= cw; x += step) {
      ctx.moveTo(x, 0);
      ctx.lineTo(x, ch);
    }
    for (let y = 0; y <= ch; y += step) {
      ctx.moveTo(0, y);
      ctx.lineTo(cw, y);
    }
    ctx.stroke();
  }
  if (iso) {
    ctx.strokeStyle = "rgba(255,255,255,0.18)";
    ctx.lineWidth = 1;
    for (let k = 1; k < 6; k++) {
      const thr = baseLo + (span * k) / 6;
      ctx.beginPath();
      for (let i = 1; i < n; i++) {
        for (let j = 1; j < n; j++) {
          const a = field[(i - 1) * n + (j - 1)] > thr;
          const b = field[i * n + (j - 1)] > thr;
          if (a !== b) {
            ctx.moveTo(((i - 1) / n) * cw, ((j - 1) / n) * ch);
            ctx.lineTo((i / n) * cw, ((j - 1) / n) * ch);
          }
        }
      }
      ctx.stroke();
    }
  }
  ctx.setTransform(1, 0, 0, 1, 0, 0);
  return { min, max, mean: sum / field.length };
}

function drawElectro(canvas, data, mesh) {
  if (!canvas || !data) return;
  drawScalar(canvas, data.V, data.n, viridis, mesh, true, undefined, undefined);
  const dpr = Math.min(2, window.devicePixelRatio || 1);
  const rect = canvas.getBoundingClientRect();
  const ctx = canvas.getContext("2d");
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  const n = data.n;
  ctx.strokeStyle = "rgba(255,255,255,0.35)";
  ctx.lineWidth = 1;
  for (let i = 4; i < n; i += 5) {
    for (let j = 4; j < n; j += 5) {
      const id = i * n + j;
      const ex = data.Ex[id];
      const ey = data.Ey[id];
      const mag = Math.hypot(ex, ey) || 1;
      const s = 10;
      const x = (i / n) * rect.width;
      const y = (j / n) * rect.height;
      ctx.beginPath();
      ctx.moveTo(x, y);
      ctx.lineTo(x + (ex / mag) * s, y + (ey / mag) * s);
      ctx.stroke();
    }
  }
  ctx.setTransform(1, 0, 0, 1, 0, 0);
}
