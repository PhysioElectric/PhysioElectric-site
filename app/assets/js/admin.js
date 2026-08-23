/* =============================================================
   PhysioElectric - Admin panel JS
   - WYSIWYG editor (contenteditable + toolbar)
   - Secure image upload (CSRF header, server validates MIME)
   - Media picker (existing uploads)
   - Slug auto-generation from the English title
   ============================================================= */
(function () {
    'use strict';

    var csrf = document.currentScript
        ? (document.currentScript.getAttribute('data-csrf') || '')
        : '';

    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) { window.lucide.createIcons(); }
        initWysiwyg();
        initImageFields();
        initSlugSync();
    });

    /* ================= WYSIWYG ================= */
    function initWysiwyg() {
        document.querySelectorAll('.pe-wysiwyg').forEach(initEditor);
    }

    function initEditor(root) {
        var area = root.querySelector('.pe-editor-area');
        var hidden = root.querySelector('[data-wysiwyg-input]');
        if (!area || !hidden) { return; }

        // seed hidden input
        hidden.value = area.innerHTML;

        area.addEventListener('input', function () { hidden.value = area.innerHTML; });

        // sync on submit
        var form = root.closest('form');
        if (form) {
            form.addEventListener('submit', function () { hidden.value = area.innerHTML; });
        }

        var toolbar = root.querySelector('.pe-editor-toolbar');
        if (!toolbar) { return; }

        toolbar.addEventListener('mousedown', function (e) {
            // keep the editor selection alive
            if (e.target.closest('button')) { e.preventDefault(); }
        });

        toolbar.addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) { return; }
            area.focus();

            if (btn.dataset.cmd) {
                document.execCommand(btn.dataset.cmd, false, null);
            } else if (btn.dataset.block) {
                document.execCommand('formatBlock', false, btn.dataset.block);
            } else if (btn.dataset.action === 'link') {
                var url = window.prompt('URL:', 'https://');
                if (url && url !== 'https://') {
                    // only safe schemes
                    var clean = url.trim();
                    if (!/^(https?:|mailto:|tel:|\/|#)/i.test(clean)) {
                        window.alert('Only http, https, mailto, tel, relative or anchor links are allowed.');
                        return;
                    }
                    document.execCommand('createLink', false, clean);
                }
            } else if (btn.dataset.action === 'image') {
                openMediaModal(function (url) {
                    var img = document.createElement('img');
                    img.src = url;
                    img.alt = '';
                    if (document.queryCommandSupported('insertHTML')) {
                        document.execCommand('insertHTML', false, img.outerHTML);
                    } else {
                        document.execCommand('insertImage', false, url);
                    }
                    area.dispatchEvent(new Event('input'));
                });
            }
            hidden.value = area.innerHTML;
            if (window.lucide) { window.lucide.createIcons(); }
        });

        // paste as plain-ish HTML: strip scripts on paste
        area.addEventListener('paste', function (e) {
            e.preventDefault();
            var html = (e.clipboardData || window.clipboardData).getData('text/html');
            var text = (e.clipboardData || window.clipboardData).getData('text/plain');
            if (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                doc.querySelectorAll('script,style,iframe,object,embed,link,meta').forEach(function (n) { n.remove(); });
                doc.body.querySelectorAll('*').forEach(function (n) {
                    Array.prototype.slice.call(n.attributes).forEach(function (attr) {
                        if (/^on/i.test(attr.name) || (['href', 'src'].indexOf(attr.name) !== -1 && !/^(https?:|mailto:|tel:|\/|#)/i.test(attr.value))) {
                            n.removeAttribute(attr.name);
                        }
                    });
                });
                document.execCommand('insertHTML', false, doc.body.innerHTML);
            } else if (text) {
                document.execCommand('insertText', false, text);
            }
        });
    }

    /* ================= Media modal (shared) ================= */
    var mediaModal = null;

    function ensureMediaModal() {
        if (mediaModal) { return mediaModal; }
        mediaModal = document.createElement('div');
        mediaModal.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4';
        mediaModal.innerHTML =
            '<div class="absolute inset-0 bg-slate-900/60" data-close></div>' +
            '<div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6" dir="rtl">' +
            '  <h3 class="text-base font-bold text-slate-800 mb-4">انتخاب تصویر</h3>' +
            '  <label class="admin-btn admin-btn-ghost !py-2 !px-4 !text-sm cursor-pointer mb-4 w-full justify-center">' +
            '    <i data-lucide="upload" class="w-4 h-4"></i> آپلود تصویر جدید' +
            '    <input type="file" accept="image/jpeg,image/png,image/webp" data-upload-input class="hidden">' +
            '  </label>' +
            '  <div class="text-xs font-semibold text-slate-500 mb-2">یا از فایل‌های موجود:</div>' +
            '  <div class="media-grid mb-4" data-media-grid></div>' +
            '  <button type="button" class="admin-btn admin-btn-primary w-full justify-center" data-close>انتخاب شد / بستن</button>' +
            '</div>';
        document.body.appendChild(mediaModal);
        mediaModal.style.display = 'none';
        bindMediaModal(mediaModal);
        return mediaModal;
    }

    var mediaOnPick = null;
    var mediaLoaded = false;

    function bindMediaModal(modal) {
        modal.querySelectorAll('[data-close]').forEach(function (el) {
            el.addEventListener('click', function () { modal.style.display = 'none'; });
        });
        modal.querySelector('[data-upload-input]').addEventListener('change', function (e) {
            var file = e.target.files && e.target.files[0];
            if (!file) { return; }
            uploadImage(file, function (url) {
                modal.style.display = 'none';
                if (mediaOnPick) { mediaOnPick(url); }
            });
        });
        modal.querySelector('[data-media-grid]').addEventListener('click', function (e) {
            var img = e.target.closest('img');
            if (!img) { return; }
            var url = img.getAttribute('data-url');
            modal.style.display = 'none';
            if (mediaOnPick) { mediaOnPick(url); }
        });
    }

    function loadMedia(grid, done) {
        fetch('/admin/media', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                grid.innerHTML = '';
                var items = (data && data.items) || [];
                if (!items.length) {
                    grid.innerHTML = '<p class="text-xs text-slate-400 p-2">فایلی وجود ندارد.</p>';
                }
                items.forEach(function (it) {
                    var img = document.createElement('img');
                    img.src = it.url;
                    img.alt = it.name;
                    img.setAttribute('data-url', it.url);
                    img.title = it.name;
                    grid.appendChild(img);
                });
                if (done) { done(); }
            })
            .catch(function () {
                grid.innerHTML = '<p class="text-xs text-rose-500 p-2">خطا در بارگذاری فهرست فایل‌ها.</p>';
            });
    }

    function openMediaModal(onPick) {
        var modal = ensureMediaModal();
        mediaOnPick = onPick;
        modal.style.display = 'flex';
        if (window.lucide) { window.lucide.createIcons(); }
        if (!mediaLoaded) {
            loadMedia(modal.querySelector('[data-media-grid]'), function () { mediaLoaded = true; });
        }
    }

    /* ================= Upload helper ================= */
    function uploadImage(file, onOk, onErr) {
        var fd = new FormData();
        fd.append('image', file);
        fetch('/admin/upload', {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': csrf }
        })
            .then(function (r) { return r.json().catch(function () { return { ok: false, message: 'Bad response' }; }).then(function (d) { return { status: r.status, data: d }; }); })
            .then(function (res) {
                if (res.status === 200 && res.data.ok) { onOk(res.data.url); }
                else { (onErr || noop)(res.data.message || 'Upload failed'); }
            })
            .catch(function () { (onErr || noop)('Network error'); });
    }

    function noop() {}

    function showError(msg) {
        window.alert(msg || 'Error');
    }

    /* ================= Cover image fields ================= */
    function initImageFields() {
        document.querySelectorAll('.pe-image-field').forEach(function (field) {
            var hidden = field.querySelector('input[type="hidden"]');
            var preview = field.querySelector('[data-preview]');
            var fileInput = field.querySelector('[data-file-input]');
            var removeBtn = field.querySelector('[data-remove-image]');
            var mediaToggle = field.querySelector('[data-media-toggle]');
            if (!hidden || !fileInput) { return; }

            function refresh() {
                var val = hidden.value;
                preview.innerHTML = '';
                if (val) {
                    var img = document.createElement('img');
                    img.src = val;
                    img.className = 'w-full h-full object-cover';
                    preview.appendChild(img);
                    if (removeBtn) { removeBtn.classList.remove('hidden'); }
                } else {
                    preview.textContent = '—';
                    if (removeBtn) { removeBtn.classList.add('hidden'); }
                }
            }
            refresh();

            fileInput.addEventListener('change', function () {
                var file = fileInput.files && fileInput.files[0];
                if (!file) { return; }
                uploadImage(file,
                    function (url) { hidden.value = url; refresh(); },
                    showError
                );
                fileInput.value = '';
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    hidden.value = '';
                    refresh();
                });
            }

            if (mediaToggle) {
                mediaToggle.addEventListener('click', function () {
                    openMediaModal(function (url) {
                        hidden.value = url;
                        refresh();
                    });
                });
            }
        });
    }

    /* ================= Slug auto-generation ================= */
    function initSlugSync() {
        var form = document.querySelector('[data-slug-form]');
        if (!form) { return; }
        var source = form.querySelector('[data-slug-source]');
        var target = form.querySelector('[data-slug-target]');
        var faTarget = form.querySelector('input[name="slug_fa"]');
        if (!source || !target) { return; }

        var touched = !!target.value; // don't overwrite a slug the admin already set
        function slugify(str) {
            return String(str)
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .slice(0, 150);
        }
        source.addEventListener('input', function () {
            if (touched) { return; }
            var s = slugify(source.value);
            target.value = s;
            if (faTarget && !faTarget.value) { faTarget.value = s; }
        });
        target.addEventListener('input', function () { touched = !!target.value; });
    }
})();
