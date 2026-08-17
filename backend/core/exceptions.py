"""Uniform API error envelope."""

from rest_framework.views import exception_handler


def api_exception_handler(exc, context):
    """Wrap DRF errors so every failure looks the same to the client."""
    response = exception_handler(exc, context)
    if response is None:
        return None

    request = context.get("request")
    detail = response.data

    # Normalise: DRF returns either {"detail": "..."} or a field->errors map.
    if isinstance(detail, dict) and set(detail.keys()) == {"detail"}:
        payload = {"error": str(detail["detail"]), "fields": {}}
    elif isinstance(detail, dict):
        payload = {"error": "validation_error", "fields": detail}
    else:
        payload = {"error": "request_failed", "detail": detail, "fields": {}}

    payload["status"] = response.status_code
    payload["requestId"] = getattr(request, "id", None)
    response.data = payload
    return response
