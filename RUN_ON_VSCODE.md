# اجرای پروژه روی VS Code

---

## ۱. چیزهایی که باید نصب کنی

| ابزار | نسخه | لینک |
|---|---|---|
| **Python** | ۳.۱۰ یا بالاتر (پیشنهاد: ۳.۱۲) | python.org/downloads |
| **VS Code** | آخرین نسخه | code.visualstudio.com |
| **Git** | هر نسخه‌ای | git-scm.com |

> ⚠️ **ویندوز:** موقع نصب پایتون حتماً تیک **«Add Python to PATH»** را بزن،
> وگرنه ترمینال دستور `python` را پیدا نمی‌کند.

### افزونه‌های VS Code

فقط دوتا لازم است (Ctrl+Shift+X → جستجو → Install):

1. **Python** (از Microsoft) — شامل Pylance، دیباگر و انتخاب مفسر
2. **Django** (از Baptiste Darthenay) — هایلایت قالب‌های `{% %}`

همین. هیچ چیز دیگری نیاز نیست — نه Node، نه دیتابیس جدا، نه Docker.

---

## ۲. گرفتن پروژه

```bash
git clone -b feature-Back-end https://github.com/PhysioElectric/PhysioElectric-site.git
cd PhysioElectric-site
code .
```

یا در VS Code: **File → Open Folder** روی پوشهٔ `PhysioElectric-site`.

---

## ۳. راه‌اندازی

ترمینال VS Code را باز کن: **Ctrl + `** (کلید بک‌تیک، زیر Esc)

### ویندوز (PowerShell)

```powershell
cd backend
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
copy .env.example .env
python manage.py migrate
python manage.py seed_content
python manage.py createsuperuser
python manage.py runserver
```

### مک و لینوکس

```bash
cd backend
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
python manage.py migrate
python manage.py seed_content
python manage.py createsuperuser
python manage.py runserver
```

بعد در مرورگر برو به **http://127.0.0.1:8000**

---

## ۴. انتخاب مفسر در VS Code

این مرحله را جا نینداز، وگرنه VS Code زیر `import django` خط قرمز می‌کشد:

**Ctrl+Shift+P** → تایپ کن `Python: Select Interpreter` → گزینه‌ای که داخلش
`backend/.venv` دارد را انتخاب کن.

---

## ۵. آدرس‌ها

| آدرس | چیست |
|---|---|
| http://127.0.0.1:8000 | صفحه اصلی |
| http://127.0.0.1:8000/?lang=en | نسخه انگلیسی |
| http://127.0.0.1:8000/admin/ | پنل مدیریت (با کاربری که ساختی) |
| http://127.0.0.1:8000/api/docs/ | مستندات تعاملی API |
| http://127.0.0.1:8000/api/v1/health/ | تست سلامت |

---

## ۶. دفعات بعد

فقط دو خط:

```powershell
.\.venv\Scripts\Activate.ps1     # ویندوز
python manage.py runserver
```

```bash
source .venv/bin/activate         # مک/لینوکس
python manage.py runserver
```

نشانهٔ فعال‌بودن محیط مجازی: `(.venv)` ابتدای خط ترمینال ظاهر می‌شود.

---

## ۷. میان‌برهای آماده

در پوشهٔ `backend/.vscode/` سه فایل گذاشتم:

**دیباگ** — کلید `F5` را بزن:
- `Django: runserver` — اجرا با دیباگر، می‌توانی breakpoint بگذاری
- `Django: tests` — اجرای تست‌ها با دیباگر

**تسک‌ها** — `Ctrl+Shift+P` → `Tasks: Run Task`:
- `setup (install + migrate + seed)` — راه‌اندازی کامل با یک کلیک
- `runserver`
- `test`

---

## ۸. دستورهای پرکاربرد

```bash
python manage.py runserver          # اجرای سرور
python manage.py test               # ۵۳ تست
python manage.py content_stats      # گزارش وضعیت محتوا
python manage.py seed_content       # پر کردن دوباره محتوا (بی‌خطر، تکراری نمی‌سازد)
python manage.py createsuperuser    # کاربر ادمین جدید
python manage.py makemigrations     # بعد از تغییر مدل‌ها
python manage.py migrate            # اعمال تغییرات دیتابیس
```

---

## ۹. خطاهای رایج

### `python : The term 'python' is not recognized`
پایتون در PATH نیست. یا دوباره نصب کن با تیک «Add to PATH»، یا `py` را
امتحان کن به‌جای `python`.

### `cannot be loaded because running scripts is disabled`
ویندوز اجرای اسکریپت را بسته است. PowerShell را **به‌عنوان Administrator** باز
کن و یک بار بزن:

```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
```

### `ModuleNotFoundError: No module named 'django'`
محیط مجازی فعال نیست. اول `activate` را بزن (باید `(.venv)` ببینی)، بعد
`pip install -r requirements.txt`.

### `That port is already in use`
پورت اشغال است. با پورت دیگری اجرا کن:

```bash
python manage.py runserver 8001
```

### VS Code زیر `import django` خط قرمز می‌کشد ولی برنامه اجرا می‌شود
مفسر اشتباه انتخاب شده. مرحلهٔ ۴ را انجام بده.

### `no such table: content_capability`
`migrate` را نزده‌ای:

```bash
python manage.py migrate
python manage.py seed_content
```

### صفحه بالا می‌آید ولی خالی است
`seed_content` را نزده‌ای — دیتابیس خالی است.

---

## ۱۰. دیتابیس

پیش‌فرض **SQLite** است: یک فایل `backend/db.sqlite3` که خودکار ساخته می‌شود.
هیچ نصب جداگانه‌ای نمی‌خواهد.

اگر خواستی PostgreSQL:

```bash
# در فایل .env
DATABASE_URL=postgres://user:pass@localhost:5432/physioelectric
```

و در `requirements.txt` خط `psycopg[binary]` را از کامنت خارج کن. تغییر کد لازم نیست.

---

## خلاصهٔ خیلی کوتاه

```
۱. پایتون ۳.۱۲ نصب کن (تیک Add to PATH)
۲. VS Code + افزونهٔ Python
۳. cd backend
۴. python -m venv .venv  →  activate
۵. pip install -r requirements.txt
۶. python manage.py migrate
۷. python manage.py seed_content
۸. python manage.py createsuperuser
۹. python manage.py runserver
۱۰. localhost:8000
```

---

# ضمیمه: رفع خطاهای Pylance

## خطاهایی مثل این

```
Import "django.conf" could not be resolved from source          (severity 4)
Import "drf_spectacular.views" could not be resolved            (severity 4)
```

**اول از همه: این‌ها ارور نیستند.** `severity: 4` یعنی Hint. کد کاملاً درست
است و اگر پکیج‌ها نصب باشند برنامه اجرا می‌شود. این فقط تحلیل‌گر ویرایشگر است.

## چرا دو پیام متفاوت دیدی

این تفاوت دقیقاً علت را مشخص می‌کند:

| پکیج | `py.typed` دارد؟ | پیام Pylance وقتی نصب نباشد |
|---|---|---|
| `django` | ❌ | `could not be resolved **from source**` |
| `rest_framework` | ❌ | `could not be resolved **from source**` |
| `drf_spectacular` | ✅ | `could not be resolved` (بدون from source) |

Pylance برای جنگو **stub آماده** دارد (typeshed)، پس نوع‌ها را می‌شناسد ولی
می‌گوید «خود پکیج را پیدا نکردم». برای `drf_spectacular` هیچ stub‌ای ندارد،
پس کلاً می‌گوید «پیدا نشد».

**نتیجه:** مفسری که VS Code انتخاب کرده، این پکیج‌ها را ندارد.

## سه حالت ممکن

### حالت ۱ — پکیج‌ها اصلاً نصب نشده‌اند
اگر `python manage.py runserver` هم خطا می‌دهد، این حالت است:

```powershell
cd backend
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

### حالت ۲ — نصب شده‌اند، ولی VS Code مفسر دیگری را انتخاب کرده
نشانه: در ترمینال `runserver` کار می‌کند، ولی ویرایشگر خط قرمز می‌کشد.

**Ctrl+Shift+P** → `Python: Select Interpreter` → گزینه‌ای که داخلش
`backend\.venv\Scripts\python.exe` است.

سپس **Ctrl+Shift+P** → `Developer: Reload Window`.

### حالت ۳ — بدون venv نصب کرده‌ای (نصب سراسری)
کار می‌کند ولی توصیه نمی‌شود. مفسر سراسری را انتخاب کن، یا بهتر: venv بساز.

## چطور مطمئن شوی کدام حالت است

در ترمینال VS Code بزن:

```powershell
python -c "import django, drf_spectacular; print(django.__file__)"
```

- **خطا داد** → حالت ۱، پکیج‌ها نصب نیستند
- **مسیر چاپ کرد** → پکیج‌ها هستند؛ مسیر را با چیزی که در نوار پایین VS Code
  (گوشه راست‌پایین، نام مفسر) نوشته مقایسه کن. اگر یکی نیستند → حالت ۲

## سریع‌ترین راه (۹۰٪ مواقع همین است)

```powershell
cd backend
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

بعد **Ctrl+Shift+P** → `Python: Select Interpreter` → `.venv` را انتخاب کن →
**Reload Window**.

## اگر باز هم نمایش داد

در `.vscode/settings.json` این را اضافه کن تا هشدار غیرواقعی جنگو خاموش شود
(در فایل settings.json پروژه گذاشته‌ام):

```jsonc
"python.analysis.diagnosticSeverityOverrides": {
  "reportMissingModuleSource": "none"
}
```

این فقط همان هشدار «from source» را ساکت می‌کند و روی خطاهای واقعی اثری ندارد.

## ⚠️ نکته درباره فایل settings.json قبلی

در نسخهٔ اولی که ساختم این خط بود:

```jsonc
"python.defaultInterpreterPath": "${workspaceFolder}/backend/.venv/bin/python"
```

`bin/python` مسیر **مک و لینوکس** است. روی ویندوز مسیر
`.venv\Scripts\python.exe` است، پس آن خط روی ویندوز مفسر را اشتباه ست می‌کرد.
حذفش کردم — بهتر است مفسر را دستی انتخاب کنی.
