from django.apps import AppConfig


class ContentConfig(AppConfig):
    default_auto_field = "django.db.models.BigAutoField"
    name = "content"
    verbose_name = "محتوا"

    def ready(self):
        from . import signals  # noqa: F401  (registers the receivers)
