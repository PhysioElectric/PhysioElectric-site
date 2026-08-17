"""Lead capture: contact requests and newsletter subscribers."""

import hashlib

from django.db import models
from django.db.models.functions import Lower

from core.models import TimeStampedModel


class ContactMessage(TimeStampedModel):
    """A "Start a Project" submission."""

    class Status(models.TextChoices):
        NEW = "new", "جدید"
        READ = "read", "خوانده شده"
        REPLIED = "replied", "پاسخ داده شده"
        ARCHIVED = "archived", "بایگانی"
        SPAM = "spam", "اسپم"

    class Budget(models.TextChoices):
        UNDECIDED = "undecided", "نامشخص"
        SMALL = "small", "کمتر از ۵ هزار دلار"
        MEDIUM = "medium", "۵ تا ۲۰ هزار دلار"
        LARGE = "large", "۲۰ تا ۵۰ هزار دلار"
        ENTERPRISE = "enterprise", "بیش از ۵۰ هزار دلار"

    name = models.CharField("نام", max_length=160)
    email = models.EmailField("ایمیل", db_index=True)
    phone = models.CharField("تلفن", max_length=40, blank=True)
    company = models.CharField("شرکت", max_length=160, blank=True)
    subject = models.CharField("موضوع", max_length=200, blank=True)
    message = models.TextField("پیام")

    service = models.CharField(
        "سرویس مورد نظر",
        max_length=60,
        blank=True,
        help_text="مثل web / matlab / comsol / ai",
    )
    budget = models.CharField(
        "بودجه", max_length=20, choices=Budget.choices, default=Budget.UNDECIDED
    )

    status = models.CharField(
        "وضعیت", max_length=20, choices=Status.choices, default=Status.NEW, db_index=True
    )
    internal_note = models.TextField("یادداشت داخلی", blank=True)

    # Diagnostics — kept coarse on purpose.
    language = models.CharField("زبان", max_length=5, blank=True)
    source_page = models.CharField("صفحه مبدا", max_length=300, blank=True)
    ip_hash = models.CharField("هش آی‌پی", max_length=64, blank=True, editable=False)
    user_agent = models.CharField("مرورگر", max_length=300, blank=True, editable=False)

    class Meta:
        ordering = ["-created_at"]
        verbose_name = "پیام تماس"
        verbose_name_plural = "پیام‌های تماس"
        indexes = [models.Index(fields=["status", "-created_at"])]

    def __str__(self) -> str:
        return f"{self.name} <{self.email}>"

    def save(self, *args, **kwargs):
        self.email = self.email.strip().lower()
        super().save(*args, **kwargs)

    @staticmethod
    def hash_ip(ip: str) -> str:
        """Store a salted digest instead of the raw address."""
        if not ip:
            return ""
        return hashlib.sha256(f"physio::{ip}".encode()).hexdigest()

    def mark_read(self):
        if self.status == self.Status.NEW:
            self.status = self.Status.READ
            self.save(update_fields=["status", "updated_at"])


class Subscriber(TimeStampedModel):
    """Newsletter signup."""

    email = models.EmailField("ایمیل", unique=True)
    name = models.CharField("نام", max_length=160, blank=True)
    language = models.CharField("زبان", max_length=5, default="fa")
    is_active = models.BooleanField("فعال", default=True, db_index=True)
    confirmed_at = models.DateTimeField("زمان تایید", null=True, blank=True)
    unsubscribed_at = models.DateTimeField("زمان لغو", null=True, blank=True)

    class Meta:
        ordering = ["-created_at"]
        verbose_name = "مشترک خبرنامه"
        verbose_name_plural = "مشترکین خبرنامه"
        constraints = [
            # "A@x.com" and "a@x.com" are the same inbox.
            models.UniqueConstraint(
                Lower("email"), name="subscriber_email_ci_unique"
            )
        ]

    def save(self, *args, **kwargs):
        self.email = self.email.strip().lower()
        super().save(*args, **kwargs)

    def __str__(self) -> str:
        return self.email
