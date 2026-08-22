
        // Initialize Icons
        lucide.createIcons();

        /* --- Bilingual System (EN/FA) --- */
        const dictionary = {
            en: {
                'nav.home': 'Home', 'nav.capabilities': 'Capabilities', 'nav.projects': 'Projects', 'nav.about': 'About', 'nav.faq': 'FAQ', 'nav.cta': 'Start a Project',
                'about.back': 'Back to Home',
                'about.hero.title': 'The People Behind <span class="text-gradient">PhysioElectric</span>',
                'about.hero.subtitle': 'PhysioElectric is a technology and engineering team focused on building intelligent software, advanced simulations, AI-driven systems and digital solutions.',
                
                'team.title': 'Meet the Team',
                'team.subtitle': 'Behind every project is a team of people who combine engineering thinking, creativity and technology.',
                'team.viewProfile': 'View Profile',
                'team.m1.name': 'Dr. Amir Hosseini', 'team.m1.role': 'Lead Engineer / AI Systems', 'team.m1.desc': 'Architecting intelligent systems and overseeing complex computational models.',
                'team.m2.name': 'Sara Radmanesh', 'team.m2.role': 'Software Architect', 'team.m2.desc': 'Designing scalable web infrastructure and bridging the gap between math and code.',
                'team.m3.name': 'Mohammad Reza Afraz', 'team.m3.role': 'Simulation & Analysis', 'team.m3.desc': 'Translating real-world physical phenomena into highly accurate COMSOL models.',
                'team.m4.name': 'Neda Vahdati', 'team.m4.role': 'Embedded Systems', 'team.m4.desc': 'Developing IoT hardware and optimizing microcontrollers for edge computing.',
                
                'purpose.tag': 'Why PhysioElectric Exists',
                'purpose.title': 'We believe complex problems deserve engineered solutions.',
                'purpose.p1': 'PhysioElectric was created around a unique intersection: engineering, programming, artificial intelligence, scientific computing, and digital technologies.',
                'purpose.p2': 'Our goal is not simply to produce software or write code in isolation. We aim to deeply understand the core problem, engineer a suitable methodology, and deliver something that is profoundly practical, reliable, and scientifically sound.',
                
                'motivation.title': 'What Drives Us',
                'motivation.p1.title': 'Curiosity', 'motivation.p1.desc': 'We constantly explore new technologies, advanced algorithms, and engineering methods to stay at the cutting edge of digital science.',
                'motivation.p2.title': 'Precision', 'motivation.p2.desc': 'We believe good engineering begins with understanding the details. Rigor and exactness define every model and line of code we write.',
                'motivation.p3.title': 'Innovation', 'motivation.p3.desc': 'We look for smarter ways to solve complex problems, combining disciplines like AI and multiphysics to unlock new possibilities.',
                'motivation.p4.title': 'Impact', 'motivation.p4.desc': 'Theoretical brilliance is not enough. We build solutions that are highly practical, useful, and meaningful for the enterprises we partner with.',
                
                'process.title': 'How We Work', 'process.subtitle': 'Every project starts with understanding the problem.',
                'process.s1.title': 'Understand', 'process.s1.desc': 'We first understand the problem, goals, constraints and core requirements of the system.',
                'process.s2.title': 'Analyze', 'process.s2.desc': 'We investigate the technical challenges, math models, and determine the appropriate approach.',
                'process.s3.title': 'Design', 'process.s3.desc': 'We design the overarching architecture, engineering methodology, and precise technical solution.',
                'process.s4.title': 'Build', 'process.s4.desc': 'We implement code, run simulations, test edge cases, and continuously improve the prototype.',
                'process.s5.title': 'Deliver', 'process.s5.desc': 'We finalize, rigorously validate, deploy to production and deliver a truly reliable solution.',

                'mindset.title': 'Think. Engineer. Build. Improve.',
                'mindset.subtitle': 'From mathematical models and simulations to software systems and intelligent agents, we approach every project as an engineering problem.',
                
                'node.problem': 'Problem', 'node.analysis': 'Analysis', 'node.model': 'Model', 'node.implementation': 'Implementation', 'node.validation': 'Validation', 'node.solution': 'Solution',

                'domain.sw': 'Software Engineering', 'domain.ai': 'Artificial Intelligence', 'domain.math': 'Scientific Computing', 'domain.matlab': 'MATLAB / COMSOL', 'domain.web': 'Web Technologies', 'domain.automation': 'Automation Agents', 'domain.iot': 'Embedded / IoT', 'domain.digital': 'Digital Systems',

                'phil.title': 'Technology is powerful when <span class="text-physio-600">engineering</span> gives it direction.',
                'phil.desc': 'We refuse to build fragile code or untested concepts. At PhysioElectric, every piece of software, every automation node, and every mathematical model is crafted with absolute precision. We exist to engineer the future, not just write it.',

                'cta.title': 'Let\'s Build Something Meaningful.', 'cta.desc': 'Have an idea, a technical challenge or a project in mind? Let\'s talk.',
                'cta.btnPrimary': 'Start a Project', 'cta.btnSecondary': 'Explore Our Projects',
                'footer.desc': 'Engineering Technology. Building Intelligent Solutions for modern businesses and scientific challenges.',
                'footer.nav': 'Navigation', 'footer.serv': 'Services', 'footer.lang': 'Language',
            },
            fa: {
                'nav.home': 'خانه', 'nav.capabilities': 'توانمندی‌ها', 'nav.projects': 'پروژه‌ها', 'nav.about': 'درباره ما', 'nav.faq': 'سوالات متداول', 'nav.cta': 'شروع پروژه',
                'about.back': 'بازگشت به صفحه اصلی',
                'about.hero.title': 'افرادِ پشت پرده <span class="text-gradient">فیزیوالکتریک</span>',
                'about.hero.subtitle': 'فیزیوالکتریک یک تیم تکنولوژی و مهندسی است که بر ساخت نرم‌افزارهای هوشمند، شبیه‌سازی‌های پیشرفته، سیستم‌های مبتنی بر AI و راهکارهای دیجیتال تمرکز دارد.',
                
                'team.title': 'آشنایی با تیم',
                'team.subtitle': 'پشت هر پروژه، تیمی از افراد قرار دارند که تفکر مهندسی، خلاقیت و تکنولوژی را در هم می‌آمیزند.',
                'team.viewProfile': 'مشاهده پروفایل',
                'team.m1.name': 'دکتر امیر حسینی', 'team.m1.role': 'مهندس ارشد / سیستم‌های AI', 'team.m1.desc': 'طراحی معماری سیستم‌های هوشمند و نظارت بر مدل‌های پیچیده محاسباتی.',
                'team.m2.name': 'سارا رادمنش', 'team.m2.role': 'آرشیتکت نرم‌افزار', 'team.m2.desc': 'طراحی زیرساخت‌های مقیاس‌پذیر وب و پر کردن شکاف بین ریاضیات و کد.',
                'team.m3.name': 'محمدرضا افراز', 'team.m3.role': 'شبیه‌سازی و تحلیل', 'team.m3.desc': 'تبدیل پدیده‌های فیزیکی دنیای واقعی به مدل‌های کامسول (COMSOL) با دقت بالا.',
                'team.m4.name': 'ندا وحدتی', 'team.m4.role': 'سیستم‌های نهفته (Embedded)', 'team.m4.desc': 'توسعه سخت‌افزارهای IoT و بهینه‌سازی میکروکنترلرها برای محاسبات لبه.',
                
                'purpose.tag': 'چرا فیزیوالکتریک وجود دارد؟',
                'purpose.title': 'ما معتقدیم مسائل پیچیده، شایسته راهکارهای مهندسی‌شده هستند.',
                'purpose.p1': 'فیزیوالکتریک در یک نقطه تلاقی منحصربه‌فرد شکل گرفت: مهندسی، برنامه‌نویسی، هوش مصنوعی، محاسبات علمی و تکنولوژی‌های دیجیتال.',
                'purpose.p2': 'هدف ما صرفاً تولید نرم‌افزار یا نوشتن کدهای ایزوله نیست. هدف ما درک عمیق هسته مسئله، مهندسی یک روش‌شناسی مناسب و ارائه محصولی است که کاملاً کاربردی، قابل‌اتکا و از نظر علمی معتبر باشد.',
                
                'motivation.title': 'محرک‌های ما',
                'motivation.p1.title': 'کنجکاوی', 'motivation.p1.desc': 'ما پیوسته تکنولوژی‌های جدید، الگوریتم‌های پیشرفته و روش‌های مهندسی را کاوش می‌کنیم تا در لبه علم دیجیتال باقی بمانیم.',
                'motivation.p2.title': 'دقت', 'motivation.p2.desc': 'ما معتقدیم مهندسی خوب با درک جزئیات آغاز می‌شود. سخت‌گیری و دقت، تعریف‌کننده تک‌تک مدل‌ها و خطوط کدی است که می‌نویسیم.',
                'motivation.p3.title': 'نوآوری', 'motivation.p3.desc': 'ما به دنبال راه‌های هوشمندانه‌تر برای حل مسائل پیچیده هستیم و دیسیپلین‌هایی مانند AI و چندفیزیکی را برای خلق امکانات جدید ترکیب می‌کنیم.',
                'motivation.p4.title': 'تاثیرگذاری', 'motivation.p4.desc': 'نبوغ تئوریک کافی نیست. ما راهکارهایی می‌سازیم که برای سازمان‌هایی که با آن‌ها شراکت داریم، کاملاً عملی، مفید و معنادار باشند.',
                
                'process.title': 'نحوه کار ما', 'process.subtitle': 'هر پروژه با درک مسئله آغاز می‌شود.',
                'process.s1.title': 'درک مسئله', 'process.s1.desc': 'ما ابتدا مشکل، اهداف، محدودیت‌ها و نیازمندی‌های کلیدی سیستم را درک می‌کنیم.',
                'process.s2.title': 'تحلیل', 'process.s2.desc': 'چالش‌های فنی و مدل‌های ریاضی را بررسی کرده و رویکرد مناسب را تعیین می‌کنیم.',
                'process.s3.title': 'طراحی', 'process.s3.desc': 'معماری کلی، متدولوژی مهندسی و راهکار فنی دقیق را طراحی می‌کنیم.',
                'process.s4.title': 'ساخت', 'process.s4.desc': 'کدها را پیاده‌سازی، شبیه‌سازی‌ها را اجرا، موارد خاص را تست و پروتوتایپ را بهبود می‌بخشیم.',
                'process.s5.title': 'تحویل', 'process.s5.desc': 'راهکار را نهایی، به دقت اعتبارسنجی، در محیط عملیاتی مستقر و یک سیستم کاملاً قابل‌اتکا تحویل می‌دهیم.',

                'mindset.title': 'تفکر. مهندسی. ساخت. بهبود.',
                'mindset.subtitle': 'از مدل‌های ریاضی و شبیه‌سازی‌ها گرفته تا سیستم‌های نرم‌افزاری و عوامل هوشمند، ما با هر پروژه به عنوان یک مسئله مهندسی برخورد می‌کنیم.',
                
                'node.problem': 'مسئله', 'node.analysis': 'تحلیل', 'node.model': 'مدل‌سازی', 'node.implementation': 'پیاده‌سازی', 'node.validation': 'اعتبارسنجی', 'node.solution': 'راهکار',

                'domain.sw': 'مهندسی نرم‌افزار', 'domain.ai': 'هوش مصنوعی', 'domain.math': 'محاسبات علمی', 'domain.matlab': 'متلب / کامسول', 'domain.web': 'تکنولوژی‌های وب', 'domain.automation': 'عوامل اتوماسیون', 'domain.iot': 'اینترنت اشیا (IoT)', 'domain.digital': 'سیستم‌های دیجیتال',

                'phil.title': 'تکنولوژی زمانی قدرتمند است که <span class="text-physio-600">مهندسی</span> به آن جهت دهد.',
                'phil.desc': 'ما از ساخت کدهای شکننده یا مفاهیم تست‌نشده اجتناب می‌کنیم. در فیزیوالکتریک، هر قطعه نرم‌افزار، هر نود اتوماسیون و هر مدل ریاضی با دقت مطلق ساخته می‌شود. ما اینجا هستیم تا آینده را مهندسی کنیم، نه اینکه فقط آن را بنویسیم.',

                'cta.title': 'بیایید محصولی معنادار بسازیم.', 'cta.desc': 'ایده، چالش فنی یا پروژه‌ای در ذهن دارید؟ بیایید صحبت کنیم.',
                'cta.btnPrimary': 'شروع پروژه', 'cta.btnSecondary': 'بررسی پروژه‌های ما',
                'footer.desc': 'تکنولوژی مهندسی. خلق راهکارهای هوشمند برای کسب‌وکارهای مدرن و چالش‌های علمی.',
                'footer.nav': 'دسترسی سریع', 'footer.serv': 'خدمات', 'footer.lang': 'زبان',
            }
        };

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

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dictionary[lang][key]) {
                    el.innerHTML = dictionary[lang][key];
                }
            });
            lucide.createIcons();
        }

        document.addEventListener("DOMContentLoaded", () => {
            setLanguage('fa');
        });

        document.getElementById('langToggle').addEventListener('click', () => {
            setLanguage(currentLang === 'en' ? 'fa' : 'en');
        });

        /* --- Scroll Animations (Intersection Observer) --- */
        const revealElements = document.querySelectorAll('.reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.1 });

        revealElements.forEach(el => revealObserver.observe(el));

        window.addEventListener('scroll', () => {
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

        /* --- Interactive Hero Canvas (Engineering Grids) --- */
        const canvas = document.getElementById('hero-canvas');
        const ctx = canvas.getContext('2d');
        let particlesArray;

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        let mouse = { x: null, y: null, radius: 200 };
        window.addEventListener('mousemove', (event) => {
            mouse.x = event.x;
            mouse.y = event.y;
        });
        window.addEventListener('mouseout', () => { mouse.x = undefined; mouse.y = undefined; });

        class NodeParticle {
            constructor(x, y, dx, dy, size) {
                this.x = x; this.y = y;
                this.dx = dx; this.dy = dy;
                this.size = size;
                this.baseX = this.x;
                this.baseY = this.y;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = '#cbd5e1'; // slate-300
                ctx.fill();
            }
            update() {
                // Extremely slow drift
                this.baseX += this.dx;
                this.baseY += this.dy;

                if(this.baseX > canvas.width || this.baseX < 0) this.dx = -this.dx;
                if(this.baseY > canvas.height || this.baseY < 0) this.dy = -this.dy;

                // Mouse interaction - subtle repel
                if(mouse.x != null) {
                    let dx = mouse.x - this.baseX;
                    let dy = mouse.y - this.baseY;
                    let distance = Math.sqrt(dx*dx + dy*dy);
                    let maxDist = mouse.radius;
                    if(distance < maxDist) {
                        let force = (maxDist - distance) / maxDist;
                        this.x = this.baseX - (dx * force * 0.1);
                        this.y = this.baseY - (dy * force * 0.1);
                    } else {
                        this.x = this.baseX;
                        this.y = this.baseY;
                    }
                } else {
                    this.x = this.baseX;
                    this.y = this.baseY;
                }
                this.draw();
            }
        }

        function initCanvas() {
            particlesArray = [];
            // Less nodes for a more structured, engineering grid feel
            let numberOfParticles = (canvas.height * canvas.width) / 25000; 
            for (let i = 0; i < numberOfParticles; i++) {
                let size = 1.5;
                let x = Math.random() * canvas.width;
                let y = Math.random() * canvas.height;
                let dx = (Math.random() - 0.5) * 0.2; // very slow
                let dy = (Math.random() - 0.5) * 0.2;
                particlesArray.push(new NodeParticle(x, y, dx, dy, size));
            }
        }

        function connectParticles() {
            for (let a = 0; a < particlesArray.length; a++) {
                for (let b = a; b < particlesArray.length; b++) {
                    let distance = ((particlesArray[a].x - particlesArray[b].x) ** 2) + ((particlesArray[a].y - particlesArray[b].y) ** 2);
                    if (distance < 25000) { // connection threshold
                        let opacity = 1 - (distance / 25000);
                        ctx.strokeStyle = `rgba(14, 165, 233, ${opacity * 0.15})`; // physio-500
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
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < particlesArray.length; i++) {
                particlesArray[i].update();
            }
            connectParticles();
        }

        window.onload = function() {
            initCanvas();
            animateCanvas();
        }
    