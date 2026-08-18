# PhysioElectric — Backend

بک‌اند جنگو برای سایت PhysioElectric.

محتوای سایت در دیتابیس نگه‌داری می‌شود و از پنل ادمین ویرایش می‌شود — بدون
نیاز به دست‌زدن به HTML یا دیپلوی مجدد.

> 🖥️ راهنمای گام‌به‌گام اجرا روی VS Code (همراه رفع خطاهای رایج Pylance):
> [`RUN_ON_VSCODE.md`](RUN_ON_VSCODE.md)

## راه‌اندازی

```bash
cd backend
python3 -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt

cp .env.example .env          # و SECRET_KEY را عوض کن
python manage.py migrate
python manage.py seed_content
python manage.py createsuperuser
python manage.py runserver
```

| آدرس | توضیح |
|---|---|
| `/` | صفحه اصلی، رندرشده از دیتابیس |
| `/?lang=en` | نسخه انگلیسی |
| `/admin/` | پنل مدیریت محتوا |
| `/api/docs/` | مستندات تعاملی API |
| `/api/v1/health/` | سلامت سرویس |

## امکانات

- **۱۵ مدل** — تنظیمات سایت، توانمندی‌ها، پروژه‌ها، مقالات، مراحل فرایند،
  سوالات متداول، تیم، نظرات، آمار، بازدید، پیام تماس، مشترکین خبرنامه
- **دوزبانه** — ستون‌های فارسی و انگلیسی جدا، با `?lang=` در API
- **REST API نسخه‌بندی‌شده** با OpenAPI 3، فیلتر، جستجو، صفحه‌بندی و throttling
- **فرم تماس** با honeypot، محدودیت نرخ و هش‌کردن IP
- **کش** با باطل‌سازی خودکار — `/api/v1/home/` روی cache hit صفر کوئری می‌زند
- **سئو** — `sitemap.xml`، `robots.txt`، متای Open Graph
- **۵۳ تست** · `check --deploy` بدون هشدار

جزئیات کامل: [`backend/README.md`](backend/README.md)

## VS Code

پوشهٔ `.vscode/` آماده است:

- **F5** → اجرای سرور با دیباگر (یا اجرای تست‌ها)
- **Ctrl+Shift+P → Tasks: Run Task** → `setup` کل نصب و migrate و seed را یک‌جا انجام می‌دهد
- قالب‌های `templates/` به‌صورت `django-html` شناسایی می‌شوند

بعد از ساخت محیط مجازی، حتماً **Ctrl+Shift+P → Python: Select Interpreter** را
بزن و `.venv` را انتخاب کن.

## دیتابیس

پیش‌فرض SQLite، بدون هیچ تنظیمی. برای PostgreSQL فقط در `.env`:

```
DATABASE_URL=postgres://user:pass@localhost:5432/physioelectric
```

و `psycopg[binary]` را در `requirements.txt` از کامنت خارج کن. تغییر کد لازم نیست.

## استقرار

```bash
export DJANGO_SETTINGS_MODULE=config.settings.prod
export SECRET_KEY="..."
python manage.py migrate
python manage.py collectstatic --noinput
gunicorn config.wsgi:application --bind 0.0.0.0:8000 --workers 4
```
