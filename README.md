# PhysioElectric — وب‌سایت دو‌زبانه پورتفولیو و بلاگ

وب‌سایت رسمی تیم **فیزیو‌الکتریک** با **PHP 8.3 + Apache + MySQL 8** و **Docker**؛ کاملاً دو‌زبانه
(فارسی به‌عنوان زبان پیش‌فرض با RTL + انگلیسی LTR)، با پنل مدیریت امن، سئوی عمیق و
طراحی هم‌راستا با ریپوییتوری `feature-Front-end` (Tailwind، فونت Vazirmatn/Inter،
لوکاید آیکن‌ها و پالت آبی-سرمای برند).

---

## 🚀 اجرای سریع

```bash
# 1) تنظیم متغیرهای محیطی
cp .env.example .env
#    -> حتماً ADMIN_PASSWORD را عوض کنید

# 2) بالا آوردن همه‌چیز
docker compose up -d --build

# 3) صبر کنید تا DB آماده شود (چند ثانیه) و بعد:
#    سایت:    http://localhost:8080      (خودش به /fa ریدایرکت می‌شود)
#    پنل:     http://localhost:8080/admin
#    ورود پنل: ایمیل و رمز تعریف‌شده در .env
```

> اگر می‌خواهید سایت روی پورت 80 باشد: در `.env` مقدار `APP_PORT=80` بگذارید.

برای ریست کامل دیتابیس (بازگشت به داده‌های اولیه):

```bash
docker compose down -v
docker compose up -d --build
```

---

## 🗂 ساختار پروژه

```
.
├── docker-compose.yml          # سرویس‌های app + db
├── Dockerfile                  # PHP 8.3 + Apache + pdo_mysql + gd
├── .env.example                # الگوی متغیرهای محیطی
├── db/
│   └── init.sql                # سکما + داده‌های اولیه (دو زبانه)
└── app/                        # DocumentRoot (mount روی /var/www/html)
    ├── .htaccess               # روتینگ SEO + هدرهای امنیتی
    ├── index.php               # Front Controller / روتر
    ├── config.php              # خواندن env
    ├── entrypoint.sh           # صبر برای DB + ساخت ادمین + پراخت
    ├── setup/
    │   ├── create_admin.php    # ساخت ادمین در اولین بوت (idempotent)
    │   └── db_ready.php        # چک TCP دیتابیس
    ├── core/
    │   ├── Database.php        # PDO (prepared statements)
    │   ├── Csrf.php            # توکن ضد CSRF
    │   ├── RateLimiter.php     # محدودسازی brute-force لاگین
    │   ├── Auth.php            # احراز هویت + Argon2id
    │   ├── HtmlSanitizer.php   # ساینایزر خروجی WYSIWYG (ضد XSS)
    │   ├── functions.php       # i18n، URL، SEO، CTA، هیلپرها
    │   └── lang.php            # دیکشنری UI فارسی/انگلیسی
    ├── models/                 # Category, Project, Post, Settings
    ├── controllers/
    │   ├── Home/About/Contact/Blog/Project  (صفحات عمومی)
    │   └── admin/              # Auth, Dashboard, Post, Project,
    │                           # Settings, Upload
    ├── views/
    │   ├── layouts/            # header (nav+SEO) / footer
    │   ├── home.php, about.php, contact.php, errors/404.php
    │   ├── blog/               # index, show
    │   ├── projects/           # index, category, show, _card
    │   └── admin/              # login, dashboard, posts/*,
    │                           # projects/*, settings/*, layouts/*
    ├── uploads/                # تصاویر (PHP execution غیرفعال)
    └── assets/
        ├── css/style.css       # سیستم طراحی (از ریپو اصلی استخراج شد)
        ├── fonts/              # Vazirmatn + Inter (لokal/offline)
        ├── js/tailwind.js      # بیلد Tailwind (آفلاین)
        ├── js/lucide.min.js    # لوکاید (آفلاین)
        ├── js/main.js          # انیمیشن‌ها، اسلایدر، FAQ، tg deep-link
        └── js/admin.js         # WYSIWYG + آپلود + مدیا + اسلاگ
```

## 🔗 نقشهٔ URL (روتر)

| URL | صفحه |
|---|---|
| `/` | 301 → `/fa` (زبان پیش‌فرض فارسی) |
| `/fa` / `/en` | صفحهٔ اصلی |
| `/fa/about` , `/fa/contact` | درباره ما / تماس |
| `/fa/blog` | آرشیو بلاگ |
| `/fa/blog/{slug}` | تک مطلب |
| `/fa/projects` | همهٔ پروژه‌ها + فیلتر دسته |
| `/fa/projects/simulation` | آرشیو پروژه‌های شبیه‌سازی |
| `/fa/projects/programming` | آرشیو پروژه‌های برنامه‌نویسی |
| `/fa/projects/web-development` | آرشیو پروژه‌های وب |
| `/fa/projects/{cat}/{slug}` | تک پروژه + CTA سفارش |
| `/admin` | پنل مدیریت (لایهٔ ورود) |
| `/admin/login` , `/admin/dashboard` | ورود / داشبورد |
| `/admin/posts` , `/admin/projects` | CRUD مطالب و پروژه‌ها |
| `/admin/settings` | تنظیمات سایت (تلگرام، ایمیل، هیرو و…) |

هر صفحهٔ عمومی در هر دو زبان قابل دسترس است:
`/en/projects/simulation/heat-exchanger-simulation` ← `/fa/projects/simulation/heat-exchanger-simulation`

## 🔐 امنیت (OWASP Top 10)

- **SQLi**: تمام کوئری‌ها PDO + prepared statement
- **XSS**: خروجی با `htmlspecialchars`؛ محتوای WYSIWYG هنگام ذخیره با
  ساینایزر whitelist‌محور پاک‌سازی می‌شود (تگ/attribut/URL نامعتبر حذف)
- **CSRF**: توکن سشن‌محور در همهٔ فرم‌ها + هدر `X-CSRF-TOKEN` برای آپلود AJAX
- **احراز هویت**: `password_hash` با Argon2id (فالبک bcrypt)،
  `session_regenerate_id` هنگام ورود، کوکیز HttpOnly + SameSite=Lax (+Secure در HTTPS)
- **آپلود امن**: whitelist پسوند (jpg/png/webp) + بررسی واقعی MIME با finfo +
  `getimagesize` + سقف ۲MB + نام‌گذاری تصادفی + پوشهٔ بدون اجرای PHP
- **Brute-force**: ۵ تلاش ناموفق در ۱۵ دقیقه = قفل IP (مستقر در MySQL)
- **سرور**: هدرهای nosniff / X-Frame-Options / Referrer-Policy،
  block dotfiles، بدون فهرست‌بندی دایرکتوری

## 🔎 سئو

- URL تمیز با پیشوند زبان + `.htaccess`
- `canonical`، **Hreflang دو‌سویه** (fa/en + x-default) در همهٔ صفحات
- `Title/Description/Keywords` پویا از دیتابیس (فیلدهای `meta_title_*` / `meta_desc_*`)
- Open Graph + Twitter Cards با `og:locale`
- **JSON-LD**: Organization + WebSite (خانه)، BlogPosting (مطالب)،
  CreativeWork (پروژه‌ها) — همگی بر اساس زبان فعال (با `JSON_HEX_TAG`
  برای جلوگیری از خرابی تگ script)
- تاریخ‌های فارسی با **تقویم جلالی** (الگوریتم خورشیدی pure-PHP،
  بدون وابستگی به ICU) + اعداد فارسی

## 📝 CTA و لیدزنی

دکمهٔ «سفارش پروژه» در **هر صفحهٔ پروژه** (سایدبار چسبان + روی کارت‌ها):
- **تلگرام**: در موبایل `tg://resolve?domain={id}` با fallback خودکار به `https://t.me/{id}`
- **ایمیل**: `mailto:` با سوژهٔ پیش‌پر «درخواست پروژه: {نام پروژه}»

نام کاربری تلگرام/ایمیل/تلفن از **پنل مدیریت ← تنظیمات** قابل تغییر است.

##  پنل مدیریت

- داشبورد با آمار + دسترسی سریع
- CRUD مطالب بلاگ و پروژه‌ها: عنوان/اسلاگ/خلاصه/محتوا (WYSIWYG)/
  meta/تصویر/وضعیت برای **هر دو زبان هم‌زمان**
- WYSIWYG سبک (بدون وابستگی خارجی): هدر، لیست، نقل‌قول، کد، لینک، تصویر
- آپلود تصویر با کتابخانهٔ رسانه؛ اسلاگ به‌صورت خودکار از عنوان انگلیسی ساخته می‌شود
- صفحهٔ تنظیمات: نام سایت، تلگرام، ایمیل، هیرو، فوتر (دو زبانه)

## ⚙️ نکات عملیاتی

- داده‌های اولیه: ۳ دسته، ۶ پروژه و ۴ مطلب (دو زبانه) از `db/init.sql`
- ادمین در اولین بوت با مقادیر `ADMIN_*` از `.env` ساخته می‌شود (اگر نباشد)
- دیتابیس روی volume `db-data` ماندگار است؛ seed فقط روی حجمِ کاملاً خالی اجرا می‌شود
- برای dev سریع‌تر: `APP_ENV=development` در `.env` (نمایش خطاها)
