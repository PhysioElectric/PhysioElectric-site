"""API + page tests for the content app."""

from django.test import TestCase
from django.urls import reverse
from rest_framework.test import APIClient

from content.models import FAQ, Article, Capability, Project, SiteSettings


class SeedMixin:
    @classmethod
    def seed(cls):
        from django.core.management import call_command
        call_command("seed_content", verbosity=0)


class HealthTests(TestCase):
    def test_health_reports_ok(self):
        res = self.client.get("/api/v1/health/")
        self.assertEqual(res.status_code, 200)
        self.assertTrue(res.json()["ok"])
        self.assertEqual(res.json()["database"], "up")

    def test_request_id_header_is_echoed(self):
        res = self.client.get("/api/v1/health/", HTTP_X_REQUEST_ID="abcd1234efgh")
        self.assertEqual(res["X-Request-ID"], "abcd1234efgh")


class ContentAPITests(SeedMixin, TestCase):
    @classmethod
    def setUpTestData(cls):
        cls.seed()

    def setUp(self):
        self.api = APIClient()

    def test_capabilities_list(self):
        res = self.api.get("/api/v1/capabilities/")
        self.assertEqual(res.status_code, 200)
        self.assertEqual(res.json()["count"], 5)

    def test_projects_are_paginated(self):
        res = self.api.get("/api/v1/projects/")
        body = res.json()
        self.assertIn("results", body)
        self.assertIn("page", body)
        self.assertEqual(body["count"], 6)

    def test_lang_switch_resolves_title(self):
        fa = self.api.get("/api/v1/capabilities/?lang=fa").json()["results"][0]
        en = self.api.get("/api/v1/capabilities/?lang=en").json()["results"][0]
        self.assertEqual(fa["title"], fa["title_fa"])
        self.assertEqual(en["title"], en["title_en"])
        self.assertNotEqual(fa["title"], en["title"])

    def test_unpublished_is_hidden(self):
        Capability.objects.update(is_published=False)
        self.assertEqual(self.api.get("/api/v1/capabilities/").json()["count"], 0)

    def test_project_detail_increments_views(self):
        project = Project.objects.first()
        before = project.view_count
        self.api.get(f"/api/v1/projects/{project.slug}/")
        project.refresh_from_db()
        self.assertEqual(project.view_count, before + 1)

    def test_featured_action(self):
        res = self.api.get("/api/v1/projects/featured/")
        self.assertEqual(res.status_code, 200)
        self.assertTrue(all(p["is_featured"] for p in res.json()))

    def test_filter_by_status(self):
        res = self.api.get("/api/v1/projects/?status=ongoing")
        self.assertTrue(all(p["status"] == "ongoing" for p in res.json()["results"]))

    def test_search(self):
        res = self.api.get("/api/v1/projects/?search=COMSOL")
        self.assertGreaterEqual(res.json()["count"], 1)

    def test_home_payload_has_every_block(self):
        body = self.api.get("/api/v1/home/").json()
        for key in ("site", "capabilities", "projects", "process",
                    "articles", "faqs", "statistics"):
            self.assertIn(key, body)
        self.assertEqual(len(body["capabilities"]), 5)

    def test_faq_exposes_question_answer_aliases(self):
        faq = self.api.get("/api/v1/faqs/?lang=fa").json()["results"][0]
        self.assertTrue(faq["question"])
        self.assertTrue(faq["answer"])

    def test_write_is_rejected(self):
        res = self.api.post("/api/v1/projects/", {"title_fa": "x"}, format="json")
        self.assertIn(res.status_code, (403, 405))


class ModelTests(TestCase):
    def test_site_settings_is_a_singleton(self):
        a = SiteSettings.load()
        a.brand_name = "One"
        a.save()
        b = SiteSettings(brand_name="Two")
        b.save()
        self.assertEqual(SiteSettings.objects.count(), 1)

    def test_slug_autogenerates_and_stays_unique(self):
        p1 = Project.objects.create(title_fa="آزمایش", title_en="Test Project")
        p2 = Project.objects.create(title_fa="آزمایش", title_en="Test Project")
        self.assertEqual(p1.slug, "test-project")
        self.assertEqual(p2.slug, "test-project-2")

    def test_publishing_an_article_stamps_published_at(self):
        a = Article.objects.create(title_fa="مقاله", title_en="Article", is_published=True)
        self.assertIsNotNone(a.published_at)

    def test_localized_falls_back_to_other_language(self):
        faq = FAQ.objects.create(title_fa="", title_en="Only English")
        self.assertEqual(faq.localized("title", "fa"), "Only English")


class PageTests(SeedMixin, TestCase):
    @classmethod
    def setUpTestData(cls):
        cls.seed()

    def test_home_renders_db_content(self):
        res = self.client.get(reverse("content:home"))
        self.assertEqual(res.status_code, 200)
        self.assertContains(res, "تکنولوژی‌های پایه")

    def test_home_renders_english(self):
        res = self.client.get(reverse("content:home") + "?lang=en")
        self.assertContains(res, "Core Tech &amp; Engineering")
        self.assertContains(res, 'dir="ltr"')

    def test_project_page(self):
        project = Project.objects.first()
        res = self.client.get(project.get_absolute_url())
        self.assertEqual(res.status_code, 200)

    def test_unpublished_project_404s(self):
        project = Project.objects.first()
        project.is_published = False
        project.save()
        self.assertEqual(self.client.get(project.get_absolute_url()).status_code, 404)


class RegressionTests(SeedMixin, TestCase):
    """Guards for bugs that were actually found and fixed."""

    @classmethod
    def setUpTestData(cls):
        cls.seed()

    def setUp(self):
        self.api = APIClient()
        from django.core.cache import cache
        cache.clear()

    def test_m2m_filter_does_not_duplicate_rows(self):
        """?technologies__slug= used to return the same project several times."""
        res = self.api.get("/api/v1/projects/?technologies__slug=python")
        slugs = [p["slug"] for p in res.json()["results"]]
        self.assertEqual(len(slugs), len(set(slugs)), f"duplicates: {slugs}")

    def test_search_across_relations_does_not_duplicate(self):
        res = self.api.get("/api/v1/projects/?search=a")
        slugs = [p["slug"] for p in res.json()["results"]]
        self.assertEqual(len(slugs), len(set(slugs)))

    def test_detail_fetches_object_once(self):
        """retrieve() used to hit the DB twice for the same row."""
        from django.db import connection
        from django.test.utils import CaptureQueriesContext

        project = Project.objects.first()
        with CaptureQueriesContext(connection) as ctx:
            self.api.get(f"/api/v1/projects/{project.slug}/")
        main = [q for q in ctx.captured_queries
                if 'FROM "content_project"' in q["sql"] and "SELECT" in q["sql"]
                and "technologies" not in q["sql"]]
        self.assertEqual(len(main), 1, f"expected 1 project SELECT, got {len(main)}")

    def test_view_count_in_response_is_not_stale(self):
        project = Project.objects.first()
        start = project.view_count
        body = self.api.get(f"/api/v1/projects/{project.slug}/").json()
        self.assertEqual(body["view_count"], start + 1)
        project.refresh_from_db()
        self.assertEqual(project.view_count, start + 1)

    def test_home_payload_is_cached(self):
        first = self.api.get("/api/v1/home/")
        second = self.api.get("/api/v1/home/")
        self.assertEqual(first["X-Cache"], "MISS")
        self.assertEqual(second["X-Cache"], "HIT")

    def test_saving_content_busts_the_cache(self):
        self.api.get("/api/v1/home/")
        cap = Capability.objects.first()
        cap.title_fa = "عنوان تازه"
        cap.save()
        res = self.api.get("/api/v1/home/")
        self.assertEqual(res["X-Cache"], "MISS")
        titles = [c["title_fa"] for c in res.json()["capabilities"]]
        self.assertIn("عنوان تازه", titles)

    def test_home_cache_is_per_language(self):
        fa = self.api.get("/api/v1/home/?lang=fa").json()
        en = self.api.get("/api/v1/home/?lang=en").json()
        self.assertNotEqual(fa["capabilities"][0]["title"],
                            en["capabilities"][0]["title"])

    def test_list_sends_etag_and_honours_304(self):
        first = self.api.get("/api/v1/capabilities/")
        etag = first["ETag"]
        self.assertTrue(etag)
        again = self.api.get("/api/v1/capabilities/", HTTP_IF_NONE_MATCH=etag)
        self.assertEqual(again.status_code, 304)

    def test_health_is_not_throttled(self):
        for _ in range(30):
            self.assertEqual(self.client.get("/api/v1/health/").status_code, 200)

    def test_track_requires_valid_payload(self):
        self.assertEqual(self.api.post("/api/v1/track/", {}, format="json").status_code, 400)

    def test_track_stores_a_pageview_without_ip(self):
        from content.models import PageView
        res = self.api.post("/api/v1/track/", {"path": "/", "language": "fa"}, format="json")
        self.assertEqual(res.status_code, 201)
        pv = PageView.objects.get()
        self.assertEqual(pv.path, "/")
        for field in pv._meta.fields:
            self.assertNotIn("ip_address", field.name)

    def test_seed_is_idempotent(self):
        from django.core.management import call_command
        before = Capability.objects.count()
        call_command("seed_content", verbosity=0)
        call_command("seed_content", verbosity=0)
        self.assertEqual(Capability.objects.count(), before)

    def test_site_settings_cache_is_invalidated_on_save(self):
        s = SiteSettings.load()
        s.brand_name = "Renamed"
        s.save()
        self.assertEqual(SiteSettings.load().brand_name, "Renamed")


class SeoTests(SeedMixin, TestCase):
    @classmethod
    def setUpTestData(cls):
        cls.seed()

    def test_sitemap_lists_projects_and_articles(self):
        res = self.client.get("/sitemap.xml")
        self.assertEqual(res.status_code, 200)
        body = res.content.decode()
        self.assertIn("/projects/iot-simulation-lab/", body)
        self.assertIn("<lastmod>", body)

    def test_robots_points_at_the_sitemap(self):
        res = self.client.get("/robots.txt")
        self.assertEqual(res.status_code, 200)
        body = res.content.decode()
        self.assertIn("Disallow: /admin/", body)
        self.assertIn("sitemap.xml", body)


class ManagementCommandTests(TestCase):
    def test_cleanup_pageviews_removes_only_old_rows(self):
        from datetime import timedelta

        from django.core.management import call_command
        from django.utils import timezone

        from content.models import PageView

        fresh = PageView.objects.create(path="/new")
        old = PageView.objects.create(path="/old")
        PageView.objects.filter(pk=old.pk).update(
            created_at=timezone.now() - timedelta(days=200)
        )

        call_command("cleanup_pageviews", days=90, verbosity=0)
        remaining = list(PageView.objects.values_list("path", flat=True))
        self.assertEqual(remaining, ["/new"])

    def test_content_stats_runs(self):
        from io import StringIO

        from django.core.management import call_command

        out = StringIO()
        call_command("content_stats", stdout=out)
        self.assertIn("محتوا", out.getvalue())
