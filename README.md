# Physio Electric

سایت گروه + آزمایشگاه شبیه‌سازی (IoT / MATLAB / COMSOL).

## اگر شبیه‌سازها را نمی‌بینی

فایل HTML را با دابل‌کلیک یا Preview خود VS Code باز نکن. باید سرور اجرا شود.

### در VS Code

```bash
git clone https://github.com/sympathiccore/PhysioElectric-site.git
cd PhysioElectric-site
```

سپس **File → Open Folder** روی همین پوشه.

ترمینال:

```bash
cd lab
npm install
npm run build
npm start
```

مرورگر:

- http://localhost:8080
- http://localhost:8080/simulations.html   ← شبیه‌سازها اینجاست

ویندوز: می‌توانی `start.bat` را دابل‌کلیک کنی.

## ساختار

- `simulations.html` — آزمایشگاه
- `lab/` — کد React + Express
- `scroll_page/scroll.html` — صفحه پروژه‌ها
