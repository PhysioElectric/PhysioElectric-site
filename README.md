# Sampatec

سایت چندصفحه‌ای. اسکرول عمودی بین صفحات:

1. `page1.html`
2. `page2.html`
3. `page3.html` — پروژه‌های شاخص
4. `page4.html` — آزمایشگاه شبیه‌سازی **Physio Electric** (IoT / MATLAB / COMSOL)

## اجرا

```bash
cd lab
npm install
npm run build
NODE_ENV=production PORT=8080 npm start
```

- کل سایت: http://localhost:8080
- فقط آزمایشگاه: http://localhost:8080/page4.html

کد لاب داخل پوشهٔ `lab/` است.
