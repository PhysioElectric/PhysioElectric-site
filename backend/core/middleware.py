"""Request-scoped helpers."""

import logging
import time
import uuid

from django.conf import settings
from django.http import JsonResponse

logger = logging.getLogger("physio.request")

HEADER = "HTTP_X_REQUEST_ID"


class RequestIDMiddleware:
    """
    Attach a stable id to every request and echo it back.

    Mirrors what the Node lab already does, so a single trace id can follow a
    user across both backends.
    """

    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        incoming = request.META.get(HEADER, "")
        request.id = incoming if 8 <= len(incoming) <= 64 and incoming.isascii() else uuid.uuid4().hex
        started = time.perf_counter()

        response = self.get_response(request)

        duration_ms = round((time.perf_counter() - started) * 1000, 2)
        response["X-Request-ID"] = request.id
        if request.path.startswith("/api/"):
            logger.info(
                "%s %s -> %s (%sms) rid=%s",
                request.method,
                request.get_full_path(),
                response.status_code,
                duration_ms,
                request.id,
            )
        return response


class PayloadSizeLimitMiddleware:
    """
    Reject oversized API bodies up front.

    Django's DATA_UPLOAD_MAX_MEMORY_SIZE only guards `request.body` and form
    parsing; DRF reads the raw stream, so a large JSON payload slips past it.
    This mirrors the 32kb cap the Node service enforces.
    """

    def __init__(self, get_response):
        self.get_response = get_response

    @property
    def limit(self) -> int:
        # Read per request so the value stays overridable at runtime.
        return getattr(settings, "API_MAX_BODY_BYTES", 64 * 1024)

    def __call__(self, request):
        if request.method in ("POST", "PUT", "PATCH") and request.path.startswith("/api/"):
            declared = request.META.get("CONTENT_LENGTH") or 0
            try:
                declared = int(declared)
            except (TypeError, ValueError):
                declared = 0
            if declared > self.limit:
                return JsonResponse(
                    {
                        "error": "payload_too_large",
                        "detail": f"حداکثر اندازه مجاز {self.limit} بایت است.",
                        "status": 413,
                        "requestId": getattr(request, "id", None),
                    },
                    status=413,
                )
        return self.get_response(request)
