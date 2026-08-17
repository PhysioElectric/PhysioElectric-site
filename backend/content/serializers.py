"""API serializers for public content."""

from rest_framework import serializers

from .models import (
    FAQ,
    Article,
    ArticleCategory,
    Capability,
    ProcessStep,
    Project,
    ProjectCategory,
    SiteSettings,
    Statistic,
    TeamMember,
    Technology,
    Testimonial,
)


class BilingualSerializerMixin:
    """
    Emit both languages *and* a resolved `title`/`description`.

    The resolved pair follows `?lang=` (default fa) so a client can stay dumb
    and just render `title`, while a bilingual client can still read
    `title_fa` / `title_en`.
    """

    def _lang(self) -> str:
        request = self.context.get("request")
        lang = (request.query_params.get("lang") if request else None) or "fa"
        return lang if lang in {"fa", "en"} else "fa"

    def to_representation(self, instance):
        data = super().to_representation(instance)
        lang = self._lang()
        for field in ("title", "description", "summary", "excerpt", "role", "link_label"):
            fa, en = f"{field}_fa", f"{field}_en"
            if fa in data or en in data:
                primary = data.get(f"{field}_{lang}") or ""
                fallback = data.get(f"{field}_{'en' if lang == 'fa' else 'fa'}") or ""
                data[field] = primary or fallback
        data["lang"] = lang
        return data


class TechnologySerializer(serializers.ModelSerializer):
    class Meta:
        model = Technology
        fields = ["id", "name", "slug", "icon", "color"]


class ProjectCategorySerializer(BilingualSerializerMixin, serializers.ModelSerializer):
    class Meta:
        model = ProjectCategory
        fields = ["id", "slug", "title_fa", "title_en", "order"]


class ArticleCategorySerializer(BilingualSerializerMixin, serializers.ModelSerializer):
    class Meta:
        model = ArticleCategory
        fields = ["id", "slug", "title_fa", "title_en", "color"]


class CapabilitySerializer(BilingualSerializerMixin, serializers.ModelSerializer):
    class Meta:
        model = Capability
        fields = [
            "id", "slug", "icon", "order", "is_featured",
            "title_fa", "title_en", "description_fa", "description_en",
            "link_label_fa", "link_label_en", "link_url",
        ]


class ProjectListSerializer(BilingualSerializerMixin, serializers.ModelSerializer):
    category = ProjectCategorySerializer(read_only=True)
    technologies = TechnologySerializer(many=True, read_only=True)
    image = serializers.CharField(read_only=True)

    class Meta:
        model = Project
        fields = [
            "id", "slug", "order", "is_featured", "status", "year", "client",
            "title_fa", "title_en", "summary_fa", "summary_en",
            "category", "technologies", "image", "accent_color",
            "external_url", "repo_url", "view_count", "created_at",
        ]


class ProjectDetailSerializer(ProjectListSerializer):
    class Meta(ProjectListSerializer.Meta):
        fields = ProjectListSerializer.Meta.fields + [
            "description_fa", "description_en", "body_fa", "body_en",
            "published_at", "updated_at",
        ]


class ProcessStepSerializer(BilingualSerializerMixin, serializers.ModelSerializer):
    class Meta:
        model = ProcessStep
        fields = ["id", "number", "icon", "title_fa", "title_en",
                  "description_fa", "description_en"]


class ArticleListSerializer(BilingualSerializerMixin, serializers.ModelSerializer):
    category = ArticleCategorySerializer(read_only=True)
    image = serializers.CharField(read_only=True)
    author_name = serializers.CharField(source="author.get_full_name", read_only=True, default="")

    class Meta:
        model = Article
        fields = [
            "id", "slug", "is_featured", "reading_minutes", "view_count",
            "title_fa", "title_en", "excerpt_fa", "excerpt_en",
            "category", "image", "author_name", "published_at", "created_at",
        ]


class ArticleDetailSerializer(ArticleListSerializer):
    class Meta(ArticleListSerializer.Meta):
        fields = ArticleListSerializer.Meta.fields + [
            "body_fa", "body_en", "description_fa", "description_en", "updated_at",
        ]


class FAQSerializer(BilingualSerializerMixin, serializers.ModelSerializer):
    # Friendlier aliases for an accordion widget.
    question = serializers.SerializerMethodField()
    answer = serializers.SerializerMethodField()

    class Meta:
        model = FAQ
        fields = ["id", "order", "title_fa", "title_en",
                  "description_fa", "description_en", "question", "answer"]

    def get_question(self, obj) -> str:
        return obj.localized("title", self._lang())

    def get_answer(self, obj) -> str:
        return obj.localized("description", self._lang())


class TeamMemberSerializer(BilingualSerializerMixin, serializers.ModelSerializer):
    photo = serializers.SerializerMethodField()

    class Meta:
        model = TeamMember
        fields = ["id", "slug", "title_fa", "title_en", "role_fa", "role_en",
                  "description_fa", "description_en", "photo",
                  "email", "github_url", "linkedin_url"]

    def get_photo(self, obj) -> str:
        return obj.photo.url if obj.photo else obj.photo_url


class TestimonialSerializer(BilingualSerializerMixin, serializers.ModelSerializer):
    class Meta:
        model = Testimonial
        fields = ["id", "author_name", "author_role_fa", "author_role_en",
                  "avatar_url", "rating", "title_fa", "title_en",
                  "description_fa", "description_en"]


class StatisticSerializer(BilingualSerializerMixin, serializers.ModelSerializer):
    class Meta:
        model = Statistic
        fields = ["id", "value", "icon", "title_fa", "title_en",
                  "description_fa", "description_en"]


class SiteSettingsSerializer(serializers.ModelSerializer):
    class Meta:
        model = SiteSettings
        fields = [
            "brand_name", "tagline_fa", "tagline_en",
            "hero_badge_fa", "hero_badge_en",
            "hero_title_fa", "hero_title_en",
            "hero_subtitle_fa", "hero_subtitle_en",
            "email", "phone", "address_fa", "address_en",
            "github_url", "linkedin_url", "twitter_url", "instagram_url",
            "meta_description_fa", "meta_description_en", "updated_at",
        ]


class HomePayloadSerializer(serializers.Serializer):
    """
    One request, whole landing page.

    Saves the frontend from firing eight round-trips on first paint.
    """

    site = SiteSettingsSerializer()
    capabilities = CapabilitySerializer(many=True)
    projects = ProjectListSerializer(many=True)
    process = ProcessStepSerializer(many=True)
    articles = ArticleListSerializer(many=True)
    faqs = FAQSerializer(many=True)
    statistics = StatisticSerializer(many=True)
    testimonials = TestimonialSerializer(many=True)
    team = TeamMemberSerializer(many=True)


class HealthSerializer(serializers.Serializer):
    """Shape of the /health/ response."""

    ok = serializers.BooleanField()
    service = serializers.CharField()
    version = serializers.CharField()
    database = serializers.CharField()
    requestId = serializers.CharField(allow_null=True)


class TrackRequestSerializer(serializers.Serializer):
    path = serializers.CharField(max_length=300)
    referrer = serializers.CharField(max_length=300, required=False, allow_blank=True)
    language = serializers.CharField(max_length=5, required=False, allow_blank=True)


class OkSerializer(serializers.Serializer):
    ok = serializers.BooleanField()
