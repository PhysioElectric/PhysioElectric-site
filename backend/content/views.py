"""Public, read-only content API + the aggregated home payload."""

import hashlib

from django.core.cache import cache
from django.db.models import F, Max
from django.utils.http import http_date, quote_etag
from drf_spectacular.utils import OpenApiParameter, extend_schema
from rest_framework import mixins, status, viewsets
from rest_framework.decorators import (
    action,
    api_view,
    permission_classes,
    throttle_classes,
)
from rest_framework.permissions import AllowAny
from rest_framework.response import Response
from rest_framework.throttling import ScopedRateThrottle

from .models import (
    FAQ,
    Article,
    Capability,
    PageView,
    ProcessStep,
    Project,
    SiteSettings,
    Statistic,
    TeamMember,
    Technology,
    Testimonial,
)
from .serializers import (
    ArticleDetailSerializer,
    ArticleListSerializer,
    CapabilitySerializer,
    FAQSerializer,
    HealthSerializer,
    HomePayloadSerializer,
    OkSerializer,
    ProcessStepSerializer,
    ProjectDetailSerializer,
    ProjectListSerializer,
    SiteSettingsSerializer,
    StatisticSerializer,
    TeamMemberSerializer,
    TechnologySerializer,
    TestimonialSerializer,
    TrackRequestSerializer,
)

LANG_PARAM = OpenApiParameter(
    name="lang",
    description="زبان محتوای resolved شده (fa یا en). پیش‌فرض fa.",
    required=False,
    type=str,
    enum=["fa", "en"],
)

HOME_CACHE_TTL = 300  # seconds


def _resolve_lang(request) -> str:
    lang = request.query_params.get("lang") or "fa"
    return lang if lang in {"fa", "en"} else "fa"


class ConditionalGetMixin:
    """
    Attach ETag / Last-Modified to list responses.

    Content changes rarely, so letting the browser revalidate with a 304 saves
    both bandwidth and serialization work.
    """

    def _collection_stamp(self):
        model = self.get_queryset().model
        return model.objects.aggregate(m=Max("updated_at"))["m"]

    def list(self, request, *args, **kwargs):
        response = super().list(request, *args, **kwargs)
        stamp = self._collection_stamp()
        if stamp is not None:
            token = f"{self.basename}:{_resolve_lang(request)}:{stamp.timestamp()}"
            response["ETag"] = quote_etag(hashlib.md5(token.encode()).hexdigest())
            response["Last-Modified"] = http_date(stamp.timestamp())
            response["Cache-Control"] = "public, max-age=60"
        return response


class ViewCountMixin:
    """
    Increment a hit counter without paying for a second fetch.

    The naive version calls `get_object()` again after `super().retrieve()`,
    which re-runs the query *and* the prefetch. This fetches once and patches
    the already-serialized payload so the response is not off by one.
    """

    view_count_field = "view_count"

    def retrieve(self, request, *args, **kwargs):
        instance = self.get_object()
        serializer = self.get_serializer(instance)
        data = dict(serializer.data)

        model = type(instance)
        model.objects.filter(pk=instance.pk).update(
            **{self.view_count_field: F(self.view_count_field) + 1}
        )
        if self.view_count_field in data:
            data[self.view_count_field] = (data[self.view_count_field] or 0) + 1

        return Response(data)


class PublishedReadOnlyViewSet(
    ConditionalGetMixin,
    mixins.ListModelMixin,
    mixins.RetrieveModelMixin,
    viewsets.GenericViewSet,
):
    """Everything public shares the same read-only, published-only behaviour."""

    permission_classes = [AllowAny]
    lookup_field = "slug"

    def get_queryset(self):
        return self.queryset.model.objects.published()

    def filter_queryset(self, queryset):
        queryset = super().filter_queryset(queryset)
        # Joining across a many-to-many (?technologies__slug=, ?search=) makes
        # the same row come back once per matching relation.
        if queryset.query.is_sliced:
            return queryset
        return queryset.distinct()


@extend_schema(parameters=[LANG_PARAM], tags=["content"])
class CapabilityViewSet(PublishedReadOnlyViewSet):
    queryset = Capability.objects.all()
    serializer_class = CapabilitySerializer
    filterset_fields = ["is_featured"]
    search_fields = ["title_fa", "title_en", "description_fa", "description_en"]
    ordering_fields = ["order", "created_at"]


@extend_schema(parameters=[LANG_PARAM], tags=["content"])
class ProjectViewSet(ViewCountMixin, PublishedReadOnlyViewSet):
    queryset = Project.objects.all()
    filterset_fields = ["status", "is_featured", "year", "category__slug", "technologies__slug"]
    search_fields = ["title_fa", "title_en", "summary_fa", "summary_en", "client"]
    ordering_fields = ["order", "year", "created_at", "view_count"]

    def get_queryset(self):
        return (
            Project.objects.published()
            .select_related("category")
            .prefetch_related("technologies")
        )

    def get_serializer_class(self):
        return ProjectDetailSerializer if self.action == "retrieve" else ProjectListSerializer

    @extend_schema(parameters=[LANG_PARAM], tags=["content"])
    @action(detail=False, methods=["get"])
    def featured(self, request):
        qs = self.get_queryset().filter(is_featured=True)[:8]
        return Response(self.get_serializer(qs, many=True).data)


@extend_schema(parameters=[LANG_PARAM], tags=["content"])
class ArticleViewSet(ViewCountMixin, PublishedReadOnlyViewSet):
    queryset = Article.objects.all()
    filterset_fields = ["is_featured", "category__slug"]
    search_fields = ["title_fa", "title_en", "excerpt_fa", "excerpt_en", "body_fa", "body_en"]
    ordering_fields = ["published_at", "created_at", "view_count"]

    def get_queryset(self):
        return Article.objects.published().select_related("category", "author")

    def get_serializer_class(self):
        return ArticleDetailSerializer if self.action == "retrieve" else ArticleListSerializer


@extend_schema(parameters=[LANG_PARAM], tags=["content"])
class FAQViewSet(PublishedReadOnlyViewSet):
    queryset = FAQ.objects.all()
    serializer_class = FAQSerializer
    lookup_field = "pk"
    search_fields = ["title_fa", "title_en", "description_fa", "description_en"]


@extend_schema(parameters=[LANG_PARAM], tags=["content"])
class ProcessStepViewSet(PublishedReadOnlyViewSet):
    queryset = ProcessStep.objects.all()
    serializer_class = ProcessStepSerializer
    lookup_field = "pk"


@extend_schema(parameters=[LANG_PARAM], tags=["content"])
class TeamMemberViewSet(PublishedReadOnlyViewSet):
    queryset = TeamMember.objects.all()
    serializer_class = TeamMemberSerializer


@extend_schema(parameters=[LANG_PARAM], tags=["content"])
class TestimonialViewSet(PublishedReadOnlyViewSet):
    queryset = Testimonial.objects.all()
    serializer_class = TestimonialSerializer
    lookup_field = "pk"


@extend_schema(parameters=[LANG_PARAM], tags=["content"])
class StatisticViewSet(PublishedReadOnlyViewSet):
    queryset = Statistic.objects.all()
    serializer_class = StatisticSerializer
    lookup_field = "pk"


@extend_schema(tags=["content"])
class TechnologyViewSet(mixins.ListModelMixin, viewsets.GenericViewSet):
    queryset = Technology.objects.all()
    serializer_class = TechnologySerializer
    permission_classes = [AllowAny]
    pagination_class = None


@extend_schema(
    parameters=[LANG_PARAM],
    responses=HomePayloadSerializer,
    tags=["content"],
    description="کل محتوای صفحه اصلی در یک درخواست (کش‌شده).",
)
@api_view(["GET"])
@permission_classes([AllowAny])
def home_payload(request):
    """
    Single-shot payload for the landing page.

    Cached per language: this is the hottest endpoint and its content only
    changes when an editor saves something (which busts the cache).
    """
    lang = _resolve_lang(request)
    cache_key = f"home_payload:{lang}"
    cached = cache.get(cache_key)
    if cached is not None:
        response = Response(cached)
        response["X-Cache"] = "HIT"
        return response

    ctx = {"request": request}
    data = {
        "site": SiteSettingsSerializer(SiteSettings.load(), context=ctx).data,
        "capabilities": CapabilitySerializer(
            Capability.objects.published(), many=True, context=ctx
        ).data,
        "projects": ProjectListSerializer(
            Project.objects.published()
            .select_related("category")
            .prefetch_related("technologies")[:12],
            many=True, context=ctx,
        ).data,
        "process": ProcessStepSerializer(
            ProcessStep.objects.published(), many=True, context=ctx
        ).data,
        "articles": ArticleListSerializer(
            Article.objects.published().select_related("category", "author")[:6],
            many=True, context=ctx,
        ).data,
        "faqs": FAQSerializer(FAQ.objects.published(), many=True, context=ctx).data,
        "statistics": StatisticSerializer(
            Statistic.objects.published(), many=True, context=ctx
        ).data,
        "testimonials": TestimonialSerializer(
            Testimonial.objects.published(), many=True, context=ctx
        ).data,
        "team": TeamMemberSerializer(TeamMember.objects.published(), many=True, context=ctx).data,
    }
    cache.set(cache_key, data, HOME_CACHE_TTL)
    response = Response(data)
    response["X-Cache"] = "MISS"
    return response


@extend_schema(tags=["ops"], responses=HealthSerializer,
               description="سلامت سرویس و اتصال دیتابیس.")
@api_view(["GET"])
@permission_classes([AllowAny])
@throttle_classes([])  # monitoring must never be rate-limited out
def health(request):
    from django.db import connection

    try:
        with connection.cursor() as cursor:
            cursor.execute("SELECT 1")
            cursor.fetchone()
        db_ok = True
    except Exception:  # pragma: no cover - only on a broken DB
        db_ok = False

    return Response(
        {
            "ok": db_ok,
            "service": "physioelectric-backend",
            "version": "1.0.0",
            "database": "up" if db_ok else "down",
            "requestId": getattr(request, "id", None),
        },
        status=status.HTTP_200_OK if db_ok else status.HTTP_503_SERVICE_UNAVAILABLE,
    )


class TrackThrottle(ScopedRateThrottle):
    scope = "track"


@extend_schema(tags=["ops"], request=TrackRequestSerializer, responses=OkSerializer,
               description="ثبت بازدید صفحه (بدون ذخیره آی‌پی).")
@api_view(["POST"])
@permission_classes([AllowAny])
@throttle_classes([TrackThrottle])
def track_view(request):
    """Store a coarse page hit. Rate-limited so it can't be used to flood the DB."""
    serializer = TrackRequestSerializer(data=request.data)
    serializer.is_valid(raise_exception=True)
    payload = serializer.validated_data

    ua = request.META.get("HTTP_USER_AGENT", "")[:300]
    PageView.objects.create(
        path=payload["path"][:300],
        referrer=payload.get("referrer", "")[:300],
        language=payload.get("language", "")[:5],
        user_agent_hash=hashlib.sha256(ua.encode()).hexdigest() if ua else "",
    )
    return Response({"ok": True}, status=status.HTTP_201_CREATED)
