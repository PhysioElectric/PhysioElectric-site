/* =============================================================
   PhysioElectric - Public site JS
   - Lucide icons
   - Navbar scroll state
   - Mobile menu
   - Scroll reveal (IntersectionObserver)
   - Hero particle canvas
   - Project slider (drag + buttons, RTL aware)
   - FAQ accordion
   - Process timeline progress
   - Telegram deep-link (tg:// with https fallback)
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

    /* ---------------- Scroll reveal ---------------- */
    function initReveal() {
        var els = document.querySelectorAll('.reveal');
        if (!els.length) { return; }
        if (!('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('active'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        els.forEach(function (el) { io.observe(el); });
    }

    /* ---------------- Hero particle canvas ---------------- */
    function initHeroCanvas() {
        var canvas = document.getElementById('hero-canvas');
        if (!canvas) { return; }
        var ctx = canvas.getContext('2d');
        var particles = [];
        var count, w, h, dpr;

        function resize() {
            dpr = Math.min(window.devicePixelRatio || 1, 2);
            w = canvas.clientWidth || canvas.parentElement.clientWidth || window.innerWidth;
            h = canvas.clientHeight || canvas.parentElement.clientHeight || window.innerHeight;
            canvas.width = w * dpr;
            canvas.height = h * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }

        function spawn() {
            particles = [];
            count = Math.max(28, Math.min(90, Math.floor(w / 16)));
            for (var i = 0; i < count; i++) {
                particles.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    r: Math.random() * 1.8 + 0.6,
                    vx: (Math.random() - 0.5) * 0.45,
                    vy: (Math.random() - 0.5) * 0.45
                });
            }
        }

        function step() {
            ctx.clearRect(0, 0, w, h);
            for (var i = 0; i < particles.length; i++) {
                var p = particles[i];
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0 || p.x > w) { p.vx *= -1; }
                if (p.y < 0 || p.y > h) { p.vy *= -1; }
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(14, 165, 233, 0.55)'; // physio-500
                ctx.fill();
            }
            var linkDist = Math.min(130, w / 8);
            for (var a = 0; a < particles.length; a++) {
                for (var b = a + 1; b < particles.length; b++) {
                    var dx = particles[a].x - particles[b].x;
                    var dy = particles[a].y - particles[b].y;
                    var d2 = dx * dx + dy * dy;
                    if (d2 < linkDist * linkDist) {
                        var alpha = (1 - Math.sqrt(d2) / linkDist) * 0.22;
                        ctx.strokeStyle = 'rgba(14, 165, 233,' + alpha.toFixed(3) + ')';
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(particles[a].x, particles[a].y);
                        ctx.lineTo(particles[b].x, particles[b].y);
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
            // In RTL the scroll axis is inverted in most browsers.
            slider.scrollLeft = IS_RTL ? startScroll + walk : startScroll - walk;
        });

        var scrollAmount = function () {
            var card = slider.querySelector('.min-w-\\[85vw\\]');
            var w = card ? card.offsetWidth + 24 : (window.innerWidth > 768 ? 600 : window.innerWidth * 0.85);
            return w;
        };
        document.getElementById('nextBtn') && document.getElementById('nextBtn').addEventListener('click', function () {
            slider.scrollBy({ left: IS_RTL ? -scrollAmount() : scrollAmount(), behavior: 'smooth' });
        });
        document.getElementById('prevBtn') && document.getElementById('prevBtn').addEventListener('click', function () {
            slider.scrollBy({ left: IS_RTL ? scrollAmount() : -scrollAmount(), behavior: 'smooth' });
        });
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

    /* ---------------- Process timeline ---------------- */
    function initTimeline() {
        var wrap = document.getElementById('processTimeline');
        var bar = document.getElementById('timelineProgress');
        if (!wrap || !bar) { return; }
        var update = function () {
            var rect = wrap.getBoundingClientRect();
            var vh = window.innerHeight;
            var progress = (vh * 0.6 - rect.top) / rect.height;
            progress = Math.max(0, Math.min(1, progress));
            bar.style.height = (progress * 100).toFixed(1) + '%';
        };
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    }

    /* ---------------- Telegram deep link ----------------
       href="https://t.me/user" (works everywhere, no JS needed)
       + data-tg-link="tg://resolve?domain=user"
       On mobile we try the app first; if it is not installed we
       fall back to the web link after a short delay.            */
    function initTelegramLinks() {
        var links = document.querySelectorAll('[data-tg-link]');
        if (!links.length) { return; }
        var mobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        links.forEach(function (link) {
            link.addEventListener('click', function (e) {
                var scheme = link.getAttribute('data-tg-link');
                if (!scheme || !mobile) { return; } // desktop: plain https link
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
