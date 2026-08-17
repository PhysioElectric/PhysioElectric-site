"""Write-only serializers for the public forms."""

from rest_framework import serializers

from .models import ContactMessage, Subscriber


class ContactMessageSerializer(serializers.ModelSerializer):
    # Bots fill hidden fields; humans never see this one.
    website = serializers.CharField(required=False, allow_blank=True, write_only=True)

    class Meta:
        model = ContactMessage
        fields = [
            "id", "name", "email", "phone", "company", "subject", "message",
            "service", "budget", "language", "source_page", "website", "created_at",
        ]
        read_only_fields = ["id", "created_at"]
        extra_kwargs = {
            "name": {"min_length": 2},
            "message": {"min_length": 10},
        }

    def validate_website(self, value):
        if value:
            raise serializers.ValidationError("spam detected")
        return value

    def validate_message(self, value):
        # Cheap link-flood heuristic — real spam filtering happens downstream.
        if value.lower().count("http") > 4:
            raise serializers.ValidationError("پیام شامل لینک بیش از حد مجاز است.")
        return value

    def create(self, validated_data):
        validated_data.pop("website", None)
        request = self.context.get("request")
        if request:
            xff = request.META.get("HTTP_X_FORWARDED_FOR", "")
            ip = xff.split(",")[0].strip() or request.META.get("REMOTE_ADDR", "")
            validated_data["ip_hash"] = ContactMessage.hash_ip(ip)
            validated_data["user_agent"] = request.META.get("HTTP_USER_AGENT", "")[:300]
        return super().create(validated_data)


class SubscriberSerializer(serializers.ModelSerializer):
    # The model enforces uniqueness, but re-subscribing is a normal thing to do.
    # Drop the auto-generated UniqueValidator so create() can reactivate instead
    # of returning a confusing 400.
    email = serializers.EmailField(validators=[])

    class Meta:
        model = Subscriber
        fields = ["id", "email", "name", "language", "created_at"]
        read_only_fields = ["id", "created_at"]

    def validate_email(self, value):
        return value.strip().lower()

    def create(self, validated_data):
        # Re-subscribing must not blow up with a unique-constraint error.
        obj, created = Subscriber.objects.get_or_create(
            email__iexact=validated_data["email"],
            defaults=validated_data,
        )
        if not created and not obj.is_active:
            obj.is_active = True
            obj.unsubscribed_at = None
            obj.save(update_fields=["is_active", "unsubscribed_at", "updated_at"])
        return obj
