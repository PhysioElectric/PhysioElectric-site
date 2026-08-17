"""Form endpoint tests."""

from django.test import TestCase
from rest_framework.test import APIClient

from leads.models import ContactMessage, Subscriber


class ContactTests(TestCase):
    def setUp(self):
        self.api = APIClient()
        self.payload = {
            "name": "پارسا",
            "email": "parsa@example.com",
            "message": "سلام، یک پروژه شبیه‌سازی حرارتی دارم و نیاز به مشاوره دارم.",
            "service": "comsol",
        }

    def test_valid_submission_is_stored(self):
        res = self.api.post("/api/v1/contact/", self.payload, format="json")
        self.assertEqual(res.status_code, 201)
        self.assertTrue(res.json()["ok"])
        self.assertEqual(ContactMessage.objects.count(), 1)

    def test_ip_is_hashed_not_stored_raw(self):
        self.api.post("/api/v1/contact/", self.payload, format="json",
                      HTTP_X_FORWARDED_FOR="203.0.113.7")
        msg = ContactMessage.objects.get()
        self.assertNotIn("203.0.113.7", msg.ip_hash)
        self.assertEqual(len(msg.ip_hash), 64)

    def test_honeypot_blocks_bots(self):
        res = self.api.post("/api/v1/contact/",
                            {**self.payload, "website": "http://spam.example"},
                            format="json")
        self.assertEqual(res.status_code, 400)
        self.assertEqual(ContactMessage.objects.count(), 0)

    def test_short_message_is_rejected(self):
        res = self.api.post("/api/v1/contact/", {**self.payload, "message": "hi"},
                            format="json")
        self.assertEqual(res.status_code, 400)
        self.assertIn("message", res.json()["fields"])

    def test_invalid_email_is_rejected(self):
        res = self.api.post("/api/v1/contact/", {**self.payload, "email": "nope"},
                            format="json")
        self.assertEqual(res.status_code, 400)

    def test_link_flood_is_rejected(self):
        spam = " ".join(["http://x.example"] * 6)
        res = self.api.post("/api/v1/contact/", {**self.payload, "message": spam},
                            format="json")
        self.assertEqual(res.status_code, 400)

    def test_error_envelope_shape(self):
        body = self.api.post("/api/v1/contact/", {}, format="json").json()
        self.assertEqual(body["error"], "validation_error")
        self.assertIn("fields", body)
        self.assertIn("status", body)


class SubscriberTests(TestCase):
    def setUp(self):
        self.api = APIClient()

    def test_subscribe(self):
        res = self.api.post("/api/v1/subscribe/", {"email": "a@example.com"}, format="json")
        self.assertEqual(res.status_code, 201)
        self.assertEqual(Subscriber.objects.count(), 1)

    def test_duplicate_subscribe_does_not_error(self):
        self.api.post("/api/v1/subscribe/", {"email": "a@example.com"}, format="json")
        res = self.api.post("/api/v1/subscribe/", {"email": "a@example.com"}, format="json")
        self.assertEqual(res.status_code, 201)
        self.assertEqual(Subscriber.objects.count(), 1)

    def test_resubscribe_reactivates(self):
        s = Subscriber.objects.create(email="a@example.com", is_active=False)
        self.api.post("/api/v1/subscribe/", {"email": "a@example.com"}, format="json")
        s.refresh_from_db()
        self.assertTrue(s.is_active)


class LeadRegressionTests(TestCase):
    """Guards for bugs found during the audit."""

    def setUp(self):
        self.api = APIClient()

    def test_subscriber_email_is_case_insensitive(self):
        self.api.post("/api/v1/subscribe/", {"email": "Parsa@Example.COM"}, format="json")
        res = self.api.post("/api/v1/subscribe/", {"email": "parsa@example.com"}, format="json")
        self.assertEqual(res.status_code, 201)
        self.assertEqual(Subscriber.objects.count(), 1)
        self.assertEqual(Subscriber.objects.get().email, "parsa@example.com")

    def test_subscribe_still_validates_email_format(self):
        res = self.api.post("/api/v1/subscribe/", {"email": "not-an-email"}, format="json")
        self.assertEqual(res.status_code, 400)
        self.assertIn("email", res.json()["fields"])

    def test_contact_email_is_normalised(self):
        self.api.post("/api/v1/contact/", {
            "name": "Parsa", "email": "  MiXeD@Example.COM ",
            "message": "a long enough message for validation",
        }, format="json")
        self.assertEqual(ContactMessage.objects.get().email, "mixed@example.com")

    def test_contact_response_is_not_delayed_by_email(self):
        """Notification runs off-thread, so a slow SMTP must not 500 the form."""
        from django.test import override_settings
        with override_settings(
            CONTACT_NOTIFY_EMAILS=["ops@example.com"],
            EMAIL_BACKEND="django.core.mail.backends.locmem.EmailBackend",
        ):
            res = self.api.post("/api/v1/contact/", {
                "name": "Parsa", "email": "p@example.com",
                "message": "a long enough message for validation",
            }, format="json")
        self.assertEqual(res.status_code, 201)

    def test_oversized_body_is_rejected(self):
        from django.test import override_settings
        with override_settings(API_MAX_BODY_BYTES=1024):
            res = self.api.post("/api/v1/contact/", {
                "name": "Parsa", "email": "p@example.com", "message": "x" * 5000,
            }, format="json")
        self.assertEqual(res.status_code, 413)
        self.assertEqual(res.json()["error"], "payload_too_large")
