import { useEffect, useMemo, useState } from "react";
import { Glass } from "./ui.jsx";
import { api } from "./api.js";
import { LinePlot, PhasePlot } from "./plots.jsx";

const EXPERIMENTS = [
  { id: "lorenz", group: "ode", blurb: "σ ρ β · strange attractor" },
  { id: "vanderpol", group: "ode", blurb: "nonlinear limit cycle" },
  { id: "harmonic", group: "ode", blurb: "ζ · ωn" },
  { id: "pid", group: "ode", blurb: "plant + controller" },
  { id: "rc", group: "ode", blurb: "first-order step" },
  { id: "fft", group: "sig", blurb: "time ↔ frequency" },
  { id: "series", group: "sig", blurb: "Gibbs phenomenon" },
  { id: "bode", group: "ctl", blurb: "G(jω) + step" },
];

const DEFAULTS = {
  lorenz: { sigma: 10, rho: 28, beta: 2.67, tEnd: 30 },
  vanderpol: { mu: 2.2, tEnd: 28 },
  harmonic: { wn: 5, zeta: 0.12, tEnd: 12 },
  pid: { kp: 2.6, ki: 1.1, kd: 0.32, setpoint: 1, tEnd: 10 },
  rc: { R: 2200, C: 0.00008, vin: 5, tEnd: 1.2 },
  fft: { kind: "sum", freq: 40, noise: 0.08, n: 2048, fs: 1000 },
  series: { wave: "square", harmonics: 7, cycles: 2 },
  bode: { wn: 6, zeta: 0.32, k: 1, delay: 0 },
};

export function MatlabLab({ t }) {
  const [exp, setExp] = useState("lorenz");
  const [params, setParams] = useState(DEFAULTS.lorenz);
  const [busy, setBusy] = useState(false);
  const [err, setErr] = useState("");
  const [pack, setPack] = useState(null);

  const run = async (id = exp, p = params) => {
    setBusy(true);
    setErr("");
    try {
      let res;
      if (id === "fft") res = await api("/api/sim/fft", p);
      else if (id === "series") res = await api("/api/sim/fourier", p);
      else if (id === "bode") res = await api("/api/sim/control", p);
      else res = await api("/api/sim/ode", { system: id, params: p, tEnd: p.tEnd, dt: 0.012 });
      setPack({ id, ...res });
    } catch (e) {
      setErr(e.message || "failed");
    } finally {
      setBusy(false);
    }
  };

  useEffect(() => {
    const id = setTimeout(() => run(exp, params), 220);
    return () => clearTimeout(id);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [params, exp]);

  const pick = (id) => {
    setExp(id);
    const p = { ...DEFAULTS[id] };
    setParams(p);
    run(id, p);
  };

  const setP = (k, v) => setParams((prev) => ({ ...prev, [k]: v }));

  const plots = useMemo(() => interpret(pack, t), [pack, t]);

  return (
    <div className="workspace">
      <div className="ws-head">
        <div>
          <h2>{t.matlabTitle}</h2>
          <p className="hint">{t.matlabDesc}</p>
        </div>
        <div style={{ display: "flex", gap: 8, alignItems: "center" }}>
          {pack?.computeMs != null && (
            <span className="pill">
              {t.compute} {pack.computeMs}ms
              {pack.netMs != null && <em style={{ fontStyle: "normal", opacity: 0.65 }}> · net {pack.netMs}ms</em>}
            </span>
          )}
          <button className="btn primary" type="button" disabled={busy} onClick={() => run()}>
            {busy ? t.running : t.run}
          </button>
        </div>
      </div>

      <div className="split">
        <Glass className="list" dense>
          <div className="lbl" style={{ padding: "6px 8px", color: "var(--faint)", fontSize: 11, letterSpacing: "0.08em" }}>
            {t.presets}
          </div>
          {EXPERIMENTS.map((e) => (
            <button key={e.id} type="button" className={exp === e.id ? "on" : ""} onClick={() => pick(e.id)}>
              <span>{t[e.id] || e.id}</span>
              <small>{e.blurb}</small>
            </button>
          ))}
        </Glass>

        <Glass className="plot-wrap" dense>
          {err && <p className="hint" style={{ color: "var(--rose)" }}>{err}</p>}
          {plots}
        </Glass>

        <Glass className="side-panel" dense>
          <strong>{t.params}</strong>
          <ParamPane exp={exp} params={params} setP={setP} t={t} />
          <button className="btn" type="button" disabled={busy} onClick={() => run()}>
            {busy ? t.running : t.run}
          </button>
        </Glass>
      </div>
    </div>
  );
}

function ParamPane({ exp, params, setP, t }) {
  const sl = (key, min, max, step = 0.01) => (
    <label className="slider" key={key}>
      <span>
        {key} · <b style={{ color: "white", fontFamily: "var(--mono)" }}>{Number(params[key] ?? 0).toFixed(step < 1 ? 2 : 0)}</b>
      </span>
      <input
        type="range"
        min={min}
        max={max}
        step={step}
        value={params[key] ?? min}
        onChange={(e) => setP(key, Number(e.target.value))}
      />
    </label>
  );

  if (exp === "lorenz") return <>{sl("sigma", 1, 25)}{sl("rho", 5, 50)}{sl("beta", 0.4, 8)}{sl("tEnd", 8, 50, 0.5)}</>;
  if (exp === "vanderpol") return <>{sl("mu", 0.2, 8)}{sl("tEnd", 8, 40, 0.5)}</>;
  if (exp === "harmonic") return <>{sl("wn", 0.5, 16)}{sl("zeta", 0, 1.6)}{sl("tEnd", 4, 24, 0.5)}</>;
  if (exp === "pid") return <>{sl("kp", 0, 10)}{sl("ki", 0, 6)}{sl("kd", 0, 2)}{sl("setpoint", 0.2, 2)}{sl("tEnd", 4, 20, 0.5)}</>;
  if (exp === "rc") return <>{sl("R", 200, 20000, 50)}{sl("C", 0.00001, 0.0004, 0.00001)}{sl("vin", 1, 12)}{sl("tEnd", 0.3, 3, 0.05)}</>;
  if (exp === "fft") {
    return (
      <>
        <label className="slider">
          <span>wave</span>
          <select
            value={params.kind}
            onChange={(e) => setP("kind", e.target.value)}
            style={{ background: "rgba(255,255,255,0.06)", border: "1px solid var(--stroke-2)", borderRadius: 10, padding: 8 }}
          >
            {["sine", "square", "saw", "triangle", "pwm", "chirp", "sum"].map((k) => (
              <option key={k} value={k}>{k}</option>
            ))}
          </select>
        </label>
        {sl("freq", 5, 180, 1)}
        {sl("noise", 0, 0.6)}
        {sl("amp", 0.2, 3)}
      </>
    );
  }
  if (exp === "series") return <>{sl("harmonics", 1, 30, 1)}{sl("cycles", 1, 4, 1)}</>;
  if (exp === "bode") return <>{sl("wn", 0.6, 24)}{sl("zeta", 0.05, 1.4)}{sl("k", 0.2, 3)}{sl("delay", 0, 0.35)}</>;
  return null;
}

function interpret(pack, t) {
  if (!pack?.data) return <p className="hint">{t.run}</p>;
  const d = pack.data;
  if (pack.id === "fft") {
    return (
      <>
        <LinePlot title="y(t)" series={[{ y: d.y }]} />
        <LinePlot title="|Y(f)|" series={[{ y: d.mag }]} colors={["#ffc37a"]} />
        <div className="kv">
          <span>{t.peak}</span>
          <b>{d.peakHz?.toFixed(2)} Hz</b>
        </div>
      </>
    );
  }
  if (pack.id === "series") {
    return (
      <>
        <LinePlot title={`${d.kind} · N=${d.harmonics}`} series={[{ y: d.ideal }, { y: d.approx }]} colors={["rgba(255,255,255,0.25)", "#7ef0dc"]} />
      </>
    );
  }
  if (pack.id === "bode") {
    return (
      <>
        <LinePlot title="step y(t)" series={[{ y: d.y }]} />
        <LinePlot title="Bode |G| dB" series={[{ y: d.magDb }]} colors={["#7ab0ff"]} />
        <LinePlot title="phase °" series={[{ y: d.phaseDeg }]} colors={["#c4b2ff"]} height={180} />
        <div className="kv"><span>{t.overshoot}</span><b>{d.metrics.overshoot.toFixed(1)}%</b></div>
        <div className="kv"><span>{t.settling}</span><b>{d.metrics.settling.toFixed(2)}s</b></div>
      </>
    );
  }
  // ODE
  const series = (d.series || []).map((y, i) => ({ y, name: d.labels?.[i] }));
  const extra =
    pack.id === "lorenz" && d.series?.[0] && d.series?.[2] ? (
      <PhasePlot x={d.series[0]} y={d.series[2]} title="x–z projection" />
    ) : pack.id === "vanderpol" && d.series?.[0] && d.series?.[1] ? (
      <PhasePlot x={d.series[0]} y={d.series[1]} title="limit cycle" />
    ) : null;
  return (
    <>
      <LinePlot title={d.title} series={series} />
      {extra}
    </>
  );
}
