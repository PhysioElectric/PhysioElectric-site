"""Public content routes (HTML pages served by Django)."""

from django.urls import path

from . import views_pages

app_name = "content"

urlpatterns = [
    path("", views_pages.HomePageView.as_view(), name="home"),
    path("projects/<slug:slug>/", views_pages.ProjectDetailView.as_view(), name="project-detail"),
    path("articles/<slug:slug>/", views_pages.ArticleDetailView.as_view(), name="article-detail"),
    path("capabilities/<slug:slug>/", views_pages.CapabilityDetailView.as_view(),
         name="capability-detail"),
]
