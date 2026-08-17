"""
Server-rendered pages.

The landing page is rendered from the database, so editors change the admin and
the site follows — no HTML edits, no rebuild step.
"""

from django.db.models import F
from django.views.generic import DetailView, TemplateView

from .models import (
    FAQ,
    Article,
    Capability,
    ProcessStep,
    Project,
    Statistic,
    TeamMember,
    Testimonial,
)


class HomePageView(TemplateView):
    template_name = "pages/home.html"

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        # lang / is_rtl / site already come from the context processor.
        ctx.update(
            capabilities=Capability.objects.published(),
            projects=(
                Project.objects.published()
                .select_related("category")
                .prefetch_related("technologies")[:12]
            ),
            process_steps=ProcessStep.objects.published(),
            articles=Article.objects.published().select_related("category")[:3],
            faqs=FAQ.objects.published(),
            statistics=Statistic.objects.published(),
            testimonials=Testimonial.objects.published(),
            team=TeamMember.objects.published(),
        )
        return ctx


class ProjectDetailView(DetailView):
    template_name = "pages/project_detail.html"
    context_object_name = "project"

    def get_queryset(self):
        return Project.objects.published().select_related("category").prefetch_related("technologies")

    def get_object(self, queryset=None):
        obj = super().get_object(queryset)
        Project.objects.filter(pk=obj.pk).update(view_count=F("view_count") + 1)
        return obj

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx["related"] = (
            Project.objects.published()
            .exclude(pk=self.object.pk)
            .filter(category=self.object.category)
            .select_related("category")[:3]
        )
        return ctx


class ArticleDetailView(DetailView):
    template_name = "pages/article_detail.html"
    context_object_name = "article"

    def get_queryset(self):
        return Article.objects.published().select_related("category", "author")

    def get_object(self, queryset=None):
        obj = super().get_object(queryset)
        Article.objects.filter(pk=obj.pk).update(view_count=F("view_count") + 1)
        return obj

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx["related"] = (
            Article.objects.published()
            .exclude(pk=self.object.pk)
            .select_related("category")[:3]
        )
        return ctx


class CapabilityDetailView(DetailView):
    template_name = "pages/capability_detail.html"
    context_object_name = "capability"

    def get_queryset(self):
        return Capability.objects.published()

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx["projects"] = (
            Project.objects.published().select_related("category")[:6]
        )
        return ctx
