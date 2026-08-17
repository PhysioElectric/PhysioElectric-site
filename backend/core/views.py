"""Small site-wide endpoints."""

from django.http import HttpResponse
from django.urls import reverse
from django.views.decorators.cache import cache_control


@cache_control(max_age=86400, public=True)
def robots_txt(request):
    lines = [
        "User-agent: *",
        "Disallow: /admin/",
        "Disallow: /api/",
        "Allow: /",
        "",
        f"Sitemap: {request.build_absolute_uri(reverse('django.contrib.sitemaps.views.sitemap'))}",
        "",
    ]
    return HttpResponse("\n".join(lines), content_type="text/plain; charset=utf-8")
