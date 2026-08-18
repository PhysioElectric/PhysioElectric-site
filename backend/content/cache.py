"""Cache keys and invalidation for content."""

from django.core.cache import cache

SITE_SETTINGS_KEY = "site_settings:v1"
HOME_KEYS = ("home_payload:fa", "home_payload:en")


def bust_content_cache() -> None:
    """Drop every derived cache entry. Cheap, and correctness beats cleverness."""
    cache.delete_many([SITE_SETTINGS_KEY, *HOME_KEYS])
