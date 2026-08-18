"""Inbox for everything the public forms produce."""

import csv

from django.contrib import admin
from django.http import HttpResponse
from django.utils import timezone
from django.utils.html import format_html

from .models import ContactMessage, Subscriber


@admin.register(ContactMessage)
class ContactMessageAdmin(admin.ModelAdmin):
    list_display = ["name", "email", "service", "budget", "status_badge", "created_at"]
    list_filter = ["status", "budget", "service", "language", "created_at"]
    search_fields = ["name", "email", "company", "subject", "message"]
    date_hierarchy = "created_at"
    readonly_fields = ["created_at", "updated_at", "ip_hash", "user_agent",
                       "language", "source_page"]
    actions = ["mark_read", "mark_replied", "mark_spam", "export_csv"]
    fieldsets = (
        ("فرستنده", {"fields": ("name", "email", "phone", "company")}),
        ("پیام", {"fields": ("subject", "service", "budget", "message")}),
        ("پیگیری", {"fields": ("status", "internal_note")}),
        ("فنی", {
            "classes": ("collapse",),
            "fields": ("language", "source_page", "ip_hash", "user_agent",
                       "created_at", "updated_at"),
        }),
    )

    @admin.display(description="وضعیت", ordering="status")
    def status_badge(self, obj):
        colors = {
            "new": "#0ea5e9", "read": "#64748b", "replied": "#16a34a",
            "archived": "#94a3b8", "spam": "#dc2626",
        }
        return format_html(
            '<span style="background:{};color:#fff;padding:2px 10px;'
            'border-radius:999px;font-size:11px">{}</span>',
            colors.get(obj.status, "#64748b"), obj.get_status_display(),
        )

    @admin.action(description="علامت‌گذاری به‌عنوان خوانده‌شده")
    def mark_read(self, request, queryset):
        n = queryset.update(status=ContactMessage.Status.READ)
        self.message_user(request, f"{n} پیام خوانده‌شده شد.")

    @admin.action(description="علامت‌گذاری به‌عنوان پاسخ‌داده‌شده")
    def mark_replied(self, request, queryset):
        n = queryset.update(status=ContactMessage.Status.REPLIED)
        self.message_user(request, f"{n} پیام پاسخ‌داده‌شده شد.")

    @admin.action(description="علامت‌گذاری به‌عنوان اسپم")
    def mark_spam(self, request, queryset):
        n = queryset.update(status=ContactMessage.Status.SPAM)
        self.message_user(request, f"{n} پیام اسپم شد.")

    @admin.action(description="خروجی CSV")
    def export_csv(self, request, queryset):
        response = HttpResponse(content_type="text/csv; charset=utf-8")
        stamp = timezone.now().strftime("%Y%m%d")
        response["Content-Disposition"] = f'attachment; filename="contacts-{stamp}.csv"'
        response.write("\ufeff")  # BOM so Excel reads Persian correctly
        writer = csv.writer(response)
        writer.writerow(["نام", "ایمیل", "تلفن", "شرکت", "سرویس",
                         "بودجه", "وضعیت", "پیام", "تاریخ"])
        for row in queryset:
            writer.writerow([
                row.name, row.email, row.phone, row.company, row.service,
                row.get_budget_display(), row.get_status_display(),
                row.message, row.created_at.strftime("%Y-%m-%d %H:%M"),
            ])
        return response


@admin.register(Subscriber)
class SubscriberAdmin(admin.ModelAdmin):
    list_display = ["email", "name", "language", "is_active", "created_at"]
    list_filter = ["is_active", "language", "created_at"]
    search_fields = ["email", "name"]
    date_hierarchy = "created_at"
    actions = ["deactivate"]

    @admin.action(description="غیرفعال کردن")
    def deactivate(self, request, queryset):
        n = queryset.update(is_active=False, unsubscribed_at=timezone.now())
        self.message_user(request, f"{n} مشترک غیرفعال شد.")
