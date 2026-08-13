# Physio Electric — Simulation Atelier

صفحهٔ آزمایشگاه شبیه‌سازی گروه **Physio Electric** با تم Liquid Glass.

## Labs

- **IoT / MCU** — برد تعاملی ESP32 و STM32، سنسور دما که با نزدیک‌کردن شعله/هویه گرم می‌شود، GPIO، PWM، سریال، تلمتری WebSocket
- **MATLAB** — Lorenz، Van der Pol، PID، FFT، سری فوریه، Bode — حل روی هستهٔ Node
- **COMSOL** — انتقال حرارت، الکترواستاتیک، معادلهٔ موج

## Security

Helmet (CSP)، CORS allowlist، rate limit، Zod، سقف JSON ۳۲کیلوبایت، بودجهٔ محاسبه، محدودیت اتصال WS، بدون `eval`.

## Run

```bash
npm install
npm run build
NODE_ENV=production PORT=8080 npm start
```

توسعه:

```bash
npm run dev
```

API: `/api/health` · `/api/meta` · `/api/sim/*` · WS `/ws/lab`
