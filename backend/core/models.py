"""Abstract models reused across the project."""

from django.db import models
from django.utils import timezone
from django.utils.text import slugify


class TimeStampedModel(models.Model):
    """Adds self-maintaining created/updated stamps."""

    created_at = models.DateTimeField("ایجاد", auto_now_add=True, db_index=True)
    updated_at = models.DateTimeField("بروزرسانی", auto_now=True)

    class Meta:
        abstract = True


class PublishedQuerySet(models.QuerySet):
    def published(self):
        return self.filter(
            is_published=True
        ).filter(
            models.Q(published_at__isnull=True) | models.Q(published_at__lte=timezone.now())
        )

    def draft(self):
        return self.filter(is_published=False)


class PublishableModel(TimeStampedModel):
    """Anything that can be hidden from the public API."""

    is_published = models.BooleanField("منتشر شده", default=True, db_index=True)
    published_at = models.DateTimeField(
        "زمان انتشار",
        null=True,
        blank=True,
        help_text="خالی یعنی بلافاصله. تاریخ آینده یعنی زمان‌بندی‌شده.",
    )
    order = models.PositiveIntegerField(
        "ترتیب", default=0, db_index=True, help_text="عدد کوچک‌تر بالاتر نمایش داده می‌شود."
    )

    objects = PublishedQuerySet.as_manager()

    class Meta:
        abstract = True
        ordering = ["order", "-created_at"]


class BilingualModel(models.Model):
    """
    The site ships Persian and English side by side.

    Rather than pulling in a translation framework we keep explicit `_fa` /
    `_en` columns: it keeps queries flat, makes the admin obvious, and lets the
    API return both languages in a single response.
    """

    title_fa = models.CharField("عنوان (فارسی)", max_length=255)
    title_en = models.CharField("عنوان (انگلیسی)", max_length=255, blank=True)
    description_fa = models.TextField("توضیح (فارسی)", blank=True)
    description_en = models.TextField("توضیح (انگلیسی)", blank=True)

    class Meta:
        abstract = True

    def __str__(self) -> str:
        return self.title_fa or self.title_en or f"#{self.pk}"

    def localized(self, field: str, lang: str = "fa") -> str:
        """Return `field_<lang>`, falling back to the other language."""
        primary = getattr(self, f"{field}_{lang}", "") or ""
        if primary:
            return primary
        other = "en" if lang == "fa" else "fa"
        return getattr(self, f"{field}_{other}", "") or ""


class SluggedModel(models.Model):
    slug = models.SlugField("نشانی یکتا", max_length=280, unique=True, blank=True)

    class Meta:
        abstract = True

    def _slug_source(self) -> str:
        return getattr(self, "title_en", "") or getattr(self, "title_fa", "") or ""

    def save(self, *args, **kwargs):
        if not self.slug:
            base = slugify(self._slug_source(), allow_unicode=True)[:250] or "item"
            slug, counter = base, 2
            model = type(self)
            while model.objects.filter(slug=slug).exclude(pk=self.pk).exists():
                slug = f"{base}-{counter}"
                counter += 1
            self.slug = slug
        super().save(*args, **kwargs)
