import { clamp, finite } from "./math.js";

/**
 * Newton's law of cooling + inverse-square-ish radiative/convective coupling
 * from a movable heat tool to a point sensor.
 */
export function sensorThermal(input) {
  const ambient = clamp(finite(input.ambient, 24.5), -20, 60);
  const temp = clamp(finite(input.temp, ambient), -30, 180);
  const humidity = clamp(finite(input.humidity, 42), 5, 99);
  const dt = clamp(finite(input.dt, 0.05), 0.01, 0.25);
  const tau = clamp(finite(input.tau, 1.35), 0.2, 8);
  const tools = Array.isArray(input.tools) ? input.tools.slice(0, 4) : [];

  let coupling = 0;
  let targetBoost = 0;
  for (const tool of tools) {
    const d = Math.max(0.004, finite(tool.distance, 1));
    const intensity = clamp(finite(tool.intensity, 0), 0, 1);
    const kind = tool.kind || "flame";
    const tSrc =
      kind === "flame" ? 420 :
      kind === "heatgun" ? 260 :
      kind === "iron" ? 330 :
      kind === "hand" ? 34.5 : 80;
    const reach = kind === "hand" ? 0.09 : kind === "heatgun" ? 0.22 : 0.16;
    const w = intensity * Math.exp(-d / reach);
    coupling += w;
    targetBoost += w * (tSrc - ambient);
  }

  const Tinf = ambient + targetBoost / (1 + coupling * 0.15);
  const next = temp + (Tinf - temp) * (1 - Math.exp(-dt / tau));
  const noise = (Math.random() - 0.5) * (0.04 + coupling * 0.08);

  // humidity drops slightly as air near the sensor warms
  const rhTarget = humidity - Math.max(0, next - ambient) * 0.35;
  const rh = humidity + (clamp(rhTarget, 8, 98) - humidity) * 0.08;

  return {
    temp: clamp(next + noise, -30, 180),
    humidity: clamp(rh + (Math.random() - 0.5) * 0.12, 5, 99),
    coupling,
    equilibrium: Tinf,
  };
}

export function stm32Adc(tempC, vref = 3.3, bits = 12) {
  // Fake NTC / LM35-ish: 10mV/°C + 0.5V offset
  const volts = clamp(0.5 + 0.01 * tempC, 0, vref);
  const max = (1 << bits) - 1;
  const code = Math.round((volts / vref) * max);
  return { volts, code, bits, vref };
}
