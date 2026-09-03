# PhysioElectric — وب‌سایت دو‌زبانه پورتفولیو و بلاگ

وب‌سایت رسمی تیم **فیزیو‌الکتریک** با **PHP 8.3 + Apache + MySQL 8** و **Docker**؛ کاملاً دو‌زبانه
(فارسی به‌عنوان زبان پیش‌فرض با RTL + انگلیسی LTR)، با پنل مدیریت امن، سئوی عمیق و
طراحی هم‌راستا با ریپوییتوری `feature-Front-end` (Tailwind، فونت Vazirmatn/Inter،
لوکاید آیکن‌ها و پالت آبی-سرمای برند).

---

## 🚀 اجرای سریع

```bash
docker compose up -d --build
```

همین. هیچ فایل `.env` و هیچ تنظیمی لازم نیست.

```
سایت:     http://localhost:8080        (خودش به /fa ریدایرکت می‌شود)
پنل:      http://localhost:8080/admin
```

رمز عبور پنل به‌صورت **تصادفی و قوی** (۳۲ کاراکتر) ساخته می‌شود و فقط یک بار
تولید می‌شود. برای دیدنش:

```bash
docker compose logs app | grep -A3 "Admin panel"
```

```
[entrypoint]  Admin panel : http://localhost:8080/admin
[entrypoint]  Email       : admin@physioelectric.com
[entrypoint]  Password    : <رمز تصادفی>
```

رمزها در volume ای به نام `secrets` نگه داشته می‌شوند و **هرگز داخل ریپو ذخیره
نمی‌شوند**. اگر خواستی رمز خودت را بگذاری، یک فایل `.env` بساز (الگو:
`.env.example`)؛ هر متغیری که آنجا تعریف کنی بر مقدار تصادفی اولویت دارد.

> کانتینر به‌صورت غیر-root روی پورت داخلی **8080** گوش می‌دهد؛ `APP_PORT` فقط پورت
> سمت host را تعیین می‌کند (پیش‌فرض 8080).

برای ریست کامل (دیتابیس + رمزها + آپلودها):

```bash
docker compose down -v
docker compose up -d --build
```

> ⚠️ حساب ادمین تازه‌ساخته (چه با رمز تصادفی تولیدشده، چه با `ADMIN_PASSWORD` خودت)
> در **اولین ورود مجبور به تغییر رمز** است (`force_password_change`). این یک لایه‌ی
> امنیتی عمدی است تا رمزی که در لاگ چاپ شده یا بین افراد رد شده، هرگز بلندمدت نماند.

---

## 🔑 تولید رمز امن ادمین

رمزهای ادمین باید **قوی، تصادفی و یکتا** باشند. دو روش پیشنهادی پروژه — همان روشی که
خودِ `docker-compose.yml` برای ساخت `db_pass`/`admin_pass` استفاده می‌کند:

```bash
# روش ۱: تصادفی کامل (پیشنهادی برای رمزهایی که کسی حفظ نمی‌کند)
openssl rand -base64 24

# روش ۲: فقط حروف و اعداد (۳۲ کاراکتر، برای جاهایی که کاراکتر خاص دردسر می‌سازد)
tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32
```

جایگزینِ قابل‌یادآوری — **diceware** (۵ تا ۶ کلمه‌ی تصادفی از یک واژه‌نامه، مثل
EFF Large Wordlist):

```text
مثال (فقط برای نمایش، خودتان تاس/randomness واقعی بیندازید):
correct-horse-battery-staple
```

کلمات را با خط تیره یا فاصله جدا کنید و حداقل ۵ کلمه بردارید؛ طول نهایی معمولاً
۲۰+ کاراکتر می‌شود و طبق سیاست رمز (بخش ۶ CHANGES-SECURITY-2) از قید «ترکیب دسته‌ها»
معاف است.

**قوانین سیاست رمز عبور (اجرای سمت سرور، `app/core/PasswordPolicy.php`):**

| قانون | مقدار |
|---|---|
| حداقل طول در production | ۱۶ کاراکتر (در development ۱۲) |
| کمتر از ۲۰ کاراکتر | باید ≥ ۳ از ۴ دسته (بزرگ/کوچک/رقم/نماد) داشته باشد |
| ۲۰+ کاراکتر | از قید دسته‌ها معاف (منطق: طول بالا خودش قدرت است) |
| رمزهای لو رفته | چک با فایل محلی `app/setup/data/common-passwords.txt` (بدون API خارجی) |
| زیررشته‌ی ایمیل/نام | ممنوع |

**نکات مهم:**

- رمز هر حساب باید **یکتا** باشد (هرگز در سرویس دیگری استفاده نشود).
- رمز را در جایی ذخیره نکنید که لو برود: نه داخل commit/گیت‌هیستوری، نه در چت/ایمیل،
  و نه در history شل (`unset HISTFILE` یا `history -c` بعد از استفاده در ترمینال).
- رمز را هر **۹۰ تا ۱۸۰ روز** عوض کنید و **بلافاصله بعد از هر مورد مشکوک** (دسترسی
  غیرمجاز، لو رفتن password manager، آلودگی سیستم) rotate کنید.
- تغییر رمزِ هر ادمین فقط از مسیر «پنل ← حساب من» انجام می‌شود؛ حتی super_admin هم
  نمی‌تواند رمزِ دیگری را عوض کند (فقط می‌تواند حساب را بسازد/غیرفعال کند و کاربر در
  اولین ورود مجبور به تغییر رمز است).

---

## 🩺 عیب‌یابی

اولین قدم همیشه دیدن لاگ همان سرویسی است که بالا نیامده:

```bash
docker compose logs db        # یا: logs app  /  logs init
docker compose ps             # وضعیت و سلامت هر سرویس
```

| نشانه | علت | راه‌حل |
|---|---|---|
| `Conflict. The container name "/pe_..." is already in use` | کانتینر بازمانده از اجرای قبلی | `docker rm -f pe_db pe_app pe_init` |
| `dependency failed to start: ... db is unhealthy` | دیتابیس بالا نیامده | `docker compose logs db` — معمولاً مشکل از رمز یا مجوز datadir است |
| ورود به پنل با رمز درست رد می‌شود | رمز با volume قدیمی نمی‌خواند | `docker compose down -v` و بعد `up -d --build` |
| آپلود تصویر خطا می‌دهد | سرویس `init` اجرا نشده | `docker compose logs init` باید `[init] ready` داشته باشد |

برای شروع کاملاً تمیز (دیتابیس، رمزها و آپلودها از صفر):

```bash
docker compose down -v --remove-orphans
docker compose up -d --build
```

---

## 🧪 اجرای محلی بدون Docker (شبیه‌سازی)

اگر Docker در دسترس نیست، همان کد با وب‌سرور داخلی PHP اجرا می‌شود:

```bash
# 1) یک MySQL/MariaDB محلی با همان اسکما
mysql -uroot < db/init.sql
mysql -uroot -e "CREATE USER IF NOT EXISTS 'pe_user'@'%' IDENTIFIED BY 'pe_secret_2026';
                 GRANT ALL PRIVILEGES ON physioelectric.* TO 'pe_user'@'%'; FLUSH PRIVILEGES;"

# 2) متغیرهای محیطی + migration + ساخت ادمین (رمز >= 12 کاراکتر)
export DB_HOST=127.0.0.1 DB_NAME=physioelectric DB_USER=pe_user DB_PASS=pe_secret_2026
export TRUSTED_HOSTS=localhost,127.0.0.1
export ADMIN_EMAIL=admin@physioelectric.com ADMIN_PASSWORD='یک-رمز-حداقل-۱۲-کاراکتری'

cd app
php setup/migrate.php
php setup/create_admin.php

# 3) اجرا
php -S 0.0.0.0:8080 index.php     # http://localhost:8080  ->  /fa
```

برای دیدن stack trace در حالت توسعه، `export APP_ENV=development` را هم اضافه کن.

## ✅ تست‌ها

```bash
./tests/run-all.sh                      # همهٔ سوئیت‌ها
./tests/run-all.sh http://host:port     # روی یک سرور در حال اجرا
RESET_DB=0 ./tests/run-all.sh           # بدون پاک‌سازی دادهٔ تست
```

| سوئیت | پوشش |
|---|---|
| `tests/test_units.php` | زمان‌بندی هش، رگلاژ، allowlist هاست، CSRF، اسلاگ، سقف طول، تقویم جلالی (مقایسه با ICU روی ۶۰۸۸ تاریخ) |
| `tests/test_sanitizer.php` | ۴۴ سناریوی XSS و فیلتر URL |
| `tests/test_e2e.py` | ۸۰ ادعا روی HTTP واقعی: مسیرها، هدرهای امنیتی، لاگین، CRUD، آپلود، حذف پنل تنظیمات، خروج |

---

## 🗂 ساختار پروژه

```
.
├── docker-compose.yml          # سرویس‌های app + db (hardened)
├── Dockerfile                  # PHP 8.3 + Apache + pdo_mysql + gd(+webp)
├── .env.example                # الگوی متغیرهای محیطی (اختیاری)
├── tests/                      # unit + sanitizer + e2e
├── db/
│   └── init.sql                # سکما + داده‌های اولیه (دو زبانه)
└── app/                        # DocumentRoot (mount روی /var/www/html، read-only)
    ├── .htaccess               # روتینگ SEO + هدرهای امنیتی
    ├── index.php               # Front Controller / روتر
    ├── config.php              # خواندن env
    ├── entrypoint.sh           # صبر برای DB + ساخت ادمین + پراخت
    ├── setup/
    │   ├── create_admin.php    # ساخت ادمین در اولین بوت (idempotent)
    │   ├── migrate.php         # migrationهای idempotent برای volumeهای قدیمی
    │   └── db_ready.php        # چک TCP دیتابیس
    ├── core/
    │   ├── Database.php        # PDO (prepared statements بومی)
    │   ├── Security.php        # هدرهای امنیتی، CSP/nonce، ممیزی، گارد درخواست
    │   ├── Csrf.php            # توکن ضد CSRF + چرخش + بررسی Origin
    │   ├── RateLimiter.php     # محدودسازی brute-force (IP + حساب کاربری)
    │   ├── Auth.php            # احراز هویت + Argon2id + مهلت سشن
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
| `/admin/login` , `/admin/login/2fa` | ورود / مرحله‌ی دوم 2FA |
| `/admin/dashboard` | داشبورد |
| `/admin/forced-password` | تغییر رمز اجباری اولین ورود (تا انجام نشود، بقیه‌ی پنل بسته است) |
| `/admin/posts` , `/admin/projects` | CRUD مطالب و پروژه‌ها (editor+) |
| `/admin/team` , `/admin/messages` | اعضای تیم / پیام‌های دریافتی (editor+) |
| `/admin/users` , `/admin/users/create` | مدیریت ادمین‌ها (فقط super_admin) |
| `/admin/account` , `/admin/account/2fa/setup` | حساب من: رمز عبور و 2FA |

هر صفحهٔ عمومی در هر دو زبان قابل دسترس است:
`/en/projects/simulation/heat-exchanger-simulation` ← `/fa/projects/simulation/heat-exchanger-simulation`

## 🔐 امنیت (OWASP Top 10)

> کنترل‌های امنیتی فعال: هدرهای CSP/COOP/CORP با nonce، محافظت در برابر
> host-header injection، CSRF روی همهٔ نوشتن‌ها، rate-limit ورود، هش argon2id،
> ضدعفونی HTML محتوا، بازنویسی تصویرهای آپلودی با gd، و اجرای غیر-root کانتینر.

- **SQLi**: تمام کوئری‌ها PDO + prepared statement بومی (بدون emulation)
- **XSS**: خروجی با `htmlspecialchars`؛ محتوای WYSIWYG هنگام ذخیره با ساینایزر
  whitelist‌محور پاک‌سازی می‌شود؛ `javascript:`/`data:`/`vbscript:` و URLهای
  protocol-relative (`//evil.com`) رد می‌شوند
- **CSP**: `script-src` با nonce per-request، بدون `unsafe-inline` و بدون `unsafe-eval`
- **CSRF**: توکن سشن‌محور + چرخش هنگام ورود/خروج + بررسی Origin به‌عنوان لایهٔ دوم
- **احراز هویت**: Argon2id با پارامتر صریح، بدون اوراکل زمانی تشخیص کاربر،
  `session_regenerate_id` هنگام ورود، مهلت بی‌فعالیت و مطلق سشن،
  کوکی HttpOnly + SameSite=Lax (+Secure روی HTTPS)
- **آپلود امن**: `is_uploaded_file` → اندازهٔ واقعی از دیسک → whitelist پسوند →
  MIME با finfo → `getimagesize` + سقف ابعاد/پیکسل → **بازکدگذاری با GD** →
  نام تصادفی → سقف تعداد فایل
- **Brute-force**: ۵ تلاش ناموفق در ۱۵ دقیقه ⇒ قفل **IP و حساب کاربری**
  (مستقر در MySQL، fail-closed)؛ کد 2FA هم باکت rate-limit جداگانه دارد
- **چندادمینه / RBAC**: نقش‌های `super_admin`/`editor`/`viewer` با منبع حقیقت در
  دیتابیس (هر درخواست دوباره چک می‌شود)؛ گیت مرکزی در روتر؛ گارد «آخرین
  super_admin» و ممنوعیت تغییر/غیرفعال‌سازی حساب خود؛ تأیید مجدد با رمز عبور برای
  عملیات حساس روی کاربران
- **2FA اختیاری**: TOTP پیاده‌سازی‌شده‌ی داخلی (RFC 6238، بدون dependency) برای
  ورود ادمین؛ مرحله‌ی میانی بعد از پسورد، rate-limit جدا، فعال‌سازی/غیرفعال‌سازی
  در «حساب من»
- **سیاست رمز مشترک**: `PasswordPolicy` برای bootstrap و همه‌ی مسیرهای تغییر رمز
  (حداقل ۱۶ در production، لیست محلی رمزهای لو رفته، ممنوعیت زیررشته‌ی هویت)؛
  کاربران تازه‌ساخته در اولین ورود مجبور به تغییر رمزند
- **پیوست‌های فرم تماس**: تطبیق MIME واقعی با finfo (نه فقط پسوند)، گارد
  zip-bomb (تعداد entry + حجم uncompressed، fail-closed بدون ext-zip)
- **CAPTCHA اختیاری**: Cloudflare Turnstile روی فرم عمومی، وریفای سمت سرور قبل از
  throttle؛ بدون کلیدهای env کاملاً غیرفعال است
- **Host-Header Injection**: URLهای مطلق فقط از `SITE_BASE_URL` یا `TRUSTED_HOSTS`
- **سرور/کانتینر**: غیر-root، `cap_drop: ALL`، سورس `:ro`، `disable_functions`،
  `ServerTokens Prod`، `TraceEnable Off`، HSTS/COOP/CORP، بدون فهرست‌بندی دایرکتوری
- **لاگ امنیتی**: `Security::audit()` — بدون رمز عبور، توکن یا شناسهٔ سشن

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
- **نقش‌ها**: super_admin (مدیریت کاربران + همه‌چیز)، editor (محتوا)، viewer
  (فقط مشاهده) — دکمه‌ها و روت‌های ویرایش برای viewer مخفی/۴۰۳ می‌شوند
- **مدیریت ادمین‌ها** (فقط super_admin): ساخت/ویرایش/غیرفعال‌سازی با تأیید مجدد
  رمز خودِ مجری؛ «آخرین super_admin» و «حساب خود» هرگز قابل غیرفعال‌سازی/حذف/تنزل نیستند
- **حساب من**: تغییر رمز عبور خود (با رمز فعلی) + فعال‌سازی TOTP 2FA
  (کلید/otpauth URI نمایش داده می‌شود؛ بدون وابستگی خارجی)

## ⚙️ نکات عملیاتی

- داده‌های اولیه: ۳ دسته، ۶ پروژه و ۴ مطلب (دو زبانه) از `db/init.sql`
- ادمین در اولین بوت با مقادیر `ADMIN_*` از `.env` ساخته می‌شود (اگر نباشد)
- دیتابیس روی volume `db-data` ماندگار است؛ seed فقط روی حجمِ کاملاً خالی اجرا می‌شود
- برای dev سریع‌تر: `APP_ENV=development` در `.env` (نمایش خطاها)
