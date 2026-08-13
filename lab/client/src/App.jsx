import { useEffect, useState } from "react";
import { dict } from "./i18n.js";
import { api } from "./api.js";
import { Atmosphere, Nav, Glass, DocsSheet } from "./ui.jsx";
import { IotLab } from "./IotLab.jsx";
import { MatlabLab } from "./MatlabLab.jsx";
import { ComsolLab } from "./ComsolLab.jsx";
import { MiniSpark, MiniHeat } from "./plots.jsx";

export function App() {
  const [lang, setLang] = useState(() => localStorage.getItem("pe-lang") || "fa");
  const [view, setView] = useState("hub");
  const [docs, setDocs] = useState(false);
  const [health, setHealth] = useState({ ok: false, ms: null });
  const t = dict[lang] || dict.fa;

  useEffect(() => {
    document.documentElement.lang = t.lang;
    document.documentElement.dir = t.dir;
    localStorage.setItem("pe-lang", lang);
    document.title = `${t.heroTitle} · Physio Electric`;
  }, [lang, t]);

  useEffect(() => {
    let alive = true;
    const ping = async () => {
      try {
        const res = await api("/api/health");
        if (alive) setHealth({ ok: true, ms: res.netMs, uptime: res.uptime });
      } catch {
        if (alive) setHealth({ ok: false, ms: null });
      }
    };
    ping();
    const id = setInterval(ping, 8000);
    return () => {
      alive = false;
      clearInterval(id);
    };
  }, []);

  return (
    <div className="world">
      <Atmosphere />
      <Nav
        t={t}
        lang={lang}
        setLang={setLang}
        health={health}
        onDocs={() => setDocs(true)}
        onHome={() => setView("hub")}
      />
      <main className="stage">
        {view === "hub" && <Hub t={t} onOpen={setView} />}
        {view === "iot" && (
          <>
            <Back t={t} onClick={() => setView("hub")} />
            <IotLab t={t} />
          </>
        )}
        {view === "matlab" && (
          <>
            <Back t={t} onClick={() => setView("hub")} />
            <MatlabLab t={t} />
          </>
        )}
        {view === "comsol" && (
          <>
            <Back t={t} onClick={() => setView("hub")} />
            <ComsolLab t={t} />
          </>
        )}
      </main>
      <footer className="foot">{t.footer}</footer>
      {docs && <DocsSheet t={t} health={health} onClose={() => setDocs(false)} />}
    </div>
  );
}

function Back({ t, onClick }) {
  return (
    <div style={{ marginBottom: 10 }}>
      <button className="pill" type="button" onClick={onClick}>
        ← {t.backHub}
      </button>
    </div>
  );
}

function Hub({ t, onOpen }) {
  return (
    <>
      <section className="hero enter-1" style={{ position: "relative" }}>
        <div className="kicker">{t.pageKicker}</div>
        <h1>{t.heroTitle}</h1>
        <p className="sub">{t.heroSub}</p>
        <p className="lead">{t.heroLead}</p>
        <div className="hero-orbs" aria-hidden>
          <span className="orb a" />
          <span className="orb b" />
          <span className="orb c" />
        </div>
      </section>
      <section className="lab-grid">
        <Glass as="button" className="lab-card enter-2" onClick={() => onOpen("iot")}>
          <div className="lab-preview" style={{ background: "linear-gradient(160deg,#0b1a18,#101018)" }}>
            <MiniSpark fn={(i, t0) => Math.sin(i * 0.28 + t0 / 400) * 0.55 + Math.sin(i * 0.07 + t0 / 900) * 0.2} />
          </div>
          <div className="lab-body">
            <h3>{t.iotTitle}</h3>
            <p>{t.iotDesc}</p>
            <span className="lab-cta">{t.enter} →</span>
          </div>
        </Glass>
        <Glass as="button" className="lab-card enter-3" onClick={() => onOpen("matlab")}>
          <div className="lab-preview" style={{ background: "#07080e" }}>
            <MiniSpark
              color="#7ab0ff"
              fn={(i, t0) => {
                const x = i / 12;
                return Math.exp(-x * 0.08) * Math.sin(x * 1.4 + t0 / 350);
              }}
            />
          </div>
          <div className="lab-body">
            <h3>{t.matlabTitle}</h3>
            <p>{t.matlabDesc}</p>
            <span className="lab-cta">{t.enter} →</span>
          </div>
        </Glass>
        <Glass as="button" className="lab-card enter-4" onClick={() => onOpen("comsol")}>
          <div className="lab-preview">
            <MiniHeat />
          </div>
          <div className="lab-body">
            <h3>{t.comsolTitle}</h3>
            <p>{t.comsolDesc}</p>
            <span className="lab-cta">{t.enter} →</span>
          </div>
        </Glass>
      </section>
    </>
  );
}
