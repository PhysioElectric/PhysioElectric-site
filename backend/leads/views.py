"""Public form endpoints."""

import logging
import threading

from django.conf import settings
from django.core.mail import send_mail
from drf_spectacular.utils import extend_schema
from rest_framework import mixins, status, viewsets
from rest_framework.permissions import AllowAny
from rest_framework.response import Response
from rest_framework.throttling import ScopedRateThrottle

from .models import ContactMessage, Subscriber
from .serializers import ContactMessageSerializer, SubscriberSerializer

logger = logging.getLogger("physio.leads")


@extend_schema(tags=["leads"], description="ارسال پیام تماس / درخواست پروژه.")
class ContactMessageViewSet(mixins.CreateModelMixin, viewsets.GenericViewSet):
    queryset = ContactMessage.objects.all()
    serializer_class = ContactMessageSerializer
    permission_classes = [AllowAny]
    throttle_classes = [ScopedRateThrottle]
    throttle_scope = "contact"

    def perform_create(self, serializer):
        instance = serializer.save()
        logger.info("contact message #%s from %s", instance.pk, instance.email)
        # SMTP can take seconds; the visitor should not wait for it.
        threading.Thread(target=self._notify, args=(instance,), daemon=True).start()

    def _notify(self, instance: ContactMessage) -> None:
        recipients = getattr(settings, "CONTACT_NOTIFY_EMAILS", [])
        if not recipients:
            return
        try:
            send_mail(
                subject=f"[PhysioElectric] پیام جدید از {instance.name}",
                message=(
                    f"نام: {instance.name}\n"
                    f"ایمیل: {instance.email}\n"
                    f"تلفن: {instance.phone}\n"
                    f"شرکت: {instance.company}\n"
                    f"سرویس: {instance.service}\n"
                    f"بودجه: {instance.get_budget_display()}\n\n"
                    f"{instance.message}"
                ),
                from_email=settings.DEFAULT_FROM_EMAIL,
                recipient_list=recipients,
                fail_silently=True,
            )
        except Exception:  # pragma: no cover - notification must never 500 the form
            logger.exception("failed to send contact notification")

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        self.perform_create(serializer)
        return Response(
            {
                "ok": True,
                "message": "پیام شما ثبت شد. به‌زودی پاسخ می‌دهیم.",
                "id": serializer.instance.pk,
            },
            status=status.HTTP_201_CREATED,
        )


@extend_schema(tags=["leads"], description="عضویت در خبرنامه.")
class SubscriberViewSet(mixins.CreateModelMixin, viewsets.GenericViewSet):
    queryset = Subscriber.objects.all()
    serializer_class = SubscriberSerializer
    permission_classes = [AllowAny]
    throttle_classes = [ScopedRateThrottle]
    throttle_scope = "subscribe"

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        serializer.save()
        return Response(
            {"ok": True, "message": "عضویت شما ثبت شد."},
            status=status.HTTP_201_CREATED,
        )
