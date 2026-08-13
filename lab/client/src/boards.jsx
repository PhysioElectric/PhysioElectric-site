import { tempPalette } from "./physics.js";

function Pins({ x, y, count, side, pitch = 18, labels, active, onPin }) {
  const dir = side === "left" ? -1 : 1;
  return (
    <g>
      {Array.from({ length: count }, (_, i) => {
        const py = y + i * pitch;
        const lab = labels[i] || "";
        const on = active?.[lab];
        return (
          <g key={`${side}-${i}`} onClick={() => onPin?.(lab)} style={{ cursor: lab ? "pointer" : "default" }}>
            <rect
              x={side === "left" ? x - 16 : x}
              y={py - 5}
              width="16"
              height="10"
              rx="1.5"
              fill={on ? "#d7c27a" : "#c9b37a"}
              stroke="#6d5b32"
              strokeWidth="0.4"
            />
            <rect
              x={side === "left" ? x - 7 : x + 1}
              y={py - 3.2}
              width="6"
              height="6.4"
              rx="0.6"
              fill="#8a7a48"
            />
            <text
              x={x + dir * 22}
              y={py + 3}
              fontSize="7.2"
              fill={on ? "#7ef0dc" : "rgba(255,255,255,0.45)"}
              fontFamily="ui-monospace, monospace"
              textAnchor={side === "left" ? "end" : "start"}
            >
              {lab}
            </text>
          </g>
        );
      })}
    </g>
  );
}

export function Esp32Board({ temp = 25, humidity = 40, ledOn, rgb, sensorHot, gpio, onPin, heatGlow }) {
  const tc = tempPalette(temp);
  const sensorFill = tempPalette(temp, 18, 85);
  return (
    <svg viewBox="0 0 720 340" role="img" aria-label="ESP32 development board">
      <defs>
        <pattern id="mask" patternUnits="userSpaceOnUse" width="120" height="120">
          <image href="/textures/solder-mask.png" width="120" height="120" />
        </pattern>
        <pattern id="metal" patternUnits="userSpaceOnUse" width="160" height="160">
          <image href="/textures/brushed-metal.png" width="160" height="160" />
        </pattern>
        <linearGradient id="can" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0" stopColor="#d8dee8" />
          <stop offset="0.45" stopColor="#8b94a3" />
          <stop offset="1" stopColor="#4b5260" />
        </linearGradient>
        <filter id="glow" x="-40%" y="-40%" width="180%" height="180%">
          <feGaussianBlur stdDeviation="6" result="b" />
          <feMerge>
            <feMergeNode in="b" />
            <feMergeNode in="SourceGraphic" />
          </feMerge>
        </filter>
        <linearGradient id="trace" x1="0" y1="0" x2="1" y2="0">
          <stop offset="0" stopColor="#1f6b4a" />
          <stop offset="0.5" stopColor="#7ef0dc" />
          <stop offset="1" stopColor="#1f6b4a" />
        </linearGradient>
      </defs>

      {/* bench shadow */}
      <ellipse cx="360" cy="318" rx="250" ry="14" fill="rgba(0,0,0,0.45)" />

      {/* PCB */}
      <g transform="translate(90 38)">
        <rect x="0" y="0" width="420" height="230" rx="10" fill="#0b1210" />
        <rect x="0" y="0" width="420" height="230" rx="10" fill="url(#mask)" opacity="0.55" />
        <rect x="0.6" y="0.6" width="418.8" height="228.8" rx="9.4" fill="none" stroke="rgba(255,255,255,0.08)" />
        {/* mounting */}
        {[
          [14, 14],
          [406, 14],
          [14, 216],
          [406, 216],
        ].map(([hx, hy], i) => (
          <g key={i}>
            <circle cx={hx} cy={hy} r="6" fill="#1a1f1c" stroke="#3a433c" />
            <circle cx={hx} cy={hy} r="2.6" fill="#0a0c0b" />
          </g>
        ))}

        {/* silkscreen */}
        <text x="28" y="28" fill="rgba(255,255,255,0.28)" fontSize="8" fontFamily="ui-monospace, monospace">
          PHYSIO ELECTRIC · DEV-C
        </text>
        <text x="300" y="28" fill="rgba(126,240,220,0.35)" fontSize="8" fontFamily="ui-monospace, monospace">
          ESP32-WROOM-32
        </text>

        {/* USB-C */}
        <g transform="translate(-22 92)">
          <rect x="0" y="0" width="28" height="46" rx="6" fill="url(#metal)" />
          <rect x="6" y="10" width="16" height="26" rx="8" fill="#111" />
          <rect x="8" y="14" width="12" height="18" rx="6" fill="#2a2a2a" />
        </g>

        {/* regulator + uart */}
        <rect x="28" y="86" width="36" height="22" rx="2" fill="#1a1a1a" stroke="#3a3a3a" />
        <text x="30" y="100" fill="#777" fontSize="6">
          AMS1117
        </text>
        <rect x="28" y="118" width="40" height="28" rx="2" fill="#111" stroke="#444" />
        <text x="32" y="135" fill="#666" fontSize="6">
          CP2102
        </text>

        {/* buttons */}
        <g transform="translate(80 168)">
          <circle cx="14" cy="14" r="11" fill="#161616" stroke="#333" />
          <circle cx="14" cy="14" r="6" fill="#2a2a2a" />
          <text x="6" y="36" fill="rgba(255,255,255,0.3)" fontSize="7">
            BOOT
          </text>
          <circle cx="50" cy="14" r="11" fill="#161616" stroke="#333" />
          <circle cx="50" cy="14" r="6" fill="#2a2a2a" />
          <text x="44" y="36" fill="rgba(255,255,255,0.3)" fontSize="7">
            EN
          </text>
        </g>

        {/* power LED */}
        <circle cx="86" cy="48" r="4" fill="#ff4d4d" filter="url(#glow)" opacity="0.95" />
        <text x="94" y="51" fill="rgba(255,80,80,0.5)" fontSize="7">
          PWR
        </text>
        {/* user LED */}
        <circle cx="86" cy="64" r="4" fill={ledOn ? "#4db8ff" : "#1a3040"} filter={ledOn ? "url(#glow)" : undefined} />

        {/* RGB */}
        <circle
          cx="130"
          cy="56"
          r="7"
          fill={`rgb(${rgb?.r ?? 20},${rgb?.g ?? 180},${rgb?.b ?? 200})`}
          filter="url(#glow)"
          opacity="0.9"
        />

        {/* traces */}
        <path
          d="M160 56 H250 V100 H300"
          fill="none"
          stroke="url(#trace)"
          strokeWidth="1.4"
          strokeDasharray="6 8"
          opacity="0.65"
        >
          <animate attributeName="stroke-dashoffset" from="0" to="-28" dur="1.4s" repeatCount="indefinite" />
        </path>

        {/* ESP module */}
        <g transform="translate(168 70)">
          <rect x="0" y="0" width="168" height="108" rx="4" fill="#0e1116" stroke="#2a3038" />
          <rect x="6" y="6" width="128" height="88" rx="2" fill="url(#can)" />
          <rect x="6" y="6" width="128" height="88" rx="2" fill="url(#metal)" opacity="0.35" />
          {/* can dots */}
          {Array.from({ length: 8 }, (_, i) =>
            Array.from({ length: 5 }, (_, j) => (
              <circle key={`${i}-${j}`} cx={18 + i * 14} cy={18 + j * 15} r="1.1" fill="rgba(0,0,0,0.18)" />
            )),
          )}
          <text x="18" y="54" fill="rgba(0,0,0,0.45)" fontSize="9" fontWeight="700" fontFamily="ui-sans-serif, system-ui">
            ESP32
          </text>
          {/* antenna */}
          <path d="M140 10 V98" stroke="#c9a24a" strokeWidth="3" />
          <path d="M146 18 V90" stroke="#c9a24a" strokeWidth="1.2" opacity="0.6" />
          <text x="150" y="58" fill="#c9a24a" fontSize="7" transform="rotate(90 150 58)">
            ANT
          </text>
        </g>

        {/* jumper wires to sensor */}
        <path d="M390 70 C 430 70, 450 40, 500 52" fill="none" stroke="#e24b4b" strokeWidth="3.2" strokeLinecap="round" />
        <path d="M390 86 C 430 90, 450 78, 500 84" fill="none" stroke="#2d2d2d" strokeWidth="3.2" strokeLinecap="round" />
        <path d="M390 102 C 430 110, 450 118, 500 116" fill="none" stroke="#f0d24a" strokeWidth="3.2" strokeLinecap="round" />
      </g>

      <Pins
        x={90}
        y={58}
        count={12}
        side="left"
        labels={["3V3", "EN", "VP", "VN", "34", "35", "32", "33", "25", "26", "27", "14"]}
        active={gpio}
        onPin={onPin}
      />
      <Pins
        x={510}
        y={58}
        count={12}
        side="right"
        labels={["GND", "23", "22", "TX", "RX", "21", "19", "18", "5", "17", "16", "4"]}
        active={gpio}
        onPin={onPin}
      />

      {/* DHT22 module */}
      <g transform="translate(500 40)" data-sensor="dht22">
        <rect x="0" y="0" width="88" height="150" rx="6" fill="#0f1720" stroke="#2a3544" />
        <rect x="0" y="0" width="88" height="150" rx="6" fill="url(#mask)" opacity="0.25" />
        <text x="10" y="16" fill="rgba(255,255,255,0.35)" fontSize="8" fontFamily="ui-monospace, monospace">
          DHT22
        </text>
        {/* sensor can */}
        <rect
          x="16"
          y="28"
          width="56"
          height="64"
          rx="6"
          fill={sensorFill}
          stroke="rgba(255,255,255,0.25)"
          filter={sensorHot ? "url(#glow)" : undefined}
          id="iot-sensor"
        />
        <rect x="22" y="34" width="44" height="40" rx="3" fill="rgba(0,0,0,0.18)" />
        {/* grill */}
        {Array.from({ length: 5 }, (_, i) => (
          <rect key={i} x="26" y={38 + i * 7} width="36" height="2.2" rx="1" fill="rgba(0,0,0,0.25)" />
        ))}
        {heatGlow > 0 && (
          <circle cx="44" cy="60" r={28 + heatGlow * 20} fill={tc} opacity={0.12 + heatGlow * 0.18} filter="url(#glow)" />
        )}
        <text x="16" y="110" fill="white" fontSize="13" fontFamily="ui-monospace, monospace">
          {temp.toFixed(1)}°
        </text>
        <text x="16" y="126" fill="rgba(255,255,255,0.5)" fontSize="9" fontFamily="ui-monospace, monospace">
          RH {humidity.toFixed(0)}%
        </text>
        <rect x="18" y="136" width="10" height="8" fill="#e24b4b" />
        <rect x="36" y="136" width="10" height="8" fill="#2d2d2d" />
        <rect x="54" y="136" width="10" height="8" fill="#f0d24a" />
      </g>
    </svg>
  );
}

export function Stm32Board({ temp = 25, ledOn, gpio, onPin, heatGlow, adc }) {
  const sensorFill = tempPalette(temp, 18, 85);
  return (
    <svg viewBox="0 0 720 340" role="img" aria-label="STM32 Nucleo board">
      <defs>
        <pattern id="fr4w" patternUnits="userSpaceOnUse" width="140" height="140">
          <image href="/textures/fr4.png" width="140" height="140" />
        </pattern>
        <pattern id="metal2" patternUnits="userSpaceOnUse" width="160" height="160">
          <image href="/textures/brushed-metal.png" width="160" height="160" />
        </pattern>
        <filter id="glow2" x="-40%" y="-40%" width="180%" height="180%">
          <feGaussianBlur stdDeviation="6" result="b" />
          <feMerge>
            <feMergeNode in="b" />
            <feMergeNode in="SourceGraphic" />
          </feMerge>
        </filter>
      </defs>
      <ellipse cx="360" cy="318" rx="250" ry="14" fill="rgba(0,0,0,0.45)" />
      <g transform="translate(70 36)">
        <rect x="0" y="0" width="460" height="240" rx="8" fill="#d7dbe2" />
        <rect x="0" y="0" width="460" height="240" rx="8" fill="url(#fr4w)" opacity="0.28" />
        <rect x="0.7" y="0.7" width="458.6" height="238.6" rx="7.4" fill="none" stroke="rgba(0,0,0,0.12)" />
        {/* red ST-LINK island */}
        <path d="M0 0 H168 L148 240 H0 Z" fill="#c0392b" opacity="0.92" />
        <path d="M0 0 H168 L148 240 H0 Z" fill="url(#fr4w)" opacity="0.18" />
        <text x="14" y="22" fill="rgba(255,255,255,0.7)" fontSize="9" fontFamily="ui-monospace, monospace">
          ST-LINK/V2-1
        </text>
        <rect x="18" y="36" width="44" height="18" rx="3" fill="url(#metal2)" />
        <text x="22" y="48" fill="#222" fontSize="7">
          CN1 USB
        </text>
        <circle cx="30" cy="72" r="5" fill={ledOn ? "#3dff7a" : "#1a3a22"} filter={ledOn ? "url(#glow2)" : undefined} />
        <text x="40" y="75" fill="rgba(255,255,255,0.55)" fontSize="7">
          LD2
        </text>
        <circle cx="30" cy="90" r="5" fill="#ff4d4d" filter="url(#glow2)" />
        <text x="40" y="93" fill="rgba(255,255,255,0.55)" fontSize="7">
          PWR
        </text>
        <rect x="22" y="150" width="28" height="28" rx="4" fill="#111" />
        <circle cx="36" cy="164" r="8" fill="#2a2a2a" />
        <text x="22" y="194" fill="rgba(255,255,255,0.55)" fontSize="7">
          B1 USER
        </text>

        <text x="190" y="24" fill="rgba(0,0,0,0.4)" fontSize="9" fontFamily="ui-monospace, monospace">
          NUCLEO-F401RE · PHYSIO ELECTRIC
        </text>

        {/* MCU */}
        <g transform="translate(230 70)">
          <rect x="0" y="0" width="96" height="96" rx="3" fill="#111216" />
          <rect x="0" y="0" width="96" height="96" rx="3" fill="url(#metal2)" opacity="0.15" />
          <circle cx="10" cy="10" r="2.2" fill="#c9a24a" />
          <text x="18" y="50" fill="rgba(255,255,255,0.55)" fontSize="8">
            STM32
          </text>
          <text x="18" y="62" fill="rgba(255,255,255,0.35)" fontSize="7">
            F401RET6
          </text>
          {Array.from({ length: 12 }, (_, i) => (
            <g key={i}>
              <rect x={6 + i * 7} y="-4" width="4" height="6" fill="#c9b37a" />
              <rect x={6 + i * 7} y="94" width="4" height="6" fill="#c9b37a" />
            </g>
          ))}
        </g>

        {/* morpho hints */}
        {Array.from({ length: 18 }, (_, i) => (
          <g key={i}>
            <rect x="176" y={36 + i * 10} width="8" height="6" fill="#1a1a1a" />
            <rect x="430" y={36 + i * 10} width="8" height="6" fill="#1a1a1a" />
          </g>
        ))}

        {/* jumper to NTC */}
        <path d="M430 80 C 470 80, 490 58, 534 66" fill="none" stroke="#2d2d2d" strokeWidth="3" strokeLinecap="round" />
        <path d="M430 96 C 470 100, 490 110, 534 108" fill="none" stroke="#e24b4b" strokeWidth="3" strokeLinecap="round" />
      </g>

      {/* NTC / analog sensor */}
      <g transform="translate(534 48)" data-sensor="ntc">
        <rect x="0" y="0" width="100" height="130" rx="6" fill="#111820" stroke="#2a3544" />
        <text x="10" y="16" fill="rgba(255,255,255,0.4)" fontSize="8" fontFamily="ui-monospace, monospace">
          NTC 10K
        </text>
        <circle
          id="iot-sensor"
          cx="50"
          cy="58"
          r="24"
          fill={sensorFill}
          stroke="rgba(255,255,255,0.25)"
          filter={heatGlow > 0.2 ? "url(#glow2)" : undefined}
        />
        <path d="M50 34 v48" stroke="rgba(0,0,0,0.35)" strokeWidth="3" />
        {heatGlow > 0 && (
          <circle cx="50" cy="58" r={26 + heatGlow * 22} fill={sensorFill} opacity={0.12 + heatGlow * 0.2} />
        )}
        <text x="14" y="100" fill="white" fontSize="13" fontFamily="ui-monospace, monospace">
          {temp.toFixed(1)}°C
        </text>
        <text x="14" y="116" fill="rgba(255,255,255,0.45)" fontSize="8" fontFamily="ui-monospace, monospace">
          ADC {adc?.code ?? "—"}
        </text>
      </g>
    </svg>
  );
}
