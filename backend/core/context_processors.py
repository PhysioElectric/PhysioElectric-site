"""Template context available on every page."""

from django.urls import reverse


def site_settings(request):
    from content.models import SiteSettings

    lang = request.GET.get("lang", "fa")
    lang = lang if lang in {"fa", "en"} else "fa"
    is_rtl = lang == "fa"
    home = reverse("content:home")

    labels = {
        "fa": ["توانمندی‌ها", "پروژه‌ها", "مطالب", "سوالات", "تماس"],
        "en": ["Capabilities", "Projects", "Insights", "FAQ", "Contact"],
    }[lang]
    targets = [
        f"{home}?lang={lang}#capabilities",
        f"{home}?lang={lang}#projects",
        f"{home}?lang={lang}#articles",
        f"{home}?lang={lang}#faq",
        f"{home}?lang={lang}#contact",
    ]

    return {
        "site": SiteSettings.load(),
        "lang": lang,
        "is_rtl": is_rtl,
        "nav_items": [{"label": l, "href": h} for l, h in zip(labels, targets)],
    }
