from django.conf import settings
from django.conf.urls.static import static
from django.contrib import admin
from django.contrib.sitemaps.views import sitemap
from django.urls import include, path

from content.sitemaps import SITEMAPS
from core.views import robots_txt
from drf_spectacular.views import (
    SpectacularAPIView,
    SpectacularRedocView,
    SpectacularSwaggerView,
)

urlpatterns = [
    path("admin/", admin.site.urls),

    # API
    path("api/v1/", include("config.api_urls")),
    path("api/schema/", SpectacularAPIView.as_view(), name="schema"),
    path("api/docs/", SpectacularSwaggerView.as_view(url_name="schema"), name="swagger-ui"),
    path("api/redoc/", SpectacularRedocView.as_view(url_name="schema"), name="redoc"),

    # SEO
    path("sitemap.xml", sitemap, {"sitemaps": SITEMAPS},
         name="django.contrib.sitemaps.views.sitemap"),
    path("robots.txt", robots_txt, name="robots"),

    # HTML pages (must stay last so it can own the root path)
    path("", include("content.urls")),
]

if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
