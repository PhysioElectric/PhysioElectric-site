"""Admin — the actual editing surface for the marketing site."""

from django.contrib import admin
from django.utils.html import format_html

from .models import (
    FAQ,
    Article,
    ArticleCategory,
    Capability,
    PageView,
    ProcessStep,
    Project,
    ProjectCategory,
    SiteSettings,
    Statistic,
    TeamMember,
    Technology,
    Testimonial,
)

admin.site.site_header = "مدیریت PhysioElectric"
admin.site.site_title = "PhysioElectric"
admin.site.index_title = "پنل مدیریت محتوا"


class PublishedAdminMixin:
    """Shared publish/unpublish actions."""

    actions = ["make_published", "make_draft"]

    @admin.action(description="انتشار موارد انتخاب‌شده")
    def make_published(self, request, queryset):
        updated = queryset.update(is_published=True)
        self.message_user(request, f"{updated} مورد منتشر شد.")

    @admin.action(description="پیش‌نویس کردن موارد انتخاب‌شده")
    def make_draft(self, request, queryset):
        updated = queryset.update(is_published=False)
        self.message_user(request, f"{updated} مورد پیش‌نویس شد.")


@admin.register(SiteSettings)
class SiteSettingsAdmin(admin.ModelAdmin):
    fieldsets = (
        ("برند", {"fields": ("brand_name", "tagline_fa", "tagline_en")}),
        ("هیرو", {"fields": (
            "hero_badge_fa", "hero_badge_en",
            "hero_title_fa", "hero_title_en",
            "hero_subtitle_fa", "hero_subtitle_en",
        )}),
        ("تماس", {"fields": ("email", "phone", "address_fa", "address_en")}),
        ("شبکه‌های اجتماعی", {"fields": (
            "github_url", "linkedin_url", "twitter_url", "instagram_url",
        )}),
        ("سئو", {"fields": ("meta_description_fa", "meta_description_en")}),
    )

    def has_add_permission(self, request):
        # Singleton: the row is created on first load.
        return not SiteSettings.objects.exists()

    def has_delete_permission(self, request, obj=None):
        return False


@admin.register(Capability)
class CapabilityAdmin(PublishedAdminMixin, admin.ModelAdmin):
    list_display = ["title_fa", "title_en", "icon", "is_featured", "is_published", "order"]
    list_editable = ["is_featured", "is_published", "order"]
    list_filter = ["is_published", "is_featured"]
    search_fields = ["title_fa", "title_en", "description_fa", "description_en"]
    prepopulated_fields = {"slug": ("title_en",)}
    fieldsets = (
        (None, {"fields": ("icon", "slug", "order", "is_featured", "is_published")}),
        ("فارسی", {"fields": ("title_fa", "description_fa", "link_label_fa")}),
        ("English", {"fields": ("title_en", "description_en", "link_label_en")}),
        ("لینک", {"fields": ("link_url",)}),
    )


@admin.register(Technology)
class TechnologyAdmin(admin.ModelAdmin):
    list_display = ["name", "slug", "swatch", "icon"]
    search_fields = ["name"]
    prepopulated_fields = {"slug": ("name",)}

    @admin.display(description="رنگ")
    def swatch(self, obj):
        if not obj.color:
            return "—"
        return format_html(
            '<span style="display:inline-block;width:18px;height:18px;'
            'border-radius:4px;background:{};border:1px solid #ccc"></span> {}',
            obj.color, obj.color,
        )


@admin.register(ProjectCategory)
class ProjectCategoryAdmin(admin.ModelAdmin):
    list_display = ["title_fa", "title_en", "slug", "order"]
    prepopulated_fields = {"slug": ("title_en",)}
    search_fields = ["title_fa", "title_en", "slug"]


@admin.register(Project)
class ProjectAdmin(PublishedAdminMixin, admin.ModelAdmin):
    list_display = ["thumb", "title_fa", "category", "status", "year",
                    "is_featured", "is_published", "view_count", "order"]
    list_display_links = ["thumb", "title_fa"]
    list_editable = ["is_featured", "is_published", "order"]
    list_filter = ["is_published", "is_featured", "status", "category", "year"]
    search_fields = ["title_fa", "title_en", "summary_fa", "summary_en", "client"]
    filter_horizontal = ["technologies"]
    prepopulated_fields = {"slug": ("title_en",)}
    readonly_fields = ["view_count", "created_at", "updated_at", "preview"]
    autocomplete_fields = ["category"]
    date_hierarchy = "created_at"
    # Without this the changelist issues one query per row for the FK column.
    list_select_related = ["category"]
    fieldsets = (
        (None, {"fields": ("slug", "category", "technologies", "status", "year",
                           "client", "order", "is_featured", "is_published", "published_at")}),
        ("فارسی", {"fields": ("title_fa", "summary_fa", "description_fa", "body_fa")}),
        ("English", {"fields": ("title_en", "summary_en", "description_en", "body_en")}),
        ("رسانه", {"fields": ("cover_image", "cover_image_url", "accent_color", "preview")}),
        ("لینک‌ها", {"fields": ("external_url", "repo_url")}),
        ("آمار", {"fields": ("view_count", "created_at", "updated_at")}),
    )

    @admin.display(description="تصویر")
    def thumb(self, obj):
        if obj.image:
            return format_html(
                '<img src="{}" style="width:56px;height:36px;object-fit:cover;'
                'border-radius:6px" />', obj.image,
            )
        return "—"

    @admin.display(description="پیش‌نمایش")
    def preview(self, obj):
        if obj.image:
            return format_html('<img src="{}" style="max-width:420px;border-radius:10px" />', obj.image)
        return "تصویری ثبت نشده"


@admin.register(ArticleCategory)
class ArticleCategoryAdmin(admin.ModelAdmin):
    list_display = ["title_fa", "title_en", "slug"]
    prepopulated_fields = {"slug": ("title_en",)}
    search_fields = ["title_fa", "title_en"]


@admin.register(Article)
class ArticleAdmin(PublishedAdminMixin, admin.ModelAdmin):
    list_display = ["title_fa", "category", "author", "reading_minutes",
                    "is_featured", "is_published", "published_at", "view_count"]
    list_editable = ["is_featured", "is_published"]
    list_filter = ["is_published", "is_featured", "category"]
    search_fields = ["title_fa", "title_en", "excerpt_fa", "excerpt_en"]
    prepopulated_fields = {"slug": ("title_en",)}
    readonly_fields = ["view_count", "created_at", "updated_at"]
    autocomplete_fields = ["category"]
    date_hierarchy = "published_at"
    list_select_related = ["category", "author"]
    fieldsets = (
        (None, {"fields": ("slug", "category", "author", "reading_minutes",
                           "order", "is_featured", "is_published", "published_at")}),
        ("فارسی", {"fields": ("title_fa", "excerpt_fa", "description_fa", "body_fa")}),
        ("English", {"fields": ("title_en", "excerpt_en", "description_en", "body_en")}),
        ("رسانه", {"fields": ("cover_image", "cover_image_url")}),
        ("آمار", {"fields": ("view_count", "created_at", "updated_at")}),
    )


@admin.register(ProcessStep)
class ProcessStepAdmin(PublishedAdminMixin, admin.ModelAdmin):
    list_display = ["number", "title_fa", "title_en", "icon", "is_published"]
    list_editable = ["is_published"]
    ordering = ["number"]


@admin.register(FAQ)
class FAQAdmin(PublishedAdminMixin, admin.ModelAdmin):
    list_display = ["title_fa", "is_published", "order"]
    list_editable = ["is_published", "order"]
    search_fields = ["title_fa", "title_en", "description_fa", "description_en"]
    fieldsets = (
        (None, {"fields": ("order", "is_published")}),
        ("فارسی", {"fields": ("title_fa", "description_fa")}),
        ("English", {"fields": ("title_en", "description_en")}),
    )


@admin.register(TeamMember)
class TeamMemberAdmin(PublishedAdminMixin, admin.ModelAdmin):
    list_display = ["title_fa", "role_fa", "email", "is_published", "order"]
    list_editable = ["is_published", "order"]
    prepopulated_fields = {"slug": ("title_en",)}


@admin.register(Testimonial)
class TestimonialAdmin(PublishedAdminMixin, admin.ModelAdmin):
    list_display = ["author_name", "author_role_fa", "rating", "is_published", "order"]
    list_editable = ["is_published", "order"]


@admin.register(Statistic)
class StatisticAdmin(PublishedAdminMixin, admin.ModelAdmin):
    list_display = ["value", "title_fa", "title_en", "is_published", "order"]
    list_editable = ["is_published", "order"]


@admin.register(PageView)
class PageViewAdmin(admin.ModelAdmin):
    list_display = ["path", "language", "referrer", "created_at"]
    list_filter = ["language", "created_at"]
    search_fields = ["path", "referrer"]
    date_hierarchy = "created_at"
    readonly_fields = [f.name for f in PageView._meta.fields]

    def has_add_permission(self, request):
        return False
