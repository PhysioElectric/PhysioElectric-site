import { useEffect, useRef } from "react";

export function Glass({ as: Tag = "div", className = "", children, dense, thin, ...rest }) {
  const ref = useRef(null);
  return (
    <Tag
      ref={ref}
      className={`glass ${dense ? "dense" : ""} ${thin ? "thin" : ""} ${className}`}
      onMouseMove={(e) => {
        const el = ref.current;
        if (!el) return;
        const r = el.getBoundingClientRect();
        el.style.setProperty("--mx", `${e.clientX - r.left}px`);
        el.style.setProperty("--my", `${e.clientY - r.top}px`);
      }}
      {...rest}
    >
      {children}
    </Tag>
  );
}

export function Atmosphere() {
  const light = useRef(null);
  useEffect(() => {
    const el = light.current;
    let x = innerWidth * 0.3;
    let y = innerHeight * 0.2;
    let tx = x;
    let ty = y;
    const move = (e) => {
      tx = e.clientX;
      ty = e.clientY;
    };
    let raf;
    const loop = () => {
      x += (tx - x) * 0.08;
      y += (ty - y) * 0.08;
      if (el) el.style.transform = `translate3d(${x}px, ${y}px, 0)`;
      raf = requestAnimationFrame(loop);
    };
    loop();
    window.addEventListener("pointermove", move, { passive: true });
    return () => {
      cancelAnimationFrame(raf);
      window.removeEventListener("pointermove", move);
    };
  }, []);
  return (
    <>
      <div className="aurora" aria-hidden>
        <b />
        <b />
        <b />
      </div>
      <div className="vignette" aria-hidden />
      <div className="grain" aria-hidden />
      <div className="cursor-light" ref={light} aria-hidden />
      <SvgFilters />
    </>
  );
}

function SvgFilters() {
  return (
    <svg width="0" height="0" style={{ position: "absolute" }} aria-hidden>
      <filter id="liquid-glass" x="-20%" y="-20%" width="140%" height="140%">
        <feTurbulence type="fractalNoise" baseFrequency="0.012 0.02" numOctaves="2" seed="7" result="n" />
        <feGaussianBlur in="n" stdDeviation="0.8" result="b" />
        <feDisplacementMap in="SourceGraphic" in2="b" scale="18" xChannelSelector="R" yChannelSelector="G" />
      </filter>
    </svg>
  );
}

export function Nav({ t, lang, setLang, health, onDocs, onHome }) {
  return (
    <header className="topbar">
      <Glass className="topbar-inner lg-nav" as="div">
        <button className="brand" onClick={onHome} type="button">
          <img src="/brand-mark.png" alt="" />
          <span className="brand-txt">
            <strong>Physio Electric</strong>
            <span>{t.brandFa}</span>
          </span>
        </button>
        <nav className="crumbs" aria-label="breadcrumb">
          <span>{t.crumbHome}</span>
          <i>/</i>
          <span>{t.crumbLabs}</span>
          <i>/</i>
          <b>{t.crumbSims}</b>
        </nav>
        <div className="nav-actions">
          <span className="pill" title={t.status}>
            <i className={`dot ${health.ok ? "" : "off"}`} />
            {health.ok ? t.live : t.offline}
            {health.ms != null && <em style={{ fontStyle: "normal", opacity: 0.7 }}> · {health.ms}ms</em>}
          </span>
          <button className="pill" type="button" onClick={onDocs}>
            {t.docs}
          </button>
          <button className="pill" type="button" onClick={() => setLang(lang === "fa" ? "en" : "fa")}>
            {t.langToggle}
          </button>
        </div>
      </Glass>
    </header>
  );
}

export function useCount(value, speed = 0.18) {
  const [v, setV] = useState(value);
  const ref = useRef(value);
  useEffect(() => {
    let raf;
    const tick = () => {
      ref.current += (value - ref.current) * speed;
      if (Math.abs(value - ref.current) < 0.01) ref.current = value;
      setV(ref.current);
      if (ref.current !== value) raf = requestAnimationFrame(tick);
    };
    raf = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(raf);
  }, [value, speed]);
  return v;
}

export function DocsSheet({ t, health, onClose }) {
  return (
    <div className="sheet" onClick={onClose} role="presentation">
      <Glass className="sheet-card" dense onClick={(e) => e.stopPropagation()}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "start", gap: 12 }}>
          <div>
            <h3>{t.docsTitle}</h3>
            <p>{t.docsBody}</p>
          </div>
          <button className="icon-btn" type="button" onClick={onClose} aria-label="close">
            ×
          </button>
        </div>
        <div className="metrics-row" style={{ marginTop: 14 }}>
          <Glass thin className="metric">
            <span className="lbl">{t.status}</span>
            <span className="val">{health.ok ? "OK" : "DOWN"}</span>
          </Glass>
          <Glass thin className="metric">
            <span className="lbl">{t.latency}</span>
            <span className="val">
              {health.ms ?? "—"}
              <small>ms</small>
            </span>
          </Glass>
        </div>
        <p className="hint" style={{ marginTop: 14 }}>
          Helmet · CORS allowlist · rate-limit · Zod · 32kb JSON · compute budget · WS origin + IP cap · no eval
        </p>
      </Glass>
    </div>
  );
}
