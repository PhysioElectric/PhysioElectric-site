"""Local development settings."""

from .base import *  # noqa: F401,F403
from .base import REST_FRAMEWORK

DEBUG = True
ALLOWED_HOSTS = ["*"]

# Browsable API + wide-open CORS make local frontend work painless.
CORS_ALLOW_ALL_ORIGINS = True

# Don't fight the manifest storage while developing.
STORAGES = {
    "default": {"BACKEND": "django.core.files.storage.FileSystemStorage"},
    "staticfiles": {"BACKEND": "whitenoise.storage.CompressedStaticFilesStorage"},
}

# Throttling gets in the way when hammering the API by hand.
REST_FRAMEWORK = {
    **REST_FRAMEWORK,
    "DEFAULT_THROTTLE_RATES": {
        "anon": "10000/min",
        "user": "10000/min",
        "contact": "100/hour",
        "subscribe": "100/hour",
    },
}
