
        // Initialize Icons
        lucide.createIcons();

        /* --- 1. Bilingual System (EN/FA) --- */
        const dictionary = {
            en: {
                'nav.home': 'Home', 'nav.capabilities': 'Capabilities', 'nav.projects': 'Projects', 'nav.about': 'About', 'nav.faq': 'FAQ', 'nav.cta': 'Start a Project',
                'hero.badge': 'Technology & Engineering Studio',
                'hero.title': 'Engineering Ideas.<br><span class="text-gradient">Building Intelligent Solutions.</span>',
                'hero.subtitle': 'PhysioElectric combines software engineering, advanced simulations, artificial intelligence, and digital technologies to transform complex ideas into practical solutions.',
                'hero.ctaPrimary': 'Explore Capabilities', 'hero.ctaSecondary': 'View Projects', 'hero.scroll': 'Scroll',
                'cap.title': 'What We Build', 'cap.subtitle': 'From intelligent software to advanced engineering simulations, we turn complex technical challenges into engineered solutions.',
                'cap.c0.title': 'Core Tech & Engineering', 'cap.c0.desc': 'We build robust architectures using modern languages and frameworks including Python, C++, React, Django, and OpenCV for specialized solutions.', 'cap.c0.link': 'Explore Technologies <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>',
                'cap.c1.title': 'Web Development', 'cap.c1.desc': 'High-performance digital experiences engineered for modern businesses. We build scalable frontends and robust backends.', 'cap.c1.link': 'Explore Web Development <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1"></i>',
                'cap.c2.title': 'MATLAB Engineering', 'cap.c2.desc': 'From mathematical modeling to advanced numerical analysis and engineering computation for complex scientific challenges.', 'cap.c2.link': 'Explore MATLAB Projects <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>',
                'cap.c3.title': 'COMSOL Simulation', 'cap.c3.desc': 'Advanced multiphysics simulation for complex engineering problems. We visualize and analyze thermal, structural, and electromagnetic fields.', 'cap.c3.link': 'Explore COMSOL Projects <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:rotate-180"></i>',
                'cap.c4.title': 'AI Agents & Automation', 'cap.c4.desc': 'Intelligent agents and automated workflows designed to solve real business problems, connecting tools, knowledge, and decision-making.', 'cap.c4.link': 'Explore AI Solutions <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:rotate-180"></i>',
                'cap.c5.title': 'Embedded Systems & IoT', 'cap.c5.desc': 'Designing and programming embedded hardware using STM32 and ESP32. We create smart connected devices, sensor networks, and IoT solutions for real-time control and automation.', 'cap.c5.link': 'Explore IoT Solutions <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:rotate-180"></i>',
                'proj.title': 'Selected Projects', 'proj.subtitle': 'Explore some of the engineering, software and AI solutions we have developed.',
                'about.title': 'Engineering Technology <br/><span class="text-slate-400">With Purpose.</span>',
                'about.title_part1': 'Engineering Technology',
                'about.title_part2': 'With Purpose.',
                'about.desc1': 'PhysioElectric operates at the exact intersection where advanced engineering meets modern software development. Founded on the principle that true innovation requires both deep domain expertise and cutting-edge technology, our team consists of multidisciplinary experts.',
                'about.desc2': 'We don\'t just write code; we build robust systems. Whether it\'s a high-performance web application, a complex multiphysics simulation, or an intelligent automation agent, our approach is always rooted in precision and scientific methodology. We partner with forward-thinking enterprises to solve their most challenging bottlenecks, transforming theoretical models into deployable, scalable real-world solutions.',
                'about.btn': 'More About Our Company',
                
                'process.title': 'From Idea to Solution',
                'process.s1.title': 'Understand', 'process.s1.desc': 'Deep analysis of the problem, requirements gathering, and establishing the technical feasibility.',
                'process.s2.title': 'Design', 'process.s2.desc': 'Architecting the solution. Creating data models, simulation setups, wireframes, and tech stack.',
                'process.s3.title': 'Engineer', 'process.s3.desc': 'The core development phase. Writing clean code, building models, and integrating systems.',
                'process.s4.title': 'Validate', 'process.s4.desc': 'Rigorous testing, simulation verification, and optimization to ensure engineering standards.',
                'process.s5.title': 'Deliver', 'process.s5.desc': 'Deploying the final product, handing over documentation, and providing ongoing support.',
                'blog.title': 'Latest Insights', 'blog.subtitle': 'Our thoughts on software, advanced engineering, and artificial intelligence.', 'blog.viewAll': 'View All Articles', 'blog.read': 'Read Article',
                'blog.c1': 'AI Research', 'blog.t1': 'The Future of Agentic Workflows in Engineering', 'blog.d1': 'Aug 12, 2026',
                'blog.c2': 'Simulation', 'blog.t2': 'Optimizing Thermal Dynamics using COMSOL', 'blog.d2': 'Jul 28, 2026',
                'blog.c3': 'System Architecture', 'blog.t3': 'Building Scalable Microservices for High-Load Systems', 'blog.d3': 'Jul 15, 2026',
                'faq.title': 'Frequently Asked Questions',
                'faq.q1': 'What type of projects does PhysioElectric accept?', 'faq.a1': 'We specialize in complex web applications, scientific computing using MATLAB/COMSOL, AI integration, and custom engineering software solutions.',
                'faq.q2': 'Do you work with international clients?', 'faq.a2': 'Yes, we operate globally and provide services to businesses and engineering firms worldwide.',
                'faq.q3': 'Can you build AI agents and automation workflows?', 'faq.a3': 'Absolutely. We design AI agents that can automate tasks, analyze data, and integrate seamlessly with your existing software ecosystem.',
                'faq.q4': 'Can you develop custom software for a specific engineering problem?', 'faq.a4': 'Yes, we specialize in bridging the gap between complex engineering requirements and modern software development. We build custom solutions tailored to specialized scientific needs.',
                'faq.q5': 'How does the project process work and how can I start?', 'faq.a5': 'Our process starts with understanding your requirements, followed by design, engineering, validation, and delivery. Reach out via our contact section to schedule an initial consultation.',
                'cta.title': 'Have a Complex Idea? <br/>Let\'s Engineer It.', 'cta.desc': 'Tell us what you\'re building. We\'ll help turn the idea into a practical technical solution.',
                'cta.btnPrimary': 'Start a Project', 'cta.btnSecondary': 'Contact Us',
                'footer.desc': 'Engineering Technology. Building Intelligent Solutions for modern businesses and scientific challenges.',
                'footer.nav': 'Navigation', 'footer.serv': 'Services', 'footer.lang': 'Language',
                'banner.title': 'Precision at Scale', 'banner.subtitle': 'Redefining the boundaries of engineering and technology.'
            },
            fa: {
                'nav.home': 'خانه', 'nav.capabilities': 'توانمندی‌ها', 'nav.projects': 'پروژه‌ها', 'nav.about': 'درباره ما', 'nav.faq': 'سوالات متداول', 'nav.cta': 'شروع پروژه',
                'hero.badge': 'استودیو مهندسی و تکنولوژی',
                'hero.title': 'مهندسی ایده‌ها.<br><span class="text-gradient">خلق راهکارهای هوشمند.</span>',
                'hero.subtitle': 'فیزیوالکتریک با ترکیب مهندسی نرم‌افزار، شبیه‌سازی‌های پیشرفته، هوش مصنوعی و تکنولوژی‌های دیجیتال، ایده‌های پیچیده را به راهکارهای عملی تبدیل می‌کند.',
                'hero.ctaPrimary': 'مشاهده توانمندی‌ها', 'hero.ctaSecondary': 'بررسی پروژه‌ها', 'hero.scroll': 'اسکرول',
                'cap.title': 'آنچه ما می‌سازیم', 'cap.subtitle': 'از نرم‌افزارهای هوشمند تا شبیه‌سازی‌های پیشرفته مهندسی، ما چالش‌های فنی پیچیده را به راهکارهای مهندسی‌شده تبدیل می‌کنیم.',
                'cap.c0.title': 'تکنولوژی‌های پایه', 'cap.c0.desc': 'ما معماری‌های قدرتمندی را با استفاده از زبان‌ها و فریم‌ورک‌های مدرن از جمله پایتون، C++، ریکت، جنگو و OpenCV برای راهکارهای تخصصی توسعه می‌دهیم.', 'cap.c0.link': '<i data-lucide="arrow-left" class="w-4 h-4 mx-1 transition-transform group-hover:-translate-x-1"></i> بررسی تکنولوژی‌ها',
                'cap.c1.title': 'توسعه وب', 'cap.c1.desc': 'تجربیات دیجیتال با عملکرد بالا، مهندسی شده برای کسب‌وکارهای مدرن. ما فرانت‌اند‌های مقیاس‌پذیر و بک‌اندهای قدرتمند می‌سازیم.', 'cap.c1.link': '<i data-lucide="arrow-left" class="w-4 h-4 mx-1 transition-transform group-hover:-translate-x-1"></i> بررسی توسعه وب',
                'cap.c2.title': 'مهندسی متلب (MATLAB)', 'cap.c2.desc': 'از مدل‌سازی ریاضی تا تحلیل عددی پیشرفته و محاسبات مهندسی برای چالش‌های پیچیده علمی.', 'cap.c2.link': '<i data-lucide="arrow-left" class="w-4 h-4 mx-1 transition-transform group-hover:-translate-x-1"></i> بررسی پروژه‌های متلب',
                'cap.c3.title': 'شبیه‌سازی کامسول (COMSOL)', 'cap.c3.desc': 'شبیه‌سازی پیشرفته چندفیزیکی برای مسائل پیچیده مهندسی. ما میدان‌های حرارتی، ساختاری و الکترومغناطیسی را تحلیل می‌کنیم.', 'cap.c3.link': '<i data-lucide="arrow-left" class="w-4 h-4 mx-1 transition-transform group-hover:-translate-x-1"></i> بررسی پروژه‌های کامسول',
                'cap.c4.title': 'عوامل هوش مصنوعی و اتوماسیون', 'cap.c4.desc': 'عوامل هوشمند و جریان‌های کاری خودکار طراحی شده برای حل مشکلات واقعی کسب‌وکار با اتصال ابزارها و داده‌ها.', 'cap.c4.link': '<i data-lucide="arrow-left" class="w-4 h-4 mx-1 transition-transform group-hover:-translate-x-1"></i> بررسی راهکارهای AI',
                'cap.c5.title': 'سیستم‌های نهفته و اینترنت اشیا (IoT)', 'cap.c5.desc': 'طراحی و برنامه‌نویسی بردهای Embedded با استفاده از میکروکنترلرهای STM32 و ماژول‌های ESP32. ما تجهیزات هوشمند، شبکه‌های حسگر و سیستم‌های کنترل بلادرنگ را برای پروژه‌های هوشمندسازی توسعه می‌دهیم.', 'cap.c5.link': '<i data-lucide="arrow-left" class="w-4 h-4 mx-1 transition-transform group-hover:-translate-x-1"></i> بررسی سیستم‌های هوشمند',
                'proj.title': 'پروژه‌های منتخب', 'proj.subtitle': 'برخی از راهکارهای مهندسی، نرم‌افزاری و هوش مصنوعی که توسعه داده‌ایم را بررسی کنید.',
                'about.title': 'تکنولوژی مهندسی <br/><span class="text-slate-400">با هدف مشخص.</span>',
                'about.title_part1': 'تکنولوژی مهندسی',
                'about.title_part2': 'با هدف مشخص.',
                'about.desc1': 'فیزیوالکتریک دقیقاً در نقطه تلاقی مهندسی پیشرفته و توسعه نرم‌افزار مدرن فعالیت می‌کند. تیم ما متشکل از متخصصان چندرشته‌ای است و بر این اصل بنا شده که نوآوری واقعی نیازمند تخصص عمیق در دامنه دانش و فناوری‌های روز دنیاست.',
                'about.desc2': 'ما فقط کد نمی‌نویسیم؛ بلکه سیستم‌های پایدار و مقاوم می‌سازیم. چه یک برنامه وب با کارایی بالا، چه یک شبیه‌سازی پیچیده چندفیزیکی، یا یک عامل اتوماسیون هوشمند، رویکرد ما همیشه ریشه در دقت و روش‌شناسی علمی دارد. ما در کنار شرکت‌های پیشرو قرار می‌گیریم تا پیچیده‌ترین چالش‌هایشان را حل کرده و مدل‌های تئوری را به راهکارهای عملیاتی و مقیاس‌پذیر در دنیای واقعی تبدیل کنیم.',
                'about.btn': 'اطلاعات بیشتر درباره مجموعه',
                
                'process.title': 'از ایده تا راهکار',
                'process.s1.title': 'درک مسئله', 'process.s1.desc': 'تحلیل عمیق مشکل، جمع‌آوری نیازمندی‌ها و بررسی امکان‌سنجی فنی پروژه.',
                'process.s2.title': 'طراحی', 'process.s2.desc': 'معماری راهکار. ایجاد مدل‌های داده، تنظیمات شبیه‌سازی، وایرفریم‌ها و تعیین پشته فناوری (Tech Stack).',
                'process.s3.title': 'توسعه و مهندسی', 'process.s3.desc': 'فاز اصلی توسعه. نوشتن کدهای تمیز، ساخت مدل‌ها و ادغام عوامل هوش مصنوعی.',
                'process.s4.title': 'اعتبارسنجی', 'process.s4.desc': 'تست دقیق، تأیید شبیه‌سازی و بهینه‌سازی برای اطمینان از رعایت استانداردهای مهندسی.',
                'process.s5.title': 'تحویل', 'process.s5.desc': 'استقرار محصول نهایی در محیط‌های عملیاتی، تحویل مستندات و ارائه پشتیبانی.',
                'blog.title': 'تازه‌های تکنولوژی و مهندسی', 'blog.subtitle': 'دیدگاه‌ها و مقالات ما درباره مهندسی نرم‌افزار، شبیه‌سازی پیشرفته و هوش مصنوعی.', 'blog.viewAll': 'مشاهده همه مقالات', 'blog.read': 'مطالعه مقاله',
                'blog.c1': 'تحقیقات AI', 'blog.t1': 'آینده جریان‌های کاری عامل‌محور در مهندسی', 'blog.d1': '۲۱ مرداد ۱۴۰۵',
                'blog.c2': 'شبیه‌سازی', 'blog.t2': 'بهینه‌سازی دینامیک حرارتی با استفاده از نرم‌افزار COMSOL', 'blog.d2': '۶ مرداد ۱۴۰۵',
                'blog.c3': 'معماری سیستم', 'blog.t3': 'ساخت میکروسرویس‌های مقیاس‌پذیر برای سیستم‌های پرفشار', 'blog.d3': '۲۴ تیر ۱۴۰۵',
                'faq.title': 'سوالات متداول',
                'faq.q1': 'فیزیوالکتریک چه نوع پروژه‌هایی را می‌پذیرد؟', 'faq.a1': 'تخصص ما در برنامه‌های کاربردی وب پیچیده، محاسبات علمی با استفاده از MATLAB/COMSOL، ادغام هوش مصنوعی و راهکارهای نرم‌افزاری مهندسی سفارشی است.',
                'faq.q2': 'آیا با مشتریان بین‌المللی کار می‌کنید؟', 'faq.a2': 'بله، ما به صورت جهانی فعالیت می‌کنیم و به کسب‌وکارها و شرکت‌های مهندسی در سراسر جهان خدمات ارائه می‌دهیم.',
                'faq.q3': 'آیا می‌توانید عوامل هوش مصنوعی (AI Agents) بسازید؟', 'faq.a3': 'قطعاً. ما عوامل هوشمندی طراحی می‌کنیم که می‌توانند وظایف را خودکار کنند، داده‌ها را تجزیه و تحلیل کنند و به طور یکپارچه با سیستم‌های فعلی شما ادغام شوند.',
                'faq.q4': 'آیا می‌توانید نرم‌افزار سفارشی برای یک مشکل مهندسی خاص توسعه دهید؟', 'faq.a4': 'بله، تخصص ما پر کردن شکاف بین نیازمندی‌های پیچیده مهندسی و توسعه نرم‌افزار مدرن است. ما راهکارهای اختصاصی متناسب با نیازهای تخصصی علمی و مهندسی می‌سازیم.',
                'faq.q5': 'روند انجام پروژه چگونه است و چطور می‌توانم شروع کنم؟', 'faq.a5': 'روند کار ما با درک نیازهای شما آغاز می‌شود و با طراحی، مهندسی، اعتبارسنجی و تحویل ادامه می‌یابد. از طریق بخش تماس با ما در ارتباط باشید تا جلسه مشاوره اولیه را تنظیم کنیم.',
                'cta.title': 'ایده پیچیده‌ای دارید؟ <br/>بیایید آن را مهندسی کنیم.', 'cta.desc': 'به ما بگویید چه چیزی می‌سازید. ما کمک می‌کنیم ایده را به یک راهکار فنی عملی تبدیل کنید.',
                'cta.btnPrimary': 'شروع پروژه', 'cta.btnSecondary': 'تماس با ما',
                'footer.desc': 'تکنولوژی مهندسی. خلق راهکارهای هوشمند برای کسب‌وکارهای مدرن و چالش‌های علمی.',
                'footer.nav': 'دسترسی سریع', 'footer.serv': 'خدمات', 'footer.lang': 'زبان',
                'banner.title': 'دقت در مقیاس کلان', 'banner.subtitle': 'بازتعریف مرزهای مهندسی و تکنولوژی‌های پیشرفته دیجیتال.'
            }
        };

        // تغییر متغیر پیش‌فرض به فارسی
        let currentLang = 'fa';

        function setLanguage(lang) {
            currentLang = lang;
            const htmlTag = document.documentElement;
            const langToggleTxt = document.getElementById('currentLang');
            
            if (lang === 'fa') {
                htmlTag.setAttribute('dir', 'rtl');
                htmlTag.setAttribute('lang', 'fa');
                htmlTag.classList.remove('font-sans');
                htmlTag.style.fontFamily = "'Vazirmatn', sans-serif";
                langToggleTxt.innerText = 'EN';
            } else {
                htmlTag.setAttribute('dir', 'ltr');
                htmlTag.setAttribute('lang', 'en');
                htmlTag.classList.add('font-sans');
                htmlTag.style.fontFamily = "'Inter', sans-serif";
                langToggleTxt.innerText = 'FA';
            }

            // Update texts
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dictionary[lang][key]) {
                    el.innerHTML = dictionary[lang][key];
                }
            });

            // Re-render lucide icons if arrows changed
            lucide.createIcons();
        }

        // اعمال زبان فارسی بلافاصله پس از لود صفحه
        document.addEventListener("DOMContentLoaded", () => {
            setLanguage('fa');
        });

        document.getElementById('langToggle').addEventListener('click', () => {
            setLanguage(currentLang === 'en' ? 'fa' : 'en');
        });

        /* --- 2. Scroll Animations (Intersection Observer) --- */
        const revealElements = document.querySelectorAll('.reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.1 });

        revealElements.forEach(el => revealObserver.observe(el));

        /* --- 3. Process Timeline Scroll Logic --- */
        const timelineObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    const dot = entry.target.querySelector('.step-dot');
                    if(dot) dot.classList.replace('border-slate-200', 'border-physio-500');
                } else {
                     const dot = entry.target.querySelector('.step-dot');
                    if(dot && entry.boundingClientRect.y > 0) { // Only reset if scrolling up
                        dot.classList.replace('border-physio-500', 'border-slate-200');
                    }
                }
            });
        }, { threshold: 0.5, rootMargin: "-10% 0px -40% 0px" });

        document.querySelectorAll('.process-step').forEach(step => timelineObserver.observe(step));
        
        window.addEventListener('scroll', () => {
            // Timeline progress bar
            const processSection = document.getElementById('process');
            const progressLine = document.getElementById('timelineProgress');
            if(processSection && progressLine) {
                const rect = processSection.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                // Calculate how much of the section is scrolled
                if(rect.top < viewportHeight / 2 && rect.bottom > 0) {
                    let progress = ((viewportHeight / 2 - rect.top) / rect.height) * 100;
                    progress = Math.max(0, Math.min(100, progress));
                    progressLine.style.height = `${progress}%`;
                }
            }

            // Navbar Blur effect
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) {
                nav.classList.add('py-2', 'shadow-sm');
                nav.classList.remove('py-4');
            } else {
                nav.classList.remove('py-2', 'shadow-sm');
                nav.classList.add('py-4');
            }
        });

        /* --- 4. Interactive Hero Canvas (Engineering Visualization) --- */
        const canvas = document.getElementById('hero-canvas');
        const ctx = canvas.getContext('2d');
        let particlesArray;

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        let mouse = { x: null, y: null, radius: 150 };
        window.addEventListener('mousemove', (event) => {
            mouse.x = event.x;
            mouse.y = event.y;
        });
        window.addEventListener('mouseout', () => { mouse.x = undefined; mouse.y = undefined; });

        class Particle {
            constructor(x, y, directionX, directionY, size, color) {
                this.x = x; this.y = y;
                this.directionX = directionX; this.directionY = directionY;
                this.size = size; this.color = color;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2, false);
                ctx.fillStyle = this.color;
                ctx.fill();
            }
            update() {
                if (this.x > canvas.width || this.x < 0) this.directionX = -this.directionX;
                if (this.y > canvas.height || this.y < 0) this.directionY = -this.directionY;

                // Collision detection with mouse
                if(mouse.x != null) {
                    let dx = mouse.x - this.x;
                    let dy = mouse.y - this.y;
                    let distance = Math.sqrt(dx*dx + dy*dy);
                    if (distance < mouse.radius + this.size) {
                        if (mouse.x < this.x && this.x < canvas.width - this.size * 10) this.x += 1;
                        if (mouse.x > this.x && this.x > this.size * 10) this.x -= 1;
                        if (mouse.y < this.y && this.y < canvas.height - this.size * 10) this.y += 1;
                        if (mouse.y > this.y && this.y > this.size * 10) this.y -= 1;
                    }
                }
                this.x += this.directionX;
                this.y += this.directionY;
                this.draw();
            }
        }

        function initCanvas() {
            particlesArray = [];
            let numberOfParticles = (canvas.height * canvas.width) / 15000;
            for (let i = 0; i < numberOfParticles; i++) {
                let size = (Math.random() * 2) + 1;
                let x = (Math.random() * ((innerWidth - size * 2) - (size * 2)) + size * 2);
                let y = (Math.random() * ((innerHeight - size * 2) - (size * 2)) + size * 2);
                let directionX = (Math.random() * 1) - 0.5;
                let directionY = (Math.random() * 1) - 0.5;
                let color = '#94a3b8'; // Slate 400
                particlesArray.push(new Particle(x, y, directionX, directionY, size, color));
            }
        }

        function connectParticles() {
            let opacityValue = 1;
            for (let a = 0; a < particlesArray.length; a++) {
                for (let b = a; b < particlesArray.length; b++) {
                    let distance = ((particlesArray[a].x - particlesArray[b].x) * (particlesArray[a].x - particlesArray[b].x)) + 
                                   ((particlesArray[a].y - particlesArray[b].y) * (particlesArray[a].y - particlesArray[b].y));
                    if (distance < (canvas.width / 7) * (canvas.height / 7)) {
                        opacityValue = 1 - (distance / 20000);
                        ctx.strokeStyle = `rgba(14, 165, 233, ${opacityValue * 0.2})`; // physio-500
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(particlesArray[a].x, particlesArray[a].y);
                        ctx.lineTo(particlesArray[b].x, particlesArray[b].y);
                        ctx.stroke();
                    }
                }
            }
        }

        function animateCanvas() {
            requestAnimationFrame(animateCanvas);
            ctx.clearRect(0, 0, innerWidth, innerHeight);
            for (let i = 0; i < particlesArray.length; i++) {
                particlesArray[i].update();
            }
            connectParticles();
        }

        // Only start canvas if window is loaded
        window.onload = function() {
            initCanvas();
            animateCanvas();
        }

        /* --- 5. Horizontal Slider Logic (Projects) --- */
        const slider = document.getElementById('projectSlider');
        let isDown = false;
        let startX;
        let scrollLeft;

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('active');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.classList.remove('active');
        });
        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.remove('active');
        });
        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2; // Scroll-fast
            if(document.documentElement.getAttribute('dir') === 'rtl') {
                 slider.scrollLeft = scrollLeft + walk;
            } else {
                 slider.scrollLeft = scrollLeft - walk;
            }
        });

        document.getElementById('nextBtn')?.addEventListener('click', () => {
            const scrollAmount = window.innerWidth > 768 ? 600 : window.innerWidth * 0.85;
            slider.scrollBy({ left: currentLang === 'fa' ? -scrollAmount : scrollAmount, behavior: 'smooth' });
        });
        document.getElementById('prevBtn')?.addEventListener('click', () => {
            const scrollAmount = window.innerWidth > 768 ? 600 : window.innerWidth * 0.85;
            slider.scrollBy({ left: currentLang === 'fa' ? scrollAmount : -scrollAmount, behavior: 'smooth' });
        });

        /* --- 6. FAQ Accordion Logic --- */
        const faqBtns = document.querySelectorAll('.faq-btn');
        faqBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('[data-lucide]');
                const isOpen = content.style.height && content.style.height !== '0px';

                // Close all others
                document.querySelectorAll('.faq-content').forEach(c => {
                    c.style.height = '0px';
                    c.style.opacity = '0';
                    const otherIcon = c.previousElementSibling.querySelector('[data-lucide]');
                    if (otherIcon) {
                        otherIcon.setAttribute('data-lucide', 'plus');
                    }
                });
                
                if (!isOpen) {
                    content.style.height = content.scrollHeight + 'px';
                    content.style.opacity = '1';
                    if (icon) icon.setAttribute('data-lucide', 'minus');
                } else {
                    if (icon) icon.setAttribute('data-lucide', 'plus');
                }
                
                lucide.createIcons(); // Re-render current icons
            });
        });

