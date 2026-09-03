# پکیج هاردنینگ امنیتی ۲ — چند-ادمینی (RBAC)، سیاست رمز عبور، 2FA، CAPTCHA و مقاوم‌سازی پیوست‌ها

تاریخ: 2026-09-04 — بر پایه‌ی برنچ `bugfix` (کامیت bdfb887) روی برنچ کاری `feature/security-hardening-2`

فایل‌های این پکیج را با همان مسیرها روی پروژه کپی کنید. برای دیتابیس‌های قدیمی،
`app/setup/migrate.php` (اجرای خودکار در entrypoint) به‌صورت idempotent ستون‌های جدید را اضافه
می‌کند؛ اسکیمای کامل و نهایی در `db/init.sql` است.

---

## ۱. پیوست‌های فرم تماس/ویزارد — تأیید MIME واقعی + گارد بمب Zip
**فایل‌ها:** `app/controllers/InquiryController.php`

**ریسک قبلی:** فقط پسوند نام فایل و سایز بررسی می‌شد؛ یک فایل `evil.php` که به `resume.pdf` تغییر نام
داده باشد (یا هر محتوای دلخواه با پسوند مجاز) پذیرفته می‌شد و در `/uploads/attachments/` ذخیره و بعداً
دانلود می‌شد. همین‌طور یک `zip` با هزاران ورودیِ فوق‌فشرده می‌توانست هنگام باز شدن توسط ادمین، دیسک/حافظه را اشباع کند (zip-bomb).

**تغییر:**
- قبل از `move_uploaded_file`، نوع واقعی فایل با `finfo(FILEINFO_MIME_TYPE)` خوانده می‌شود و باید
  دقیقاً با نگاشت سختگیرانه‌ی پسوند→MIME یکی باشد: `pdf→application/pdf`، `zip→application/zip`،
  `doc→` انواع واقعی OLE2/MS Office، `docx→` MIME واقعی OOXML (یا `application/zip` چون OOXML خود
  کانتینر ZIP است)، `png/jpg/jpeg→` همان نوع تصویر واقعی. ناهماهنگی → رد با 415 و ثبت
  `Security::audit('inquiry.attachment_mime_mismatch')`.
- برای `zip`، آرایه با `ZipArchive` پیش از جابجایی باز می‌شود: بیش از ۵۰ ورودی یا بیش از ۵۰MB محتوای
  uncompressed → رد و ثبت `inquiry.zip_bomb_rejected`. اگر اکستنشن `zip` روی سرور نباشد → **fail closed**
  (رد درخواست + ثبت `inquiry.zip_check_unavailable`).
- محدودیت‌های قبلی حفظ شدند: حداکثر ۳ فایل، هر فایل ≤ ۲MB.

**تست (قبل/بعد):** قبل: فایل PHP با پسوند `.pdf` پذیرفته می‌شد؛ بعد: 415 و رد ✔. PNG با پسوند `.jpg`
→ رد ✔. `zip` با ۶۰ ورودی → رد ✔. `zip` با ~۵۵MB خروجی (فایل فشرده <۲MB) → رد ✔. zip/docx سالم →
پذیرش و ذخیره در `/uploads/attachments/` ✔. پیام بدون پیوست همچنان کار می‌کند ✔. (تست خودکار در
`tests/security_e2e.php` بخش ۱.)

## ۲. CAPTCHA اختیاری (Cloudflare Turnstile) — پیش‌فرض خاموش
**فایل‌ها:** `app/core/Captcha.php` (جدید)، `app/controllers/InquiryController.php`،
`app/core/Security.php` (افزودن سرچشمه به CSP)، `views/contact.php` + `app/assets/js/contact.js`
(ویجت، بدون `unsafe-inline` — با nonce)، `app/config.php` (کلیدهای `CAPTCHA_PROVIDER/SITE_KEY/SECRET_KEY`)،
`docker-compose.yml`/`.env.example`/`Dockerfile` (پاس‌دادن env و اکستنشن curl).

**ریسک قبلی:** فرم عمومی تماس/ویزارد فقط با CSRF + honeypot + throttle محافظت می‌شد؛ بات‌ها می‌توانستند
سهمیه‌ی IP را بسوزانند یا با ولوم بالا صندوق پیام‌ها را پر کنند.

**تغییر:** با فعال‌سازی `CAPTCHA_PROVIDER=turnstile` + دو کلید، ویجت در فرم رندر و **همیشه سمت سرور**
قبل از بلوک throttle بررسی می‌شود (`siteverify`). اگر خاموش باشد یا کلیدی ناقص باشد، کل لایه بی‌اثر است
(نصب‌های فعلی بدون تغییر کار می‌کنند) و هیچ سرچشمه‌ی خارجی به CSP اضافه نمی‌شود. هر خطای تأیید → 422 با
کد `captcha` و ثبت در audit. بدون توکن/عدم دسترسی به تأمین‌کننده/نبود curl → fail closed.

**تست:** سرور دوم با CAPTCHA روشن (کلید ساختگی): صفحه شامل ظرف ویجت + اسکریپت Turnstile است و CSP شامل
`https://challenges.cloudflare.com` در `script-src/connect-src/frame-src` ✔. ارسال بدون توکن →
422 `captcha` ✔. توکن جعلی با سکرت نامعتبر → 422 `captcha` (fail closed) ✔. سرور بدون CAPTCHA: هیچ
اثری از ویجت/Turnstile در HTML و CSP نیست و فرم عادی کار می‌کند ✔. (بخش ۵ e2e + بخش ۰.)

## ۳. «حساب من» — تغییر رمز عبور توسط خود ادمین
**فایل‌ها:** `app/controllers/admin/AccountController.php` (جدید — `SettingsController` بی‌روت بود)،
مسیرهای جدید در `app/index.php` (`/admin/account`، `/admin/account/password`، `/admin/forced-password`)،
ویوهای `app/views/admin/account/…`.

**ریسک قبلی:** هیچ راهی برای تغییر رمز عبور خودِ ادمین‌ها بدون بازنشانی دستی دیتابیس وجود نداشت؛ رمزهای
ساخته‌شده توسط مدیر (حتی ضعیف) تا ابد معتبر می‌ماندند.

**تغییر:**
- رمز فعلی با `password_verify` تأیید می‌شود (reauth)؛ رمز جدید باید با رمز فعلی فرق داشته باشد
  (مقایسه روی هش) و از `PasswordPolicy::validate()` رد شود؛ هش با همان گزینه‌های Argon2id سراسری ساخته می‌شود.
- بعد از موفقیت: `session_regenerate_id()` + `Csrf::rotate()` + audit `account.password_changed`.

**تست:** تغییر با رمز فعلی درست → 302 و لاگین مجدد با رمز جدید ✔. رمز ضعیف/لو رفته → رد با پیام سیاست ✔.
رمز فعلی نادرست → رد + audit `account.reauth_failed` ✔. (بخش ۳ e2e.)

## ۴. ورود دومرحله‌ای TOTP برای ادمین (اختیاری، پیش‌فرض غیرفعال)
**فایل‌ها:** `app/core/Totp.php` (جدید، RFC 6238 خالص با HMAC-SHA1، پنجره‌ی ±۱ دوره) — بدون کتابخانه،
`app/core/Auth.php` (چالش ۶۰۳-ثانیه‌ای بین رمز و جلسه)، `app/controllers/admin/AuthController.php` +
`AccountController.php` (فعال/غیرفعال‌سازی)، ویوهای `login2fa.php`/`twofa-setup.php`، ستون‌های
`users.totp_secret`/`totp_enabled` در `migrate.php` + `init.sql`.

**ریسک قبلی:** فقط یک رمز عبور بین ادمین و پنل بود؛ رمزِ لو رفته = دسترسی کامل.

**تغییر:** بعد از رمز درست، اگر حساب `totp_enabled=1` باشد، یک مرحله‌ی ۶ رقمی میانی سرو می‌شود و جلسه‌ی
واقعی فقط پس از `hash_equals` روی کد (با رید-چک وضعیت حساب از DB در همان لحظه) ساخته می‌شود؛ سپس
`session_regenerate_id` و چرخش CSRF. چالش ۱۰ دقیقه TTL دارد، برای هر حساب rate-limit جدا دارد و اگر
حساب وسط چالش غیرفعال/۲FA خاموش شود، چالش ابطال می‌شود (بدون downgrade ساکت). غیرفعال → هیچ تغییری در
جریان قبلی لاگین نیست.

**تست:** فعال‌سازی با کد درست+رمز فعلی → `totp_enabled=1` ✔. کد غلط → رد ✔. ورود بعد از فعال‌سازی →
ریدایرکت به مرحله‌ی 2FA ✔. کد غلط در مرحله‌ی 2FA → 401 ✔. کد درست → 302 به داشبورد ✔. غیرفعال‌سازی با
رمز → ورود عادی بدون مرحله‌ی دوم ✔. صحت پیاده‌سازی RFC 6238 با تست واحد آینه‌ای (کد فعلی/±۳۰ ثانیه/کد
۳ دوره قدیمی) ✔. (بخش ۴ e2e + `tests/security_units.php`.)

## ۵. CI سبک
**فایل‌ها:** `.github/workflows/security.yml` (جدید)، `.hadolint.yaml` (جدید).

**ریسک قبلی:** هیچ بررسی خودکاری؛ خطای Syntax/تصویر ناایمن فقط هنگام دیپلوی دیده می‌شد.

**تغییر:** lint همه‌ی PHPها با `php -l` + `node --check` روی باندل‌های JS؛ PHPStan سطح پایه فقط با
`phpstan.phar` مستقل (بدون composer)؛ `hadolint` روی Dockerfile (فقط DL3008 با توجیه نادیده گرفته شده)؛
اسکن `trivy` روی ایمیج نهایی با شکست روی HIGH/CRITICAL + آپلود SARIF. اجرا روی bugfix، bugfix-hardened،
main، develop، release و PRها.

## ۶. سیاست رمز عبور سراسری و مشترک
**فایل‌ها:** `app/core/PasswordPolicy.php` (جدید)، `app/setup/create_admin.php`، `app/entrypoint.sh`،
`app/setup/data/common-passwords.txt` (جدید، ~۴۱۰ خط، <۵۰۰KB)، بخش «🔑 تولید رمز پسورد امن ادمین» در README.md.

**ریسک قبلی:** حداقل طول ۱۲ کاراکترِ پراکنده؛ چند کپی مستقل از قانون؛ هیچ فیلتر «رمزهای لو رفته»؛
`create_admin.php` هر رمزی را می‌پذیرفت و هیچ راهنمای تولید رمز امن در README نبود.

**تغییر:**
- `PasswordPolicy::validate(string): array{ok:bool, reason:?string}` تنها منبع قانون است: production حداقل
  ۱۶ کاراکتر (dev: ۱۲)؛ زیر ۲۰ کاراکتر باید ≥۳ از ۴ کلاس را داشته باشد؛ ۲۰+ کاراکتر (خروجی openssl/diceware)
  از قانون کلاس‌ها معاف است؛ blocklist آفلاینِ رمزهای رایجِ لو رفته (فایل محلی — `allow_url_fopen` خاموش،
  بنابراین هیچ واکشی از راه دور در کار نیست)؛ رد زیررشته‌ی معنی‌دار ایمیل/نام (≥۴ کاراکتر).
- `create_admin.php` و تمام مسیرهای تغییر رمز (حساب من، ساخت کاربر توسط super_admin، چرخش اجباری)
  فقط از همین کلاس استفاده می‌کنند؛ ادمین اول با `role='super_admin'` و `force_password_change=1` ساخته می‌شود.
- `entrypoint.sh`: بررسی سریع طول (۱۶/۱۲ بر اساس APP_ENV) + راهنمای خطا + یادآوری چرخش رمز در اولین ورود؛
  قانون نهایی همان PHP است.
- README: تولید با `openssl rand -base64 24`، `tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32`،
  diceware ۵–۶ کلمه‌ای؛ جدول سیاست؛ یکتا بودن و چرخش ۹۰–۱۸۰ روزه و پس از هر شک؛ هرگز ذخیره در چت/تاریخچه‌ی ترمینال.

**تست:** کوتاه (<۱۶)، لو رفته (`Qwerty1234567890` و نمونه‌های مشابه از لیست)، و حاوی زیررشته‌ی ایمیل →
رد با پیام مشخص ✔. خروجی ۳۲ کاراکتری alnum تصادفی → پذیرش ✔. (تست‌های واحد + تست دستی `create_admin.php`؛
پس‌از تست، ردیف تستی حذف شد.) `php tests/security_units.php` → 27/27 ✔.

## ۷. چند-ادمینی (RBAC) + پنل «مدیریت ادمین‌ها» + تغییر اجباری رمز در اولین ورود
**فایل‌ها:** `app/index.php` (روت‌ها و گیت‌های مرکزی)، `app/core/Auth.php`، `app/models/UserModel.php`،
`app/controllers/admin/AdminUserController.php` (جدید)، ویوهای `app/views/admin/users/*` (جدید)،
`app/views/admin/layouts/header.php` (ورودی‌های سایدبار)، `db/init.sql` + `app/setup/migrate.php`
(ستون‌ها)، `app/core/functions.php` + ویوهای محتوا (گیت UI فقط برای نمایشگر).

**ریسک قبلی:** فقط یک ادمین تمام‌قدرت وجود داشت (بدون نقش، بدون مدیریت کاربران)؛ اگر حساب لو می‌رفت یا
باید به کسی دسترسی میزبان محدود داد، راهی جز دادن کلید کل پنل یا دستکاری DB نبود.

**تغییر:**
- **اسکیمای جدید users (idempotent در migrate.php):** `role ENUM('super_admin','editor','viewer')`
  (پیش‌فرض `super_admin`)، `force_password_change` (پیش‌فرض ۱ برای حساب‌های جدید)، `created_by` (نشانِ
  سازنده)، `totp_secret/totp_enabled`. `is_active` همچنان تنها «حذف» فیزیکی است؛ هیچ ردیفی از users حذف نمی‌شود.
- **Auth:** `role()/hasRole()/requireRole()` — `requireRole` خروج 403 + audit `authz.denied`. `Auth::check()`
  نقش و پرچم اجبار را در **هر درخواست** از DB می‌خواند، پس تغییر نقش روی نشست‌های باز هم بی‌درنگ اثر دارد.
- **گیت‌های مرکزی index.php:** `/admin/users*` فقط `super_admin`؛ هر POST محتوایی editor|super؛ صفحات
  create/edit/delete/upload/media برای viewer حتی در GET ممنوع؛ `account*` و `forced-password` برای همه‌ی
  نقش‌ها باز (هر ادمین باید بتواند رمز خودش را عوض کند).
- **AdminUserController (super_admin فقط، منطق بدون Csrf — حفاظت مرکزی):** لیست/ساخت/ویرایش/toggle/delete؛
  ساخت: ایمیل یکتا، رمزِ انتخاب‌شده توسط سازنده که باید از PasswordPolicy رد شود (مسیر env-تصادفی حذف شده)،
  نقش از allowlist، `force_password_change=1`، audit `admin.user_created`؛ ویرایش فقط name/role/is_active
  (تغییر رمز دیگران عمداً ممکن نیست؛ فقط صاحب حساب در «حساب من»).
- **نام‌شکنی‌های سمت سرور:** هیچ‌کس نمی‌تواند خودش را غیرفعال/حذف کند؛ نقش خودش را عوض کند (پین‌شده)؛
  و آخرین `super_admin` فعال هرگز حذف/غیرفعال/تنزل نمی‌شود — COUNT داخل همان تراکنش، هم پیش از نوشتن و هم
  پس از آن (SELECT…FOR UPDATE)، به‌گونه‌ای که حتی رقابت هم‌زمان هم صفر super فعال باقی نگذارد.
- **عملیات حساس** (ساخت ادمین، تغییر نقش، غیرفعال‌سازی/حذف) نیازمند `current_password` خودِ عمل‌کننده است
  (دوباره با `password_verify`) — نشستِ دزدیده‌شده به‌تنهایی کافی نیست.
- **تغییر اجباری در اولین ورود:** با `force_password_change=1`، بعد از لاگین موفق همه‌ی مسیرهای ادمین
  (به‌جز خود صفحه‌ی چرخش) به `/admin/forced-password` ریدایرکت می‌شوند؛ اعمال در گیت مرکزی **و** درون
  `Auth::check()`؛ تکمیل → پاک‌شدن پرچم + `session_regenerate_id` + `Csrf::rotate` + audit
  `account.forced_password_change_completed`.
- **UI:** دکمه‌های ساخت/ویرایش/حذف محتوا برای نقش viewer با تابع جدید `admin_can_edit()` در ویوها مخفی می‌شوند
  (کنترل واقعی همان سمت سرور است)؛ صفحات read برای viewer باز می‌مانند.

**تست (بخش‌های ۲–۳ e2e + ۳n):** ساخت editor/viewer/super دوم (با reauth و رمز قوی) ✔؛ بدون
`current_password` یا با رمز غلط → رد ✔؛ رمز لو رفته → رد ✔؛ نقش خارج از enum → رد ✔؛ deactivate/delete روی
خود → رد (حتی با رمز درست) ✔؛ نقش خودی قابل تغییر نیست ✔؛ در پنجره‌ای که فقط یک super فعال مانده هیچ مسیرِ
حذف/تنزل خودی جواب نمی‌دهد و شمارش `≥۱` پس از هر عملیات برقرار است ✔؛ ورود اول editor/viewer/super →
بلاک کامل تا چرخش رمز ✔ (dashboard/posts/create هم 302 می‌شوند)؛ بعد از چرخش: editor به محتوا دسترسی دارد
ولی `/admin/users` → 403 (GET و POST) ✔؛ viewer همه‌جا read-only (داشبورد/لیست‌ها/جزئیات 200؛
create/edit/upload/messages-toggle → 403) ✔ ولی می‌تواند رمز خودش را عوض کند ✔.

## تست‌های نهایی (همه روی MariaDB واقعی + دو سرور php -S)
- `php -l` روی همه‌ی فایل‌های `app/` ✔ — `tests/security_units.php`: **27/27 ✔**
- `tests/security_e2e.php` (HTTP واقعی + DB واقعی): **86/86 ✔** — شامل پیوست‌ها، RBAC، پنل کاربران،
  چرخش اجباری، حساب من، 2FA، CAPTCHA (روشن/خاموش) و پاک‌سازی کامل ردیف‌های تستی در پایان.
- `migrate.php` دوبار اجرا شد: دفعه‌ی دوم «schema up to date» (idempotent) ✔؛ `SHOW COLUMNS` با
  `db/init.sql` یکسان ✔.
- تغییرات UI با گیت viewer روی صفحات عمومی/پنل بازبینی شد؛ گیت‌های سمت سرور دست‌نخورده و مرجع هستند ✔.
- سکرت/کلید واقعی هیچ‌جا hardcode نشده؛ همه‌چیز از env یا `/run/secrets` از طریق
  `Config::ALLOWED_KEYS` خوانده می‌شود (همان مدل `db_pass`/`admin_pass` قبلی) ✔.
