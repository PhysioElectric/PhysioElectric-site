/* =============================================================
   PhysioElectric - Public site JS
   - Lucide icons
   - Navbar scroll state
   - Mobile menu
   - Scroll reveal (Chrome/Firefox Fixed)
   - Hero particle canvas (Smooth Constellation)
   - Project slider
   - FAQ accordion
   - Process timeline progress (Chrome RTL Fixed)
   - Telegram deep-link
   ============================================================= */
(function () {
    'use strict';

    var LANG = document.currentScript ? (document.currentScript.getAttribute('data-lang') || 'fa') : 'fa';
    var IS_RTL = (document.documentElement.getAttribute('dir') || '').toLowerCase() === 'rtl';

    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) { window.lucide.createIcons(); }
        initNavbar();
        initMobileMenu();
        initReveal();
        initHeroCanvas();
        initProjectSlider();
        initTerminalBackground();
        initBlogSlider();
        initScrollVideos();
        initFaq();
        initTimeline();
        initTelegramLinks();
    });

    /* ---------------- Navbar ---------------- */
    function initNavbar() {
        var nav = document.getElementById('navbar');
        if (!nav) { return; }
        var onScroll = function () {
            if (window.scrollY > 24) { nav.classList.add('scrolled'); }
            else { nav.classList.remove('scrolled'); }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ---------------- Mobile menu ---------------- */
    function initMobileMenu() {
        var btn = document.getElementById('mobileMenuBtn');
        var menu = document.getElementById('mobileMenu');
        if (!btn || !menu) { return; }
        btn.addEventListener('click', function () {
            menu.classList.toggle('open');
        });
        menu.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () { menu.classList.remove('open'); });
        });
    }

   
 /* ---------------- Scroll reveal (Bi-directional interactive) ---------------- */
function initReveal() {
    var els = document.querySelectorAll('.reveal, .reveal-from-left, .reveal-from-right');
    if (!els.length) { return; }

    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            } else {
                // با خروج المان از صفحه، کلاس active برداشته می‌شود تا با ورود مجدد انیمیشن تکرار شود
                entry.target.classList.remove('active');
            }
        });
    // تنظیم threshold روی 0.15 باعث می‌شود انیمیشن زمانی شروع شود که کمی از المان وارد صفحه شده باشد
    }, { threshold: 0.15, rootMargin: '0px 0px -50px 0px' });

    els.forEach(function (el) { io.observe(el); });

    // سیستم بک‌آپ برای کروم و سافاری تا المان‌هایی که از قبل در صفحه (Viewport) هستند مخفی نمانند
    setTimeout(function() {
        var vh = window.innerHeight || document.documentElement.clientHeight;
        els.forEach(function (el) {
            var rect = el.getBoundingClientRect();
            if (rect.top < vh) {
                el.classList.add('active');
            }
        });
    }, 400);
}
/* ---------------- Interactive Hero Canvas (Soft Magnetic Constellation) ---------------- */
    function initHeroCanvas() {
        var canvas = document.getElementById('hero-canvas');
        if (!canvas) return;

        var ctx = canvas.getContext('2d');
        var particles = [];
        var w, h;

        var mouse = { 
            x: null, 
            y: null, 
            radius: 250 // شعاع اتصال متعادل
        };

        window.addEventListener('mousemove', function (e) {
            var rect = canvas.getBoundingClientRect();
            mouse.x = e.clientX - rect.left;
            mouse.y = e.clientY - rect.top;
        });

        window.addEventListener('mouseout', function () {
            mouse.x = null;
            mouse.y = null;
        });

        function resize() {
            w = canvas.parentElement.clientWidth || window.innerWidth;
            h = canvas.parentElement.clientHeight || window.innerHeight;
            canvas.width = w;
            canvas.height = h;
        }

        function spawn() {
            particles = [];
            var count = Math.max(90, Math.floor((w * h) / 8500));
            for (var i = 0; i < count; i++) {
                particles.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    r: Math.random() * 1.8 + 1.2,
                    vx: (Math.random() - 0.5) * 0.5, // حرکت بسیار نرم
                    vy: (Math.random() - 0.5) * 0.5
                });
            }
        }

        function step() {
            ctx.clearRect(0, 0, w, h);
            
            for (var i = 0; i < particles.length; i++) {
                var p = particles[i];

                p.x += p.vx;
                p.y += p.vy;

                if (p.x < 0 || p.x > w) p.vx *= -1;
                if (p.y < 0 || p.y > h) p.vy *= -1;

                if (mouse.x !== null && mouse.y !== null) {
                    var dx = mouse.x - p.x;
                    var dy = mouse.y - p.y;
                    var dist = Math.sqrt(dx * dx + dy * dy);
                    
                    if (dist < mouse.radius) {
                          var alpha = (1 - dist / mouse.radius) * 0.6; // خطوط پررنگ‌تر
                            ctx.beginPath();
                            ctx.strokeStyle = 'rgba(14, 165, 233, ' + alpha.toFixed(3) + ')';
                            ctx.lineWidth = 1.5; // خطوط ضخیم‌تر
                            ctx.moveTo(p.x, p.y);
                            ctx.lineTo(mouse.x, mouse.y);
                            ctx.stroke();

    // مگنت قدرتمندتر (تغییر از 0.15 به 1.5)
                            var force = (mouse.radius - dist) / mouse.radius;
                                p.x -= (dx / dist) * force * 1.5; 
                                p.y -= (dy / dist) * force * 1.5;
                    }
                }

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(2, 132, 199, 0.65)'; 
                ctx.fill();

                for (var j = i + 1; j < particles.length; j++) {
                    var p2 = particles[j];
                    var dx2 = p.x - p2.x;
                    var dy2 = p.y - p2.y;
                    var dist2 = Math.sqrt(dx2 * dx2 + dy2 * dy2);
                    
                    if (dist2 < 110) {
                        var linkAlpha = (1 - dist2 / 110) * 0.2;
                        ctx.beginPath();
                        ctx.strokeStyle = 'rgba(14, 165, 233, ' + linkAlpha.toFixed(3) + ')';
                        ctx.lineWidth = 0.8;
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(step);
        }

        resize();
        spawn();
        step();
        window.addEventListener('resize', function () { resize(); spawn(); });
    }
    /* ---------------- Project slider ---------------- */
    function initProjectSlider() {
        var slider = document.getElementById('projectSlider');
        if (!slider) { return; }
        var isDown = false, startX = 0, startScroll = 0;

        slider.addEventListener('mousedown', function (e) {
            isDown = true;
            slider.classList.add('active');
            startX = e.pageX;
            startScroll = slider.scrollLeft;
        });
        ['mouseleave', 'mouseup', 'blur'].forEach(function (ev) {
            slider.addEventListener(ev, function () {
                isDown = false;
                slider.classList.remove('active');
            });
        });
        slider.addEventListener('mousemove', function (e) {
            if (!isDown) { return; }
            e.preventDefault();
            var walk = (e.pageX - startX) * 1.6;
            slider.scrollLeft = IS_RTL ? startScroll + walk : startScroll - walk;
        });

        var scrollAmount = function () {
            var card = slider.querySelector('.snap-center');
            var w = card ? card.offsetWidth + 24 : (window.innerWidth > 768 ? 600 : window.innerWidth * 0.85);
            return w;
        };
        
        // اتصال دکمه‌های هدر
        var nextBtn = document.getElementById('projNextBtn');
        var prevBtn = document.getElementById('projPrevBtn');
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                slider.scrollBy({ left: IS_RTL ? -scrollAmount() : scrollAmount(), behavior: 'smooth' });
            });
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                slider.scrollBy({ left: IS_RTL ? scrollAmount() : -scrollAmount(), behavior: 'smooth' });
            });
        }
    }
    /* ---------------- Scroll to Play Videos ---------------- */
    function initScrollVideos() {
        var vids = document.querySelectorAll('.scroll-play-vid');
        if (!vids.length) return;

        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                var vid = entry.target;
                // پیدا کردن آیکون پلی که روی ویدیو قرار دارد
                var playIcon = vid.parentElement.querySelector('.play-icon-overlay');

                if (entry.isIntersecting) {
                    // وقتی ویدیو وارد صفحه می‌شود
                    var playPromise = vid.play();
                    if (playPromise !== undefined) {
                        playPromise.then(function() {
                            // پس از شروع پخش: ویدیو کاملاً روشن و آیکون محو می‌شود
                            vid.style.opacity = '1';
                            if (playIcon) {
                                playIcon.style.opacity = '0';
                                playIcon.style.transform = 'scale(1.2)';
                            }
                        }).catch(function(error) {
                            console.log("Auto-play prevented by browser.", error);
                        });
                    }
                } else {
                    // وقتی ویدیو از صفحه خارج می‌شود
                    vid.pause();
                    // بازگشت به حالت تاریک و نمایش مجدد آیکون
                    vid.style.opacity = '0.5';
                    if (playIcon) {
                        playIcon.style.opacity = '1';
                        playIcon.style.transform = 'scale(1)';
                    }
                }
            });
        }, { threshold: 0.4 }); // وقتی 40 درصد ویدیو وارد صفحه شد، پخش شروع می‌شود

        vids.forEach(function(vid) {
            io.observe(vid);
        });
    }
/* ---------------- Background Terminal Typing Effect ---------------- */
function initTerminalBackground() {
    var el = document.getElementById('terminal-bg-text');
    if (!el) return;
    
    var snippets = [
        "import torch\nimport torch.nn as nn\n\nclass PhysioNet(nn.Module):\n    def __init__(self):\n        super().__init__()\n        self.conv = nn.Conv2d(1, 64, 3)\n\n    def forward(self, x):\n        return self.conv(x)",
        "clear;\nclc;\n\n% Initialize FEM Simulation\nmesh = createMesh('geometry.stl');\nbc = applyBoundary(mesh, 'dirichlet');\n\nsolution = solvePDE(bc);\nplotResults(solution);",
        "// Real-time Control Loop\nvoid control_task() {\n    while(running) {\n        auto state = read_sensors();\n        auto output = pid.compute(state);\n        set_actuators(output);\n        delay(10);\n    }\n}"
    ];
    
    var snippetIndex = 0;
    var charIndex = 0;
    var isDeleting = false;
    
    function type() {
        var currentSnippet = snippets[snippetIndex];
        
        if (isDeleting) {
            el.textContent = currentSnippet.substring(0, charIndex - 1);
            charIndex--;
        } else {
            el.textContent = currentSnippet.substring(0, charIndex + 1);
            charIndex++;
        }

        var speed = isDeleting ? 10 : 35; // سرعت تایپ و پاک شدن

        if (!isDeleting && charIndex === currentSnippet.length) {
            speed = 3000; // مکث بعد از اتمام تایپ یک قطعه کد
            isDeleting = true;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            snippetIndex = (snippetIndex + 1) % snippets.length;
            speed = 500;
        }

        setTimeout(type, speed);
    }
    setTimeout(type, 1000);
}
/* ---------------- Blog slider ---------------- */
    function initBlogSlider() {
        var slider = document.getElementById('blogSlider');
        if (!slider) { return; }
        var isDown = false, startX = 0, startScroll = 0;

        slider.addEventListener('mousedown', function (e) {
            isDown = true; slider.classList.add('active');
            startX = e.pageX; startScroll = slider.scrollLeft;
        });
        ['mouseleave', 'mouseup', 'blur'].forEach(function (ev) {
            slider.addEventListener(ev, function () {
                isDown = false; slider.classList.remove('active');
            });
        });
        slider.addEventListener('mousemove', function (e) {
            if (!isDown) { return; }
            e.preventDefault();
            var walk = (e.pageX - startX) * 1.6;
            slider.scrollLeft = IS_RTL ? startScroll + walk : startScroll - walk;
        });

        var scrollAmount = function () {
            var card = slider.querySelector('.snap-center');
            return card ? card.offsetWidth + 24 : 400;
        };
        var nextBtn = document.getElementById('blogNextBtn');
        var prevBtn = document.getElementById('blogPrevBtn');
        if (nextBtn) nextBtn.addEventListener('click', function () { slider.scrollBy({ left: IS_RTL ? -scrollAmount() : scrollAmount(), behavior: 'smooth' }); });
        if (prevBtn) prevBtn.addEventListener('click', function () { slider.scrollBy({ left: IS_RTL ? scrollAmount() : -scrollAmount(), behavior: 'smooth' }); });
    }

    /* ---------------- FAQ accordion ---------------- */
    function initFaq() {
        var btns = document.querySelectorAll('.faq-btn');
        if (!btns.length) { return; }
        btns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var content = btn.nextElementSibling;
                if (!content) { return; }
                var icon = btn.querySelector('[data-lucide], svg');
                var isOpen = content.style.height !== '0px' && content.style.height !== '';

                document.querySelectorAll('.faq-content').forEach(function (c) {
                    c.style.height = '0px';
                    c.style.opacity = '0';
                });
                document.querySelectorAll('.faq-btn svg[data-lucide]').forEach(function (s) {
                    s.setAttribute('data-lucide', 'plus');
                });

                if (!isOpen) {
                    content.style.height = content.scrollHeight + 'px';
                    content.style.opacity = '1';
                    if (icon) { icon.setAttribute('data-lucide', 'minus'); }
                }
                if (window.lucide) { window.lucide.createIcons(); }
            });
        });
    }
/* ---------------- Process timeline (Pure CSS Fix) ---------------- */
    function initTimeline() {
        var wrap = document.getElementById('processTimeline');
        var bar = document.getElementById('timelineProgress');
        if (!wrap || !bar) { return; }

        var update = function () {
            var rect = wrap.getBoundingClientRect();
            var vh = window.innerHeight || document.documentElement.clientHeight;
            
            var startOffset = vh * 0.75;
            var progress = (startOffset - rect.top) / rect.height;
            progress = Math.max(0, Math.min(1, progress));
            
            bar.style.height = (progress * 100).toFixed(1) + '%';

            // روشن شدن دایره‌ها
            var steps = wrap.querySelectorAll('.pe-step');
            steps.forEach(function(step) {
                var stepRect = step.getBoundingClientRect();
                var dot = step.querySelector('.pe-dot');
                if (dot) {
                    if (stepRect.top < vh * 0.65) {
                        dot.classList.add('active-dot');
                    } else {
                        dot.classList.remove('active-dot');
                    }
                }
            });
        };

        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
        setTimeout(update, 300);
    }
    /* ---------------- Telegram deep link ---------------- */
    function initTelegramLinks() {
        var links = document.querySelectorAll('[data-tg-link]');
        if (!links.length) { return; }
        var mobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        links.forEach(function (link) {
            link.addEventListener('click', function (e) {
                var scheme = link.getAttribute('data-tg-link');
                if (!scheme || !mobile) { return; } 
                e.preventDefault();
                var webUrl = link.getAttribute('href');
                var fallback = setTimeout(function () {
                    if (webUrl) { window.location.href = webUrl; }
                }, 1500);
                document.addEventListener('visibilitychange', function onVis() {
                    if (document.hidden) { clearTimeout(fallback); }
                    document.removeEventListener('visibilitychange', onVis);
                });
                window.location.href = scheme;
            });
        });
    }
})();

// --- Interactive 3D Hover Effect for IoT Cards ---
document.addEventListener('DOMContentLoaded', () => {
    const iotCards = document.querySelectorAll('.iot-card');

    iotCards.forEach(card => {
        const icon = card.querySelector('.iot-icon');
        if (!icon) return;

        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = ((y - centerY) / centerY) * -20;
            const rotateY = ((x - centerX) / centerX) * 20;

            icon.classList.remove('iot-icon-float');
            icon.style.transition = 'transform 0.1s ease-out';
            icon.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.1, 1.1, 1.1)`;
        });

        card.addEventListener('mouseleave', () => {
            icon.style.transition = 'transform 0.5s ease-out';
            icon.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
            
            setTimeout(() => {
                icon.classList.add('iot-icon-float');
                icon.style.transition = '';
            }, 500);
        });
    });
});

/* ---------------- Mobile menu ---------------- */
    function initMobileMenu() {
        var btn = document.getElementById('mobileMenuBtn');
        var menu = document.getElementById('mobileMenu');
        if (!btn || !menu) return;
        btn.addEventListener('click', function () {
            if(menu.style.display === 'none' || menu.style.display === ''){
                menu.style.display = 'block';
                setTimeout(() => menu.style.transform = 'scaleY(1)', 10);
            } else {
                menu.style.transform = 'scaleY(0)';
                setTimeout(() => menu.style.display = 'none', 300);
            }
        });
    }