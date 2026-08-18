"""Versioned API surface — /api/v1/."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from content import views as content_views
from leads import views as lead_views

router = DefaultRouter()
router.register("capabilities", content_views.CapabilityViewSet, basename="capability")
router.register("projects", content_views.ProjectViewSet, basename="project")
router.register("articles", content_views.ArticleViewSet, basename="article")
router.register("faqs", content_views.FAQViewSet, basename="faq")
router.register("process", content_views.ProcessStepViewSet, basename="processstep")
router.register("team", content_views.TeamMemberViewSet, basename="teammember")
router.register("testimonials", content_views.TestimonialViewSet, basename="testimonial")
router.register("statistics", content_views.StatisticViewSet, basename="statistic")
router.register("technologies", content_views.TechnologyViewSet, basename="technology")
router.register("contact", lead_views.ContactMessageViewSet, basename="contact")
router.register("subscribe", lead_views.SubscriberViewSet, basename="subscribe")

urlpatterns = [
    path("home/", content_views.home_payload, name="home-payload"),
    path("health/", content_views.health, name="health"),
    path("track/", content_views.track_view, name="track"),
    path("", include(router.urls)),
]
