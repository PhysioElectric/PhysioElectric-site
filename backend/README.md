# PhysioElectric — Backend

بک‌اند جنگو برای سایت PhysioElectric. محتوای صفحه از دیتابیس می‌آید، پس ویرایش
سایت از پنل ادمین انجام می‌شود و نیازی به دست‌زدن به HTML نیست.

---

## راه‌اندازی سریع

```bash
cd backend
python3 -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt

cp .env.example .env          # و SECRET_KEY را عوض کن
python manage.py migrate
python manage.py seed_content # محتوای واقعی فارسی/انگلیسی سایت
python manage.py createsuperuser
python manage.py runserver 0.0.0.0:8000
```

| آدرس | توضیح |
|---|---|
| `http://localhost:8000/` | صفحه اصلی (رندر از دیتابیس) |
| `http://localhost:8000/?lang=en` | نسخه انگلیسی |
| `http://localhost:8000/admin/` | پنل مدیریت |
| `http://localhost:8000/api/docs/` | مستندات تعاملی Swagger |
| `http://localhost:8000/api/redoc/` | مستندات ReDoc |
| `http://localhost:8000/api/schema/` | فایل OpenAPI 3 |

---

## ساختار

```
backend/
├── config/
│   ├── settings/
│   │   ├── base.py      مشترک، همه‌چیز از env خوانده می‌شود
│   │   ├── dev.py       DEBUG، CORS باز، throttle شل
│   │   └── prod.py      HSTS، کوکی امن، SSL redirect، بدون browsable API
│   ├── urls.py          admin · api · صفحات
│   └── api_urls.py      روتر نسخه v1
├── core/                زیرساخت مشترک
│   ├── models.py        TimeStamped · Publishable · Bilingual · Slugged
│   ├── middleware.py    RequestID + لاگ زمان پاسخ
│   ├── pagination.py    صفحه‌بندی استاندارد
│   ├── exceptions.py    قالب یکسان خطا
│   └── context_processors.py
├── content/             محتوای سایت
│   ├── models.py        ۱۳ مدل
│   ├── serializers.py   با resolve خودکار زبان
│   ├── views.py         API فقط-خواندنی
│   ├── views_pages.py   صفحات HTML
│   ├── admin.py         پنل کامل فارسی
│   └── management/commands/seed_content.py
├── leads/               فرم تماس و خبرنامه
└── templates/           base + صفحات
```

---

## مدل‌های دیتابیس

### `content`

| مدل | کار |
|---|---|
| `SiteSettings` | تک‌ردیفی (singleton) — برند، هیرو، تماس، شبکه‌های اجتماعی، متای سئو |
| `Capability` | کارت‌های «آنچه می‌سازیم» |
| `Project` | نمونه‌کارها |
| `ProjectCategory` | دسته‌بندی پروژه |
| `Technology` | تگ تکنولوژی (M2M با پروژه) |
| `Article` | مقالات |
| `ArticleCategory` | دسته‌بندی مقاله |
| `ProcessStep` | مراحل «از ایده تا راهکار» |
| `FAQ` | سوالات متداول |
| `TeamMember` | اعضای تیم |
| `Testimonial` | نظرات مشتریان |
| `Statistic` | آمارهای صفحه اصلی |
| `PageView` | آمار بازدید (بدون ذخیره IP) |

### `leads`

| مدل | کار |
|---|---|
| `ContactMessage` | درخواست پروژه — با وضعیت، بودجه، یادداشت داخلی، خروجی CSV |
| `Subscriber` | خبرنامه — عضویت مجدد باعث خطا نمی‌شود |

### کلاس‌های پایه

هر مدل محتوا از این‌ها ارث می‌برد:

- **`TimeStampedModel`** — `created_at` / `updated_at`
- **`PublishableModel`** — `is_published` + `published_at` (زمان‌بندی انتشار) + `order`
- **`BilingualModel`** — ستون‌های `_fa` و `_en` جدا، به‌جای فریم‌ورک ترجمه.
  کوئری تخت می‌ماند، ادمین شفاف است و API می‌تواند هر دو زبان را یک‌جا بدهد.
- **`SluggedModel`** — تولید خودکار slug با تضمین یکتایی (`test-project`, `test-project-2`, …)

---

## API

پایه: `/api/v1/`

### محتوا (فقط خواندن، عمومی)

```
GET  /api/v1/home/                  کل صفحه اصلی در یک درخواست
GET  /api/v1/capabilities/
GET  /api/v1/projects/
GET  /api/v1/projects/featured/
GET  /api/v1/projects/{slug}/
GET  /api/v1/articles/
GET  /api/v1/articles/{slug}/
GET  /api/v1/faqs/
GET  /api/v1/process/
GET  /api/v1/team/
GET  /api/v1/testimonials/
GET  /api/v1/statistics/
GET  /api/v1/technologies/
```

### فرم‌ها (نوشتن، عمومی، محدودشده)

```
POST /api/v1/contact/       ۵ درخواست در ساعت
POST /api/v1/subscribe/     ۱۰ درخواست در ساعت
POST /api/v1/track/         ثبت بازدید
```

### عملیاتی

```
GET  /api/v1/health/        سلامت سرویس + اتصال دیتابیس
```

### پارامترها

| پارامتر | نمونه |
|---|---|
| `?lang=` | `fa` (پیش‌فرض) یا `en` — فیلد `title` و `description` را resolve می‌کند |
| `?search=` | `?search=COMSOL` |
| `?page=` `?page_size=` | صفحه‌بندی، سقف ۱۰۰ |
| `?ordering=` | `?ordering=-view_count` |
| فیلتر پروژه | `?status=ongoing` · `?year=2026` · `?category__slug=ai` · `?technologies__slug=python` · `?is_featured=true` |

### نمونه پاسخ

```jsonc
// GET /api/v1/capabilities/?lang=fa
{
  "count": 5, "page": 1, "pages": 1, "page_size": 12,
  "next": null, "previous": null,
  "results": [{
    "id": 1, "slug": "core-tech-engineering", "icon": "cpu",
    "title_fa": "تکنولوژی‌های پایه",
    "title_en": "Core Tech & Engineering",
    "title": "تکنولوژی‌های پایه",   // ← resolve شده طبق lang
    "description": "…", "lang": "fa"
  }]
}
```

### قالب یکسان خطا

```json
{
  "error": "validation_error",
  "fields": { "email": ["پست الکترونیکی صحیح وارد کنید."] },
  "status": 400,
  "requestId": "2ae83b2a9a5a4aee96c88091b1aeb5f0"
}
```

---

## امنیت

- **Throttling دو لایه** — عمومی ۱۲۰/دقیقه، تماس ۵/ساعت، خبرنامه ۱۰/ساعت
- **Honeypot** — فیلد مخفی `website`؛ اگر پر شود، ربات است و ۴۰۰ می‌گیرد
- **IP هش می‌شود** — `sha256` با salt، آدرس خام هرگز ذخیره نمی‌شود
- **حریم خصوصی آمار** — `PageView` فقط هش user-agent نگه می‌دارد
- **فیلتر لینک‌بمباران** در متن پیام
- **CORS allowlist** با regex برای localhost و دامنه‌های پیش‌نمایش
- **در production**: HSTS یک‌ساله، `SECURE_SSL_REDIRECT`، کوکی‌های `Secure`،
  `SECRET_KEY` اجباری (اگر نباشد بالا نمی‌آید)، browsable API خاموش
- **`X-Request-ID`** روی هر پاسخ — همان الگوی سرور Node، پس یک trace id در هر دو
  بک‌اند قابل پیگیری است

---

## پنل ادمین

فارسی، با `list_editable` برای ترتیب و انتشار، فیلتر و جستجو، `date_hierarchy`،
پیش‌نمایش تصویر، و اکشن‌های گروهی:

- انتشار / پیش‌نویس کردن دسته‌جمعی
- برای پیام‌ها: خوانده‌شده / پاسخ‌داده‌شده / اسپم + **خروجی CSV با BOM**
  (تا اکسل فارسی را درست بخواند)

---

## دستورهای مدیریتی

```bash
python manage.py seed_content              # محتوای واقعی سایت (idempotent)
python manage.py seed_content --flush      # اول پاک کن، بعد پر کن
python manage.py content_stats             # گزارش وضعیت محتوا و سرنخ‌ها
python manage.py cleanup_pageviews --days 90   # نگه‌داری جدول آمار
```

## کارایی

- `/api/v1/home/` به‌ازای هر زبان کش می‌شود → **۰ کوئری** روی cache hit.
  سیگنال‌های `post_save`/`post_delete`/`m2m_changed` کش را خودکار می‌شکنند،
  پس محتوای بیات ممکن نیست.
- لیست‌ها `ETag` و `Last-Modified` می‌فرستند → مرورگر `304` می‌گیرد.
- `SiteSettings` کش می‌شود (context processor روی هر رندر اجرا می‌شود).
- ایندکس `(is_published, order)` روی مدل‌های پرتکرار.
- `list_select_related` در ادمین برای جلوگیری از N+1.

## تست

```bash
python manage.py test
```

**۵۳ تست** — API، فیلتر، صفحه‌بندی، تفکیک زبان، مخفی‌بودن محتوای منتشرنشده،
شمارنده بازدید، singleton بودن تنظیمات، یکتایی slug، honeypot، هش IP،
عضویت مجدد خبرنامه، رندر صفحات، نقشه سایت، دستورهای مدیریتی — و یک دسته
**تست رگرسیون** برای باگ‌هایی که در ممیزی پیدا و رفع شدند (تکرار ردیف در فیلتر
M2M، دوبار خواندن آبجکت، کش، ETag، سقف اندازه بدنه، حساسیت به حروف در ایمیل).

---

## دیتابیس

پیش‌فرض SQLite است و هیچ تنظیمی نمی‌خواهد. برای PostgreSQL فقط کافی است در `.env`:

```
DATABASE_URL=postgres://user:pass@localhost:5432/physioelectric
```

و `psycopg[binary]` را از `requirements.txt` از حالت کامنت خارج کنی. هیچ تغییر
کدی لازم نیست.

---

## استقرار

```bash
export DJANGO_SETTINGS_MODULE=config.settings.prod
export SECRET_KEY="..."
export DATABASE_URL="postgres://..."

python manage.py migrate
python manage.py collectstatic --noinput
gunicorn config.wsgi:application --bind 0.0.0.0:8000 --workers 4
```

فایل‌های استاتیک با WhiteNoise سرو می‌شوند، پس برای شروع نیازی به Nginx نیست.
