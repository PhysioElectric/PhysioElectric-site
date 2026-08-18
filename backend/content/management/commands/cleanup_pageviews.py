"""
Trim the analytics table.

`PageView` grows forever otherwise. Run it from cron/systemd-timer:

    python manage.py cleanup_pageviews --days 90
"""

from django.core.management.base import BaseCommand
from django.utils import timezone

from content.models import PageView


class Command(BaseCommand):
    help = "Delete page views older than N days."

    def add_arguments(self, parser):
        parser.add_argument("--days", type=int, default=90,
                            help="سن نگه‌داری بر حسب روز (پیش‌فرض ۹۰).")
        parser.add_argument("--dry-run", action="store_true",
                            help="فقط تعداد را نشان بده، حذف نکن.")

    def handle(self, *args, **options):
        cutoff = timezone.now() - timezone.timedelta(days=options["days"])
        qs = PageView.objects.filter(created_at__lt=cutoff)
        count = qs.count()

        if options["dry_run"]:
            self.stdout.write(f"{count} رکورد قدیمی‌تر از {options['days']} روز پیدا شد (dry-run).")
            return

        qs.delete()
        self.stdout.write(self.style.SUCCESS(f"✓ {count} بازدید قدیمی حذف شد."))
