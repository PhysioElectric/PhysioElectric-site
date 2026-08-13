import { useEffect, useMemo, useRef, useState } from "react";
import { Glass } from "./ui.jsx";
import { Esp32Board, Stm32Board } from "./boards.jsx";
import { labSocket } from "./api.js";
import { clamp, tempPalette, sensorThermalLocal } from "./physics.js";

const TOOLS = [
  { id: "hand", icon: Hand },
  { id: "flame", icon: Flame },
  { id: "heatgun", icon: HeatGun },
  { id: "iron", icon: Iron },
  { id: "light", icon: Lamp },
];

export function IotLab({ t }) {
  const [board, setBoard] = useState("esp32");
  const [tool, setTool] = useState("flame");
  const [telem, setTelem] = useState(fallbackTelem());
  const [live, setLive] = useState(false);
  const [tilt, setTilt] = useState({ x: 8, y: -14 });
  const [cursor, setCursor] = useState({ x: -999, y: -999, active: false });
  const [heatGlow, setHeatGlow] = useState(0);
  const sensorRef = useRef(null);
  const wsRef = useRef(null);
  const localRef = useRef({ temp: 24.6, humidity: 43, tools: [] });
  const benchRef = useRef(null);

  useEffect(() => {
    let ws;
    let closed = false;
    let fallbackTimer;
    const attach = () => {
      try {
        ws = labSocket(board);
        wsRef.current = ws;
        ws.onopen = () => setLive(true);
        ws.onclose = () => {
          setLive(false);
          if (!closed) fallbackTimer = setTimeout(attach, 2500);
        };
        ws.onerror = () => ws.close();
        ws.onmessage = (ev) => {
          try {
            const msg = JSON.parse(ev.data);
            if (msg.type === "telemetry") setTelem(msg);
          } catch {
            /* */
          }
        };
      } catch {
        setLive(false);
      }
    };
    attach();
    return () => {
      closed = true;
      clearTimeout(fallbackTimer);
      try { ws?.close(); } catch { /* */ }
    };
  }, [board]);

  useEffect(() => {
    const send = (payload) => {
      const ws = wsRef.current;
      if (ws && ws.readyState === 1) ws.send(JSON.stringify(payload));
    };
    let raf;
    const loop = () => {
      const tools = localRef.current.tools;
      send({ type: "tools", tools });
      if (!live) {
        const th = sensorThermalLocal({
          temp: localRef.current.temp,
          humidity: localRef.current.humidity,
          dt: 0.08,
          tools,
        });
        localRef.current.temp = th.temp;
        localRef.current.humidity = th.humidity;
        const volts = clamp(0.5 + 0.01 * th.temp, 0, 3.3);
        const ms = Math.round((localRef.current.uptime = (localRef.current.uptime || 0) + 80));
        const shouldLog = !localRef.current.lastLog || ms - localRef.current.lastLog > 850;
        if (shouldLog) localRef.current.lastLog = ms;
        setTelem((prev) => {
          const line = shouldLog
            ? {
                t: ms,
                level: th.coupling > 0.35 ? "w" : "i",
                message:
                  th.coupling > 0.35
                    ? `[THERMAL] t=${th.temp.toFixed(2)}C  couple=${th.coupling.toFixed(2)}`
                    : `[DHT22] t=${th.temp.toFixed(2)}C  rh=${th.humidity.toFixed(1)}%`,
              }
            : null;
          const serial = line ? [...(prev.serial || []), line].slice(-12) : prev.serial;
          return {
            ...prev,
            temp: th.temp,
            humidity: th.humidity,
            coupling: th.coupling,
            lux: 160 + (tools.length ? 0 : 20),
            adc: { volts, code: Math.round((volts / 3.3) * 4095), bits: 12, vref: 3.3 },
            serial,
          };
        });
      }
      raf = setTimeout(loop, live ? 90 : 80);
    };
    loop();
    return () => clearTimeout(raf);
  }, [live]);

  const onPointer = (e) => {
    const heat = ["hand", "flame", "heatgun", "iron"].includes(tool);
    const light = tool === "light";
    setCursor({ x: e.clientX, y: e.clientY, active: heat || light });
    const node = document.getElementById("iot-sensor");
    let dist = 2;
    if (node) {
      const r = node.getBoundingClientRect();
      const cx = r.left + r.width / 2;
      const cy = r.top + r.height / 2;
      dist = Math.hypot(e.clientX - cx, e.clientY - cy) / 220;
    }
    const intensity = heat ? clamp(1 - dist * 0.95, 0, 1) : 0;
    setHeatGlow(intensity);
    localRef.current.tools = heat
      ? [{ kind: tool, distance: Math.max(0.01, dist * 0.35), intensity: tool === "hand" ? 0.35 : 1 }]
      : [];
    const ws = wsRef.current;
    if (ws && ws.readyState === 1 && light) {
      ws.send(JSON.stringify({ type: "light", intensity: clamp(1 - dist, 0, 1) }));
    }
  };

  const endPointer = () => {
    setCursor((c) => ({ ...c, active: false }));
    localRef.current.tools = [];
    setHeatGlow(0);
    const ws = wsRef.current;
    if (ws && ws.readyState === 1) {
      ws.send(JSON.stringify({ type: "tools", tools: [] }));
      ws.send(JSON.stringify({ type: "light", intensity: 0 }));
    }
  };

  const toggleGpio = (pin) => {
    if (!pin || Number.isNaN(Number(pin))) return;
    const ws = wsRef.current;
    const next = telem.gpio?.[pin] ? 0 : 1;
    setTelem((p) => ({ ...p, gpio: { ...p.gpio, [pin]: next } }));
    if (ws && ws.readyState === 1) ws.send(JSON.stringify({ type: "gpio", pin, value: next }));
  };

  const setPwm = (value) => {
    setTelem((p) => ({ ...p, pwm: value }));
    const ws = wsRef.current;
    if (ws && ws.readyState === 1) ws.send(JSON.stringify({ type: "pwm", value }));
  };

  const setRgb = (key, value) => {
    const rgb = { ...(telem.leds?.rgb || { r: 20, g: 180, b: 200 }), [key]: value };
    setTelem((p) => ({ ...p, leds: { ...p.leds, rgb } }));
    const ws = wsRef.current;
    if (ws && ws.readyState === 1) ws.send(JSON.stringify({ type: "rgb", ...rgb }));
  };

  const dragBoard = (e) => {
    if (e.buttons !== 1) return;
    const el = benchRef.current;
    if (!el) return;
    const r = el.getBoundingClientRect();
    const nx = ((e.clientX - r.left) / r.width - 0.5) * 24;
    const ny = ((e.clientY - r.top) / r.height - 0.5) * -18;
    setTilt({ x: ny, y: nx });
    const ws = wsRef.current;
    if (ws && ws.readyState === 1) {
      ws.send(JSON.stringify({ type: "imu", roll: nx, pitch: ny, yaw: 0 }));
    }
  };

  const gpio = telem.gpio || {};
  const rgb = telem.leds?.rgb || { r: 20, g: 180, b: 200 };
  const tempC = tempPalette(telem.temp || 25);

  const history = useTempHistory(telem.temp);

  return (
    <div className="workspace">
      <div className="ws-head">
        <div>
          <h2>{t.iotTitle}</h2>
          <p className="hint">{t.sensorHint}</p>
        </div>
        <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
          <Glass className="seg" thin>
            <button type="button" className={board === "esp32" ? "on" : ""} onClick={() => setBoard("esp32")}>
              {t.boardEsp}
            </button>
            <button type="button" className={board === "stm32" ? "on" : ""} onClick={() => setBoard("stm32")}>
              {t.boardStm}
            </button>
          </Glass>
          <span className="pill">
            <i className={`dot ${live ? "" : "off"}`} />
            {live ? t.firmware : t.connect}
          </span>
        </div>
      </div>

      <div className="iot-grid">
        <div>
          <Glass
            className="bench"
            dense
            ref={undefined}
            onPointerMove={(e) => {
              onPointer(e);
              dragBoard(e);
            }}
            onPointerLeave={endPointer}
            onPointerUp={endPointer}
          >
            <div className="bench-inner" ref={benchRef}>
              <div
                className="board-stage"
                style={{ transform: `rotateX(${8 + tilt.x}deg) rotateY(${-16 + tilt.y}deg)` }}
              >
                {board === "esp32" ? (
                  <Esp32Board
                    temp={telem.temp}
                    humidity={telem.humidity}
                    ledOn={telem.leds?.user || telem.pwm > 20}
                    rgb={rgb}
                    gpio={gpio}
                    onPin={toggleGpio}
                    heatGlow={heatGlow}
                    sensorHot={heatGlow > 0.25}
                  />
                ) : (
                  <Stm32Board
                    temp={telem.temp}
                    ledOn={telem.leds?.user || true}
                    gpio={gpio}
                    onPin={toggleGpio}
                    heatGlow={heatGlow}
                    adc={telem.adc}
                  />
                )}
              </div>
            </div>
            <p className="hint" style={{ textAlign: "center", marginTop: 8 }}>
              {t.dragBoard}
            </p>
          </Glass>

          <Glass className="dock" style={{ marginTop: 12 }} thin>
            {TOOLS.map((tl) => {
              const Icon = tl.icon;
              return (
                <button
                  key={tl.id}
                  type="button"
                  className={`tool ${tool === tl.id ? "on" : ""}`}
                  onClick={() => setTool(tl.id)}
                >
                  <Icon />
                  <span>{t[tl.id]}</span>
                </button>
              );
            })}
          </Glass>

          <Glass className="serial" dense style={{ marginTop: 12 }}>
            <div className="serial-head">
              <span>{t.serial}</span>
              <span>{board === "esp32" ? "115200 8N1" : "USART2 115200"}</span>
            </div>
            <div className="serial-body">
              {(telem.serial || []).map((line, i) => (
                <div key={`${line.t}-${i}`} className={line.level === "w" ? "w" : line.level === "e" ? "e" : ""}>
                  <span className="t">{String(line.t).padStart(6, "0")}</span>
                  {line.message}
                </div>
              ))}
            </div>
          </Glass>
        </div>

        <div className="side">
          <Glass className="metric" dense>
            <span className="lbl">{t.temp}</span>
            <span className="val" style={{ color: tempC }}>
              {(telem.temp ?? 0).toFixed(2)}
              <small>°C</small>
            </span>
            <Spark values={history} color={tempC} />
            <div className="bar">
              <i style={{ width: `${clamp(((telem.temp ?? 25) - 18) / 80, 0, 1) * 100}%` }} />
            </div>
          </Glass>
          <div className="metrics-row">
            <Glass className="metric" thin>
              <span className="lbl">{t.humidity}</span>
              <span className="val">
                {(telem.humidity ?? 0).toFixed(1)}
                <small>%</small>
              </span>
            </Glass>
            <Glass className="metric" thin>
              <span className="lbl">{t.lux}</span>
              <span className="val">
                {Math.round(telem.lux ?? 0)}
                <small>lx</small>
              </span>
            </Glass>
            <Glass className="metric" thin>
              <span className="lbl">{t.rssi}</span>
              <span className="val">
                {Math.round(telem.rssi ?? -60)}
                <small>dBm</small>
              </span>
            </Glass>
            <Glass className="metric" thin>
              <span className="lbl">{t.adc}</span>
              <span className="val" style={{ fontSize: 18 }}>
                {telem.adc?.code ?? "—"}
                <small>{telem.adc ? `${telem.adc.volts.toFixed(2)}V` : ""}</small>
              </span>
            </Glass>
          </div>
          <Glass className="metric" thin>
            <span className="lbl">{t.coupling}</span>
            <span className="val">
              {((telem.coupling || heatGlow) * 100).toFixed(0)}
              <small>%</small>
            </span>
            <div className="bar">
              <i style={{ width: `${clamp(telem.coupling || heatGlow, 0, 1) * 100}%` }} />
            </div>
          </Glass>
          <Glass className="metric" thin>
            <span className="lbl">{t.gpio}</span>
            <div className="gpio" style={{ marginTop: 8 }}>
              {["2", "4", "5", "18", "19"].map((p) => (
                <button key={p} type="button" className={gpio[p] ? "on" : ""} onClick={() => toggleGpio(p)}>
                  {t.pin} {p}
                </button>
              ))}
            </div>
          </Glass>
          <Glass className="metric" thin>
            <label className="slider">
              <span>
                {t.pwm} · {telem.pwm ?? 0}
              </span>
              <input type="range" min="0" max="255" value={telem.pwm ?? 40} onChange={(e) => setPwm(Number(e.target.value))} />
            </label>
            <div className="rgb-row" style={{ marginTop: 10 }}>
              <span className="lbl">{t.rgb}</span>
              {["r", "g", "b"].map((k) => (
                <label className="slider" key={k}>
                  <span>{k.toUpperCase()}</span>
                  <input type="range" min="0" max="255" value={rgb[k]} onChange={(e) => setRgb(k, Number(e.target.value))} />
                </label>
              ))}
              <div className="swatch" style={{ background: `rgb(${rgb.r},${rgb.g},${rgb.b})` }} />
            </div>
          </Glass>
          <button
            className="btn ghost"
            type="button"
            onClick={() => {
              const ws = wsRef.current;
              if (ws && ws.readyState === 1) ws.send(JSON.stringify({ type: "reset" }));
            }}
          >
            {t.reset}
          </button>
        </div>
      </div>

      {cursor.active && (
        <div className={`heat-cursor ${tool}`} style={{ left: cursor.x, top: cursor.y }}>
          <span className="core" />
        </div>
      )}
    </div>
  );
}

function fallbackTelem() {
  return {
    temp: 24.6,
    humidity: 43,
    lux: 180,
    rssi: -58,
    adc: { code: 1842, volts: 1.49 },
    gpio: { 2: 0, 4: 0, 5: 1, 18: 0, 19: 0 },
    pwm: 40,
    leds: { user: false, rgb: { r: 20, g: 180, b: 200 } },
    serial: [{ t: 28, level: "i", message: "waiting for firmware socket…" }],
    coupling: 0,
  };
}

function useTempHistory(temp) {
  const [h, setH] = useState(() => Array.from({ length: 48 }, () => 24.6));
  useEffect(() => {
    setH((prev) => [...prev.slice(1), temp ?? prev[prev.length - 1]]);
  }, [temp]);
  return h;
}

function Spark({ values, color }) {
  const d = useMemo(() => {
    if (!values?.length) return "";
    const min = Math.min(...values);
    const max = Math.max(...values);
    const span = max - min || 1;
    return values
      .map((v, i) => {
        const x = (i / (values.length - 1)) * 220;
        const y = 36 - ((v - min) / span) * 30;
        return `${i === 0 ? "M" : "L"}${x.toFixed(1)},${y.toFixed(1)}`;
      })
      .join(" ");
  }, [values]);
  return (
    <svg viewBox="0 0 220 40" width="100%" height="40" aria-hidden>
      <path d={d} fill="none" stroke={color} strokeWidth="2" />
    </svg>
  );
}

function Hand() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6">
      <path d="M8 13V6.5a1.5 1.5 0 1 1 3 0V12m0-5.5a1.5 1.5 0 1 1 3 0V12m0-3.5a1.5 1.5 0 1 1 3 0V13c0 3.2-2 6-6 8-3.2-1.5-6-4.2-6-8V11a1.5 1.5 0 1 1 3 0v2" />
    </svg>
  );
}
function Flame() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6">
      <path d="M12 3s6 6 6 11a6 6 0 1 1-12 0c0-2 2-5 3-6 0 3 2 3 2 3 0-2 1-6 1-8z" />
    </svg>
  );
}
function HeatGun() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6">
      <rect x="3" y="9" width="12" height="6" rx="2" />
      <path d="M15 11h4l2-2v6l-2-2h-4M7 15v4h5" />
    </svg>
  );
}
function Iron() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6">
      <path d="M4 15h14l2-5H9L4 15zM6 15v3m10-3v3" />
    </svg>
  );
}
function Lamp() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6">
      <path d="M9 18h6M12 18v3M8 9a4 4 0 1 1 8 0c0 2-1 3-2 4h-4c-1-1-2-2-2-4z" />
    </svg>
  );
}
