"""Sitemaps so search engines can discover the DB-driven pages."""

from django.contrib.sitemaps import Sitemap
from django.urls import reverse

from .models import Article, Capability, Project


class StaticSitemap(Sitemap):
    priority = 1.0
    changefreq = "weekly"

    def items(self):
        return ["content:home"]

    def location(self, item):
        return reverse(item)


class ProjectSitemap(Sitemap):
    priority = 0.8
    changefreq = "monthly"

    def items(self):
        return Project.objects.published()

    def lastmod(self, obj):
        return obj.updated_at


class ArticleSitemap(Sitemap):
    priority = 0.7
    changefreq = "weekly"

    def items(self):
        return Article.objects.published()

    def lastmod(self, obj):
        return obj.updated_at


class CapabilitySitemap(Sitemap):
    priority = 0.6
    changefreq = "monthly"

    def items(self):
        return Capability.objects.published()

    def lastmod(self, obj):
        return obj.updated_at


SITEMAPS = {
    "static": StaticSitemap,
    "projects": ProjectSitemap,
    "articles": ArticleSitemap,
    "capabilities": CapabilitySitemap,
}
