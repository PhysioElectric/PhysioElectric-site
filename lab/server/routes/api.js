import { Router } from "express";
import { z } from "zod";
import { validate, asyncHandler } from "../middleware/validate.js";
import { computeLimiter } from "../middleware/security.js";
import { config } from "../config.js";
import { nowMs, withBudget } from "../services/math.js";
import { solveOde } from "../services/ode.js";
import { analyzeSignal, fourierSeries } from "../services/signal.js";
import { bodeAndStep } from "../services/control.js";
import { heatStep, electrostatics, waveStep, materials } from "../services/fem.js";
import { sensorThermal, stm32Adc } from "../services/thermal.js";

const r = Router();

const finiteNum = z.number().finite();

r.get("/health", (req, res) => {
  res.json({
    ok: true,
    service: "physio-electric-lab",
    version: "1.0.0",
    time: new Date().toISOString(),
    requestId: req.id,
    uptime: Math.round(process.uptime()),
  });
});

r.get("/meta", (_req, res) => {
  res.json({
    labs: ["iot", "matlab", "comsol"],
    boards: ["esp32", "stm32"],
    ode: ["lorenz", "vanderpol", "harmonic", "pid", "rc"],
    signals: ["sine", "square", "saw", "triangle", "pwm", "chirp", "noise", "sum"],
    materials: Object.fromEntries(
      Object.entries(materials).map(([id, m]) => [id, { name: m.name, alpha: m.alpha, k: m.k }]),
    ),
    limits: {
      grid: 80,
      fft: 4096,
      sources: 8,
      computeMs: config.computeBudgetMs,
    },
  });
});

r.post(
  "/sim/ode",
  computeLimiter(),
  validate(
    z.object({
      system: z.enum(["lorenz", "vanderpol", "harmonic", "pid", "rc"]),
      params: z.record(finiteNum).optional(),
      tEnd: finiteNum.optional(),
      dt: finiteNum.optional(),
    }),
  ),
  asyncHandler(async (req, res) => {
    const { result, ms } = withBudget(config.computeBudgetMs, (over) => solveOde(req.data, over));
    res.json({ ok: true, computeMs: ms, requestId: req.id, data: result });
  }),
);

r.post(
  "/sim/fft",
  computeLimiter(),
  validate(
    z.object({
      kind: z.enum(["sine", "square", "saw", "triangle", "pwm", "chirp", "noise", "sum"]),
      n: finiteNum.optional(),
      fs: finiteNum.optional(),
      freq: finiteNum.optional(),
      amp: finiteNum.optional(),
      phase: finiteNum.optional(),
      noise: finiteNum.optional(),
      duty: finiteNum.optional(),
    }),
  ),
  asyncHandler(async (req, res) => {
    const t0 = nowMs();
    const data = analyzeSignal(req.data);
    res.json({ ok: true, computeMs: nowMs() - t0, requestId: req.id, data });
  }),
);

r.post(
  "/sim/fourier",
  computeLimiter(),
  validate(
    z.object({
      wave: z.enum(["square", "saw", "triangle"]).optional(),
      harmonics: finiteNum.optional(),
      cycles: finiteNum.optional(),
    }),
  ),
  asyncHandler(async (req, res) => {
    const t0 = nowMs();
    const data = fourierSeries(req.data);
    res.json({ ok: true, computeMs: nowMs() - t0, requestId: req.id, data });
  }),
);

r.post(
  "/sim/control",
  computeLimiter(),
  validate(
    z.object({
      wn: finiteNum.optional(),
      zeta: finiteNum.optional(),
      k: finiteNum.optional(),
      delay: finiteNum.optional(),
    }),
  ),
  asyncHandler(async (req, res) => {
    const t0 = nowMs();
    const data = bodeAndStep(req.data);
    res.json({ ok: true, computeMs: nowMs() - t0, requestId: req.id, data });
  }),
);

r.post(
  "/sim/heat",
  computeLimiter(),
  validate(
    z.object({
      nx: finiteNum.optional(),
      ny: finiteNum.optional(),
      steps: finiteNum.optional(),
      material: z.enum(["copper", "aluminum", "silicon", "steel", "glass", "air", "fr4"]).optional(),
      diffusivity: finiteNum.optional(),
      ambient: finiteNum.optional(),
      field: z.array(finiteNum).max(80 * 80).optional(),
      sources: z
        .array(
          z.object({
            x: finiteNum,
            y: finiteNum,
            power: finiteNum.optional(),
            radius: finiteNum.optional(),
          }),
        )
        .max(8)
        .optional(),
    }),
  ),
  asyncHandler(async (req, res) => {
    const { result, ms } = withBudget(config.computeBudgetMs, (over) => heatStep(req.data, over));
    res.json({ ok: true, computeMs: ms, requestId: req.id, data: result });
  }),
);

r.post(
  "/sim/electro",
  computeLimiter(),
  validate(
    z.object({
      n: finiteNum.optional(),
      iters: finiteNum.optional(),
      electrodes: z
        .array(z.object({ x: finiteNum, y: finiteNum, v: finiteNum.optional(), r: finiteNum.optional() }))
        .max(6)
        .optional(),
    }),
  ),
  asyncHandler(async (req, res) => {
    const { result, ms } = withBudget(config.computeBudgetMs, (over) => electrostatics(req.data, over));
    res.json({ ok: true, computeMs: ms, requestId: req.id, data: result });
  }),
);

r.post(
  "/sim/wave",
  computeLimiter(),
  validate(
    z.object({
      n: finiteNum.optional(),
      steps: finiteNum.optional(),
      c: finiteNum.optional(),
      damp: finiteNum.optional(),
      u: z.array(finiteNum).max(72 * 72).optional(),
      prev: z.array(finiteNum).max(72 * 72).optional(),
      sources: z
        .array(
          z.object({
            x: finiteNum,
            y: finiteNum,
            amp: finiteNum.optional(),
            freq: finiteNum.optional(),
            phase: finiteNum.optional(),
            t: finiteNum.optional(),
          }),
        )
        .max(6)
        .optional(),
    }),
  ),
  asyncHandler(async (req, res) => {
    const { result, ms } = withBudget(config.computeBudgetMs, (over) => waveStep(req.data, over));
    res.json({ ok: true, computeMs: ms, requestId: req.id, data: result });
  }),
);

r.post(
  "/sim/sensor",
  validate(
    z.object({
      ambient: finiteNum.optional(),
      temp: finiteNum.optional(),
      humidity: finiteNum.optional(),
      dt: finiteNum.optional(),
      tau: finiteNum.optional(),
      tools: z
        .array(
          z.object({
            kind: z.string().max(16),
            distance: finiteNum,
            intensity: finiteNum.optional(),
          }),
        )
        .max(4)
        .optional(),
    }),
  ),
  (req, res) => {
    const th = sensorThermal(req.data);
    res.json({
      ok: true,
      requestId: req.id,
      data: { ...th, adc: stm32Adc(th.temp) },
    });
  },
);

export default r;
