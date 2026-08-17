"""
Seed the database with the real bilingual copy from home_page/index.html.

Idempotent: run it as many times as you like. Existing rows are updated in
place (matched on slug / natural key) instead of duplicated.

    python manage.py seed_content
    python manage.py seed_content --flush   # wipe content tables first
"""

from django.core.management.base import BaseCommand
from django.db import transaction
from django.utils import timezone

from content.models import (
    FAQ,
    Article,
    ArticleCategory,
    Capability,
    ProcessStep,
    Project,
    ProjectCategory,
    SiteSettings,
    Statistic,
    Technology,
    Testimonial,
)

CAPABILITIES = [
    {
        "slug": "core-tech-engineering",
        "icon": "cpu",
        "title_en": "Core Tech & Engineering",
        "title_fa": "تکنولوژی‌های پایه",
        "description_en": "We build robust architectures using modern languages and frameworks including Python, C++, React, Django, and OpenCV for specialized solutions.",
        "description_fa": "ما معماری‌های قدرتمندی را با استفاده از زبان‌ها و فریم‌ورک‌های مدرن از جمله پایتون، C++، ریکت، جنگو و OpenCV برای راهکارهای تخصصی توسعه می‌دهیم.",
        "link_label_en": "Explore Technologies",
        "link_label_fa": "بررسی تکنولوژی‌ها",
        "is_featured": True,
    },
    {
        "slug": "web-development",
        "icon": "code-2",
        "title_en": "Web Development",
        "title_fa": "توسعه وب",
        "description_en": "High-performance digital experiences engineered for modern businesses. We build scalable frontends and robust backends.",
        "description_fa": "تجربیات دیجیتال با عملکرد بالا، مهندسی شده برای کسب‌وکارهای مدرن. ما فرانت‌اند‌های مقیاس‌پذیر و بک‌اندهای قدرتمند می‌سازیم.",
        "link_label_en": "Explore Web Development",
        "link_label_fa": "بررسی توسعه وب",
    },
    {
        "slug": "matlab-engineering",
        "icon": "function-square",
        "title_en": "MATLAB Engineering",
        "title_fa": "مهندسی متلب (MATLAB)",
        "description_en": "From mathematical modeling to advanced numerical analysis and engineering computation for complex scientific challenges.",
        "description_fa": "از مدل‌سازی ریاضی تا تحلیل عددی پیشرفته و محاسبات مهندسی برای چالش‌های پیچیده علمی.",
        "link_label_en": "Explore MATLAB Projects",
        "link_label_fa": "بررسی پروژه‌های متلب",
        "is_featured": True,
    },
    {
        "slug": "comsol-simulation",
        "icon": "waves",
        "title_en": "COMSOL Simulation",
        "title_fa": "شبیه‌سازی کامسول (COMSOL)",
        "description_en": "Advanced multiphysics simulation for complex engineering problems. We visualize and analyze thermal, structural, and electromagnetic fields.",
        "description_fa": "شبیه‌سازی پیشرفته چندفیزیکی برای مسائل پیچیده مهندسی. ما میدان‌های حرارتی، ساختاری و الکترومغناطیسی را تحلیل می‌کنیم.",
        "link_label_en": "Explore COMSOL Projects",
        "link_label_fa": "بررسی پروژه‌های کامسول",
        "is_featured": True,
    },
    {
        "slug": "ai-agents-automation",
        "icon": "bot",
        "title_en": "AI Agents & Automation",
        "title_fa": "عوامل هوش مصنوعی و اتوماسیون",
        "description_en": "Intelligent agents and automated workflows designed to solve real business problems, connecting tools, knowledge, and decision-making.",
        "description_fa": "عوامل هوشمند و جریان‌های کاری خودکار طراحی شده برای حل مشکلات واقعی کسب‌وکار با اتصال ابزارها و داده‌ها.",
        "link_label_en": "Explore AI Solutions",
        "link_label_fa": "بررسی راهکارهای AI",
        "is_featured": True,
    },
]

PROCESS_STEPS = [
    ("Understand", "شناخت",
     "Deep analysis of the problem, requirements gathering, and establishing the technical feasibility.",
     "تحلیل عمیق مسئله، جمع‌آوری نیازمندی‌ها و بررسی امکان‌سنجی فنی.", "search"),
    ("Design", "طراحی",
     "Architecting the solution. Creating data models, simulation setups, wireframes, and tech stack.",
     "معماری راهکار. ساخت مدل داده، چیدمان شبیه‌سازی، وایرفریم و انتخاب تکنولوژی.", "pen-tool"),
    ("Engineer", "مهندسی",
     "The core development phase. Writing clean code, building models, and integrating systems.",
     "فاز اصلی توسعه. نوشتن کد تمیز، ساخت مدل‌ها و یکپارچه‌سازی سیستم‌ها.", "hammer"),
    ("Validate", "اعتبارسنجی",
     "Rigorous testing, simulation verification, and optimization to ensure engineering standards.",
     "تست دقیق، راستی‌آزمایی شبیه‌سازی و بهینه‌سازی برای رعایت استانداردهای مهندسی.", "shield-check"),
    ("Deliver", "تحویل",
     "Deploying the final product, handing over documentation, and providing ongoing support.",
     "استقرار محصول نهایی، تحویل مستندات و پشتیبانی مستمر.", "rocket"),
]

FAQS = [
    ("What type of projects does PhysioElectric accept?",
     "فیزیوالکتریک چه نوع پروژه‌هایی می‌پذیرد؟",
     "We specialize in complex web applications, scientific computing using MATLAB/COMSOL, AI integration, and custom engineering software solutions.",
     "ما در اپلیکیشن‌های وب پیچیده، محاسبات علمی با متلب و کامسول، یکپارچه‌سازی هوش مصنوعی و نرم‌افزارهای مهندسی سفارشی تخصص داریم."),
    ("Do you work with international clients?",
     "با مشتریان بین‌المللی کار می‌کنید؟",
     "Yes, we operate globally and provide services to businesses and engineering firms worldwide.",
     "بله، ما در سطح جهانی فعالیت می‌کنیم و به کسب‌وکارها و شرکت‌های مهندسی در سراسر دنیا خدمات می‌دهیم."),
    ("Can you build AI agents and automation workflows?",
     "می‌توانید عامل هوش مصنوعی و اتوماسیون بسازید؟",
     "Absolutely. We design AI agents that can automate tasks, analyze data, and integrate seamlessly with your existing software ecosystem.",
     "قطعاً. ما عامل‌هایی طراحی می‌کنیم که وظایف را خودکار کنند، داده تحلیل کنند و به‌راحتی با اکوسیستم نرم‌افزاری فعلی شما یکپارچه شوند."),
    ("Can you develop custom software for a specific engineering problem?",
     "برای یک مسئله مهندسی خاص نرم‌افزار اختصاصی می‌سازید؟",
     "Yes, we specialize in bridging the gap between complex engineering requirements and modern software development.",
     "بله، تخصص ما دقیقاً پر کردن فاصله میان نیازهای پیچیده مهندسی و توسعه نرم‌افزار مدرن است."),
    ("How does the project process work and how can I start?",
     "فرایند پروژه چگونه است و چطور شروع کنم؟",
     "Our process starts with understanding your requirements, followed by design, engineering, validation, and delivery. Reach out via our contact section to schedule an initial consultation.",
     "فرایند ما با شناخت نیازهای شما آغاز می‌شود و با طراحی، مهندسی، اعتبارسنجی و تحویل ادامه می‌یابد. از بخش تماس برای هماهنگی جلسه مشاوره اولیه اقدام کنید."),
]

ARTICLES = [
    ("The Future of Agentic Workflows in Engineering",
     "آینده جریان‌های کاری مبتنی بر عامل در مهندسی",
     "AI Research", "پژوهش هوش مصنوعی", "ai-research", 2026, 8, 12),
    ("Optimizing Thermal Dynamics using COMSOL",
     "بهینه‌سازی دینامیک حرارتی با کامسول",
     "Simulation", "شبیه‌سازی", "simulation", 2026, 7, 28),
    ("Building Scalable Microservices for High-Load Systems",
     "ساخت میکروسرویس‌های مقیاس‌پذیر برای سیستم‌های پربار",
     "System Architecture", "معماری سیستم", "system-architecture", 2026, 7, 15),
]

TECHNOLOGIES = [
    ("Python", "python", "#3776ab"), ("C++", "cpp", "#00599c"),
    ("React", "react", "#61dafb"), ("Django", "django", "#092e20"),
    ("OpenCV", "opencv", "#5c3ee8"), ("MATLAB", "matlab", "#e16737"),
    ("COMSOL", "comsol", "#0072c6"), ("ESP32", "esp32", "#e7352c"),
    ("STM32", "stm32", "#03234b"), ("PostgreSQL", "postgresql", "#4169e1"),
    ("Node.js", "nodejs", "#5fa04e"), ("WebSocket", "websocket", "#0ea5e9"),
]

PROJECTS = [
    {
        "slug": "iot-simulation-lab",
        "title_en": "IoT Simulation Laboratory",
        "title_fa": "آزمایشگاه شبیه‌سازی اینترنت اشیا",
        "summary_en": "Interactive ESP32 and STM32 boards with live WebSocket telemetry, virtual sensors and a thermal model reacting to heat sources.",
        "summary_fa": "بردهای تعاملی ESP32 و STM32 با تلمتری زنده روی وب‌سوکت، سنسورهای مجازی و مدل حرارتی که به منابع گرما واکنش نشان می‌دهد.",
        "category": "embedded", "status": "completed", "year": 2026,
        "techs": ["esp32", "stm32", "nodejs", "websocket"],
        "accent_color": "#7ef0dc", "is_featured": True,
    },
    {
        "slug": "matlab-numerical-suite",
        "title_en": "MATLAB Numerical Suite",
        "title_fa": "مجموعه محاسبات عددی متلب",
        "summary_en": "Lorenz and Van der Pol attractors, PID tuning, FFT, Fourier series and Bode analysis solved on a Node core.",
        "summary_fa": "جاذب‌های لورنز و ون‌درپل، تنظیم PID، تبدیل فوریه سریع، سری فوریه و تحلیل بود، حل‌شده روی هسته Node.",
        "category": "scientific-computing", "status": "completed", "year": 2026,
        "techs": ["matlab", "nodejs", "python"],
        "accent_color": "#0ea5e9", "is_featured": True,
    },
    {
        "slug": "comsol-multiphysics-solver",
        "title_en": "COMSOL Multiphysics Solver",
        "title_fa": "حل‌گر چندفیزیکی کامسول",
        "summary_en": "Browser-based heat transfer, electrostatics and wave equation solvers on a finite-difference grid.",
        "summary_fa": "حل‌گرهای انتقال حرارت، الکترواستاتیک و معادله موج روی شبکه تفاضل محدود، مستقیم در مرورگر.",
        "category": "simulation", "status": "completed", "year": 2026,
        "techs": ["comsol", "cpp", "nodejs"],
        "accent_color": "#a855f7", "is_featured": True,
    },
    {
        "slug": "agentic-automation-platform",
        "title_en": "Agentic Automation Platform",
        "title_fa": "پلتفرم اتوماسیون مبتنی بر عامل",
        "summary_en": "Tool-using AI agents that connect internal knowledge bases to day-to-day engineering decisions.",
        "summary_fa": "عامل‌های هوش مصنوعی ابزارمحور که پایگاه دانش داخلی را به تصمیم‌های روزمره مهندسی وصل می‌کنند.",
        "category": "ai", "status": "ongoing", "year": 2026,
        "techs": ["python", "django", "postgresql"],
        "accent_color": "#f59e0b", "is_featured": True,
    },
    {
        "slug": "computer-vision-inspection",
        "title_en": "Computer Vision Inspection",
        "title_fa": "بازرسی بصری با بینایی ماشین",
        "summary_en": "Real-time defect detection on a production line using OpenCV and a custom classifier.",
        "summary_fa": "تشخیص لحظه‌ای عیب روی خط تولید با OpenCV و یک دسته‌بند اختصاصی.",
        "category": "ai", "status": "research", "year": 2025,
        "techs": ["python", "opencv", "cpp"],
        "accent_color": "#22c55e",
    },
    {
        "slug": "physio-electric-site",
        "title_en": "PhysioElectric Web Platform",
        "title_fa": "پلتفرم وب فیزیوالکتریک",
        "summary_en": "Bilingual marketing site with a Django content backend and an embedded simulation lab.",
        "summary_fa": "سایت دوزبانه با بک‌اند محتوای جنگو و آزمایشگاه شبیه‌سازی توکار.",
        "category": "web", "status": "ongoing", "year": 2026,
        "techs": ["django", "python", "react", "postgresql"],
        "accent_color": "#0ea5e9",
    },
]

PROJECT_CATEGORIES = [
    ("web", "Web", "وب"), ("ai", "AI & Automation", "هوش مصنوعی"),
    ("simulation", "Simulation", "شبیه‌سازی"),
    ("scientific-computing", "Scientific Computing", "محاسبات علمی"),
    ("embedded", "Embedded", "سیستم‌های نهفته"),
]

STATISTICS = [
    ("+50", "Projects Delivered", "پروژه تحویل‌شده", "package-check"),
    ("+30", "Happy Clients", "مشتری راضی", "smile"),
    ("8", "Years of Experience", "سال تجربه", "calendar"),
    ("99%", "On-time Delivery", "تحویل به‌موقع", "clock"),
]


class Command(BaseCommand):
    help = "Populate the database with the site's real bilingual content."

    def add_arguments(self, parser):
        parser.add_argument(
            "--flush",
            action="store_true",
            help="Delete existing content rows before seeding.",
        )

    @transaction.atomic
    def handle(self, *args, **options):
        if options["flush"]:
            self.stdout.write(self.style.WARNING("پاک‌سازی جداول محتوا..."))
            for model in (Project, Article, Capability, ProcessStep, FAQ,
                          Statistic, Testimonial, ProjectCategory,
                          ArticleCategory, Technology):
                model.objects.all().delete()

        self._site_settings()
        self._technologies()
        self._capabilities()
        self._project_categories()
        self._projects()
        self._process()
        self._articles()
        self._faqs()
        self._statistics()

        self.stdout.write(self.style.SUCCESS("\n✓ محتوا با موفقیت وارد دیتابیس شد."))
        self.stdout.write(
            f"  توانمندی {Capability.objects.count()} · "
            f"پروژه {Project.objects.count()} · "
            f"مقاله {Article.objects.count()} · "
            f"سوال {FAQ.objects.count()} · "
            f"مرحله {ProcessStep.objects.count()} · "
            f"تکنولوژی {Technology.objects.count()}"
        )

    # -- individual seeders -------------------------------------------------

    def _site_settings(self):
        s = SiteSettings.load()
        s.brand_name = "PhysioElectric"
        s.tagline_en = "Engineering Ideas. Building Intelligent Solutions."
        s.tagline_fa = "مهندسی ایده‌ها، خلق راهکارهای هوشمند."
        s.hero_badge_en = "Technology & Engineering Studio"
        s.hero_badge_fa = "استودیو مهندسی و تکنولوژی"
        s.hero_title_en = "Engineering Ideas. Building Intelligent Solutions."
        s.hero_title_fa = "مهندسی ایده‌ها. خلق راهکارهای هوشمند."
        s.hero_subtitle_en = (
            "PhysioElectric combines software engineering, advanced simulations, "
            "artificial intelligence, and digital technologies to transform complex "
            "ideas into practical solutions."
        )
        s.hero_subtitle_fa = (
            "فیزیوالکتریک با ترکیب مهندسی نرم‌افزار، شبیه‌سازی‌های پیشرفته، هوش مصنوعی "
            "و تکنولوژی‌های دیجیتال، ایده‌های پیچیده را به راهکارهای عملی تبدیل می‌کند."
        )
        s.email = "hello@physioelectric.dev"
        s.github_url = "https://github.com/sympathiccore"
        s.meta_description_en = (
            "PhysioElectric — software engineering, MATLAB/COMSOL simulation and AI "
            "automation for complex technical challenges."
        )
        s.meta_description_fa = (
            "فیزیوالکتریک — مهندسی نرم‌افزار، شبیه‌سازی متلب و کامسول و اتوماسیون هوش "
            "مصنوعی برای چالش‌های فنی پیچیده."
        )
        s.save()
        self.stdout.write("  ✓ تنظیمات سایت")

    def _technologies(self):
        for name, slug, color in TECHNOLOGIES:
            Technology.objects.update_or_create(
                slug=slug, defaults={"name": name, "color": color}
            )
        self.stdout.write(f"  ✓ {len(TECHNOLOGIES)} تکنولوژی")

    def _capabilities(self):
        # Copy before mutating: the module-level list must survive repeated
        # runs inside the same process (the test suite seeds more than once).
        for i, item in enumerate(CAPABILITIES):
            data = {k: v for k, v in item.items() if k != "slug"}
            data["order"] = i
            data["is_published"] = True
            Capability.objects.update_or_create(slug=item["slug"], defaults=data)
        self.stdout.write(f"  ✓ {len(CAPABILITIES)} توانمندی")

    def _project_categories(self):
        for i, (slug, en, fa) in enumerate(PROJECT_CATEGORIES):
            ProjectCategory.objects.update_or_create(
                slug=slug, defaults={"title_en": en, "title_fa": fa, "order": i}
            )

    def _projects(self):
        for i, item in enumerate(PROJECTS):
            techs = item.get("techs", [])
            category = ProjectCategory.objects.filter(slug=item["category"]).first()
            project, _ = Project.objects.update_or_create(
                slug=item["slug"],
                defaults={
                    "title_en": item["title_en"],
                    "title_fa": item["title_fa"],
                    "summary_en": item["summary_en"],
                    "summary_fa": item["summary_fa"],
                    "description_en": item["summary_en"],
                    "description_fa": item["summary_fa"],
                    "category": category,
                    "status": item["status"],
                    "year": item["year"],
                    "accent_color": item.get("accent_color", "#0ea5e9"),
                    "is_featured": item.get("is_featured", False),
                    "order": i,
                    "is_published": True,
                },
            )
            project.technologies.set(Technology.objects.filter(slug__in=techs))
        self.stdout.write(f"  ✓ {len(PROJECTS)} پروژه")

    def _process(self):
        for i, (en, fa, den, dfa, icon) in enumerate(PROCESS_STEPS, start=1):
            ProcessStep.objects.update_or_create(
                number=i,
                defaults={
                    "title_en": en, "title_fa": fa,
                    "description_en": den, "description_fa": dfa,
                    "icon": icon, "order": i, "is_published": True,
                },
            )
        self.stdout.write(f"  ✓ {len(PROCESS_STEPS)} مرحله فرایند")

    def _articles(self):
        for i, (t_en, t_fa, c_en, c_fa, c_slug, y, m, d) in enumerate(ARTICLES):
            category, _ = ArticleCategory.objects.update_or_create(
                slug=c_slug, defaults={"title_en": c_en, "title_fa": c_fa}
            )
            slug = f"article-{c_slug}-{i + 1}"
            Article.objects.update_or_create(
                slug=slug,
                defaults={
                    "title_en": t_en, "title_fa": t_fa,
                    "excerpt_en": f"{t_en} — an engineering deep dive.",
                    "excerpt_fa": f"{t_fa} — یک بررسی عمیق مهندسی.",
                    "category": category,
                    "reading_minutes": 6 + i,
                    "order": i,
                    "is_published": True,
                    "is_featured": i == 0,
                    "published_at": timezone.datetime(y, m, d, tzinfo=timezone.get_current_timezone()),
                },
            )
        self.stdout.write(f"  ✓ {len(ARTICLES)} مقاله")

    def _faqs(self):
        for i, (q_en, q_fa, a_en, a_fa) in enumerate(FAQS):
            FAQ.objects.update_or_create(
                title_en=q_en,
                defaults={
                    "title_fa": q_fa,
                    "description_en": a_en,
                    "description_fa": a_fa,
                    "order": i,
                    "is_published": True,
                },
            )
        self.stdout.write(f"  ✓ {len(FAQS)} سوال متداول")

    def _statistics(self):
        for i, (value, en, fa, icon) in enumerate(STATISTICS):
            Statistic.objects.update_or_create(
                title_en=en,
                defaults={
                    "value": value, "title_fa": fa, "icon": icon,
                    "order": i, "is_published": True,
                },
            )
        self.stdout.write(f"  ✓ {len(STATISTICS)} آمار")
