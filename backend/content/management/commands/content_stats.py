"""Quick operational snapshot of what is in the database."""

from django.core.management.base import BaseCommand
from django.db.models import Count, Sum

from content.models import Article, Capability, PageView, Project
from leads.models import ContactMessage, Subscriber


class Command(BaseCommand):
    help = "Print a summary of stored content and leads."

    def handle(self, *args, **options):
        rows = [
            ("توانمندی‌ها", Capability.objects.count(), Capability.objects.published().count()),
            ("پروژه‌ها", Project.objects.count(), Project.objects.published().count()),
            ("مقالات", Article.objects.count(), Article.objects.published().count()),
        ]
        self.stdout.write(self.style.MIGRATE_HEADING("\nمحتوا (کل / منتشرشده)"))
        for label, total, pub in rows:
            self.stdout.write(f"  {label:<14} {total:>4} / {pub}")

        views = Project.objects.aggregate(v=Sum("view_count"))["v"] or 0
        self.stdout.write(f"  {'بازدید پروژه‌ها':<14} {views:>4}")

        self.stdout.write(self.style.MIGRATE_HEADING("\nسرنخ‌ها"))
        by_status = ContactMessage.objects.values("status").annotate(n=Count("id"))
        if by_status:
            for row in by_status:
                self.stdout.write(f"  {row['status']:<14} {row['n']:>4}")
        else:
            self.stdout.write("  هنوز پیامی ثبت نشده")
        self.stdout.write(f"  {'مشترکین':<14} {Subscriber.objects.filter(is_active=True).count():>4}")

        self.stdout.write(self.style.MIGRATE_HEADING("\nپربازدیدترین مسیرها"))
        top = (PageView.objects.values("path")
               .annotate(n=Count("id")).order_by("-n")[:5])
        for row in top:
            self.stdout.write(f"  {row['path'][:40]:<40} {row['n']:>4}")
        if not top:
            self.stdout.write("  هنوز بازدیدی ثبت نشده")
        self.stdout.write("")
