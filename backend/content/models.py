"""
Content models.

Every block the marketing site renders is a row here, so the page can be
edited from the admin without touching HTML.
"""

from django.core.validators import MinValueValidator
from django.db import models
from django.urls import reverse
from django.utils import timezone

from core.models import BilingualModel, PublishableModel, SluggedModel, TimeStampedModel


class SiteSettings(models.Model):
    """Singleton row holding brand-wide values."""

    brand_name = models.CharField("نام برند", max_length=120, default="PhysioElectric")
    tagline_fa = models.CharField("شعار (فارسی)", max_length=255, blank=True)
    tagline_en = models.CharField("شعار (انگلیسی)", max_length=255, blank=True)

    hero_badge_fa = models.CharField("برچسب هیرو (فارسی)", max_length=160, blank=True)
    hero_badge_en = models.CharField("برچسب هیرو (انگلیسی)", max_length=160, blank=True)
    hero_title_fa = models.CharField("عنوان هیرو (فارسی)", max_length=255, blank=True)
    hero_title_en = models.CharField("عنوان هیرو (انگلیسی)", max_length=255, blank=True)
    hero_subtitle_fa = models.TextField("زیرعنوان هیرو (فارسی)", blank=True)
    hero_subtitle_en = models.TextField("زیرعنوان هیرو (انگلیسی)", blank=True)

    email = models.EmailField("ایمیل", blank=True)
    phone = models.CharField("تلفن", max_length=40, blank=True)
    address_fa = models.CharField("نشانی (فارسی)", max_length=255, blank=True)
    address_en = models.CharField("نشانی (انگلیسی)", max_length=255, blank=True)

    github_url = models.URLField("گیت‌هاب", blank=True)
    linkedin_url = models.URLField("لینکدین", blank=True)
    twitter_url = models.URLField("توییتر", blank=True)
    instagram_url = models.URLField("اینستاگرام", blank=True)

    meta_description_fa = models.TextField("توضیح متا (فارسی)", blank=True, max_length=320)
    meta_description_en = models.TextField("توضیح متا (انگلیسی)", blank=True, max_length=320)

    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = "تنظیمات سایت"
        verbose_name_plural = "تنظیمات سایت"

    def __str__(self) -> str:
        return self.brand_name

    def save(self, *args, **kwargs):
        # Enforce the singleton invariant at the model level.
        self.pk = 1
        super().save(*args, **kwargs)

    @classmethod
    def load(cls) -> "SiteSettings":
        """
        Fetch the singleton, cached.

        The context processor runs on every page render, so without this the
        homepage pays for an extra query on each request.
        """
        from django.core.cache import cache

        from .cache import SITE_SETTINGS_KEY

        obj = cache.get(SITE_SETTINGS_KEY)
        if obj is None:
            obj, _ = cls.objects.get_or_create(pk=1)
            cache.set(SITE_SETTINGS_KEY, obj, 600)
        return obj


class Capability(BilingualModel, PublishableModel, SluggedModel):
    """A "What We Build" card."""

    icon = models.CharField(
        "آیکون",
        max_length=60,
        default="cpu",
        help_text="نام آیکون Lucide، مثل cpu یا code-2",
    )
    link_label_fa = models.CharField("متن لینک (فارسی)", max_length=120, blank=True)
    link_label_en = models.CharField("متن لینک (انگلیسی)", max_length=120, blank=True)
    link_url = models.CharField("آدرس لینک", max_length=300, blank=True)
    is_featured = models.BooleanField("شاخص", default=False, db_index=True)

    class Meta(PublishableModel.Meta):
        verbose_name = "توانمندی"
        verbose_name_plural = "توانمندی‌ها"
        indexes = [models.Index(fields=["is_published", "order"])]

    def get_absolute_url(self):
        return reverse("content:capability-detail", kwargs={"slug": self.slug})


class Technology(models.Model):
    """Reusable tech tag (Python, COMSOL, React ...)."""

    name = models.CharField("نام", max_length=80, unique=True)
    slug = models.SlugField("نشانی", max_length=90, unique=True)
    icon = models.CharField("آیکون", max_length=60, blank=True)
    color = models.CharField(
        "رنگ", max_length=9, blank=True, help_text="کد هگز، مثل #0ea5e9"
    )

    class Meta:
        ordering = ["name"]
        verbose_name = "تکنولوژی"
        verbose_name_plural = "تکنولوژی‌ها"

    def __str__(self) -> str:
        return self.name


class ProjectCategory(BilingualModel, models.Model):
    slug = models.SlugField("نشانی", max_length=90, unique=True)
    order = models.PositiveIntegerField("ترتیب", default=0)

    class Meta:
        ordering = ["order", "title_fa"]
        verbose_name = "دسته پروژه"
        verbose_name_plural = "دسته‌های پروژه"


class Project(BilingualModel, PublishableModel, SluggedModel):
    """Portfolio entry shown in the horizontal slider."""

    class Status(models.TextChoices):
        COMPLETED = "completed", "تکمیل شده"
        ONGOING = "ongoing", "در حال انجام"
        RESEARCH = "research", "پژوهشی"

    category = models.ForeignKey(
        ProjectCategory,
        verbose_name="دسته",
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="projects",
    )
    technologies = models.ManyToManyField(
        Technology, verbose_name="تکنولوژی‌ها", blank=True, related_name="projects"
    )
    summary_fa = models.TextField("خلاصه (فارسی)", blank=True, max_length=400)
    summary_en = models.TextField("خلاصه (انگلیسی)", blank=True, max_length=400)
    body_fa = models.TextField("متن کامل (فارسی)", blank=True)
    body_en = models.TextField("متن کامل (انگلیسی)", blank=True)

    cover_image = models.ImageField(
        "تصویر کاور", upload_to="projects/%Y/%m/", blank=True, null=True
    )
    cover_image_url = models.URLField(
        "آدرس تصویر", blank=True, help_text="اگر فایل آپلود نشده، از این استفاده می‌شود."
    )
    accent_color = models.CharField("رنگ تاکیدی", max_length=9, blank=True, default="#0ea5e9")

    client = models.CharField("کارفرما", max_length=160, blank=True)
    status = models.CharField(
        "وضعیت", max_length=20, choices=Status.choices, default=Status.COMPLETED, db_index=True
    )
    year = models.PositiveIntegerField(
        "سال", null=True, blank=True, validators=[MinValueValidator(1990)]
    )
    external_url = models.URLField("لینک خارجی", blank=True)
    repo_url = models.URLField("مخزن کد", blank=True)
    is_featured = models.BooleanField("شاخص", default=False, db_index=True)
    view_count = models.PositiveIntegerField("بازدید", default=0, editable=False)

    class Meta(PublishableModel.Meta):
        verbose_name = "پروژه"
        verbose_name_plural = "پروژه‌ها"
        indexes = [
            models.Index(fields=["is_published", "is_featured", "order"]),
            models.Index(fields=["status", "-year"]),
        ]

    def get_absolute_url(self):
        return reverse("content:project-detail", kwargs={"slug": self.slug})

    @property
    def image(self) -> str:
        if self.cover_image:
            return self.cover_image.url
        return self.cover_image_url


class ProcessStep(BilingualModel, PublishableModel):
    """One node of the "From Idea to Solution" timeline."""

    number = models.PositiveSmallIntegerField("شماره مرحله", default=1)
    icon = models.CharField("آیکون", max_length=60, default="search")

    class Meta(PublishableModel.Meta):
        ordering = ["number", "order"]
        verbose_name = "مرحله فرایند"
        verbose_name_plural = "مراحل فرایند"


class ArticleCategory(BilingualModel, models.Model):
    slug = models.SlugField("نشانی", max_length=90, unique=True)
    color = models.CharField("رنگ", max_length=9, blank=True)

    class Meta:
        ordering = ["title_fa"]
        verbose_name = "دسته مقاله"
        verbose_name_plural = "دسته‌های مقاله"


class Article(BilingualModel, PublishableModel, SluggedModel):
    """Blog post / insight."""

    category = models.ForeignKey(
        ArticleCategory,
        verbose_name="دسته",
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="articles",
    )
    author = models.ForeignKey(
        "auth.User",
        verbose_name="نویسنده",
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="articles",
    )
    excerpt_fa = models.TextField("چکیده (فارسی)", blank=True, max_length=400)
    excerpt_en = models.TextField("چکیده (انگلیسی)", blank=True, max_length=400)
    body_fa = models.TextField("متن (فارسی)", blank=True)
    body_en = models.TextField("متن (انگلیسی)", blank=True)

    cover_image = models.ImageField(
        "تصویر کاور", upload_to="articles/%Y/%m/", blank=True, null=True
    )
    cover_image_url = models.URLField("آدرس تصویر", blank=True)
    reading_minutes = models.PositiveSmallIntegerField("زمان مطالعه (دقیقه)", default=5)
    view_count = models.PositiveIntegerField("بازدید", default=0, editable=False)
    is_featured = models.BooleanField("شاخص", default=False, db_index=True)

    class Meta(PublishableModel.Meta):
        ordering = ["-published_at", "order", "-created_at"]
        verbose_name = "مقاله"
        verbose_name_plural = "مقالات"
        indexes = [models.Index(fields=["is_published", "-published_at"])]

    def get_absolute_url(self):
        return reverse("content:article-detail", kwargs={"slug": self.slug})

    @property
    def image(self) -> str:
        if self.cover_image:
            return self.cover_image.url
        return self.cover_image_url

    def save(self, *args, **kwargs):
        if self.is_published and self.published_at is None:
            self.published_at = timezone.now()
        super().save(*args, **kwargs)


class FAQ(BilingualModel, PublishableModel):
    """
    Accordion entry.

    `title_*` holds the question and `description_*` the answer — reusing the
    bilingual base keeps the admin and serializers consistent.
    """

    class Meta(PublishableModel.Meta):
        verbose_name = "سوال متداول"
        verbose_name_plural = "سوالات متداول"
        indexes = [models.Index(fields=["is_published", "order"])]


class TeamMember(BilingualModel, PublishableModel, SluggedModel):
    """`title_*` is the person's name, `description_*` their bio."""

    role_fa = models.CharField("سمت (فارسی)", max_length=160, blank=True)
    role_en = models.CharField("سمت (انگلیسی)", max_length=160, blank=True)
    photo = models.ImageField("عکس", upload_to="team/", blank=True, null=True)
    photo_url = models.URLField("آدرس عکس", blank=True)
    email = models.EmailField("ایمیل", blank=True)
    github_url = models.URLField("گیت‌هاب", blank=True)
    linkedin_url = models.URLField("لینکدین", blank=True)

    class Meta(PublishableModel.Meta):
        verbose_name = "عضو تیم"
        verbose_name_plural = "اعضای تیم"
        indexes = [models.Index(fields=["is_published", "order"])]


class Testimonial(BilingualModel, PublishableModel):
    """`description_*` is the quote."""

    author_name = models.CharField("نام گوینده", max_length=160)
    author_role_fa = models.CharField("سمت (فارسی)", max_length=160, blank=True)
    author_role_en = models.CharField("سمت (انگلیسی)", max_length=160, blank=True)
    avatar_url = models.URLField("آدرس آواتار", blank=True)
    rating = models.PositiveSmallIntegerField("امتیاز", default=5)

    class Meta(PublishableModel.Meta):
        verbose_name = "نظر مشتری"
        verbose_name_plural = "نظرات مشتریان"
        indexes = [models.Index(fields=["is_published", "order"])]


class Statistic(BilingualModel, PublishableModel):
    """Counter shown on the landing page (projects delivered, clients ...)."""

    value = models.CharField("مقدار", max_length=40, help_text="مثل +۵۰ یا ۹۹٪")
    icon = models.CharField("آیکون", max_length=60, blank=True)

    class Meta(PublishableModel.Meta):
        verbose_name = "آمار"
        verbose_name_plural = "آمارها"
        indexes = [models.Index(fields=["is_published", "order"])]


class PageView(TimeStampedModel):
    """
    Lightweight first-party analytics.

    Deliberately stores no IP address — just enough to answer "what is popular".
    """

    path = models.CharField("مسیر", max_length=300, db_index=True)
    referrer = models.CharField("ارجاع‌دهنده", max_length=300, blank=True)
    language = models.CharField("زبان", max_length=5, blank=True)
    user_agent_hash = models.CharField("هش مرورگر", max_length=64, blank=True)

    class Meta:
        ordering = ["-created_at"]
        verbose_name = "بازدید صفحه"
        verbose_name_plural = "بازدیدهای صفحه"
        indexes = [models.Index(fields=["path", "-created_at"])]

    def __str__(self) -> str:
        return f"{self.path} @ {self.created_at:%Y-%m-%d %H:%M}"
