"""Keep caches honest: any content write invalidates the derived caches."""

from django.db.models.signals import post_delete, post_save, m2m_changed
from django.dispatch import receiver

from .cache import bust_content_cache
from .models import (
    FAQ,
    Article,
    Capability,
    ProcessStep,
    Project,
    SiteSettings,
    Statistic,
    TeamMember,
    Testimonial,
)

CACHED_MODELS = (
    SiteSettings, Capability, Project, Article,
    ProcessStep, FAQ, Statistic, Testimonial, TeamMember,
)


@receiver(post_save)
@receiver(post_delete)
def _invalidate(sender, **kwargs):
    if sender in CACHED_MODELS:
        bust_content_cache()


@receiver(m2m_changed, sender=Project.technologies.through)
def _invalidate_m2m(sender, **kwargs):
    bust_content_cache()
