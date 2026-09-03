(function() {
    'use strict';
    
    var LANG = window.PE_CONTACT_LANG || 'fa';
    var DICT = window.PE_CONTACT_DICT || {};
    // Optional CAPTCHA (Cloudflare Turnstile) — injected by contact.php only
    // when the server-side CAPTCHA_* config is enabled.
    var captchaEnabled = (window.PE_CONTACT_CAPTCHA === true || window.PE_CONTACT_CAPTCHA === '1' || window.PE_CONTACT_CAPTCHA === 1);
    var stepsCount = 6;
    var currentStep = 0;
    var selectedCategories = [];

    /* ========================================
       BLUEPRINT TIMELINE
       ======================================== */
    function initBlueprint() {
        var ul = document.getElementById('blueprint-list');
        if (!ul) return;
        ul.innerHTML = '';
        ul.style.cssText = 'position: relative; padding: 0; margin: 0; list-style: none;';
        
        // ===== TIMELINE LINE CONTAINER =====
        var lineContainer = document.createElement('div');
        lineContainer.style.cssText = 'position: absolute; top: 20px; bottom: 20px; left: 13px; width: 2px; z-index: 0;';
        
        // Gray base line
        var grayLine = document.createElement('div');
        grayLine.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #e2e8f0; border-radius: 2px;';
        lineContainer.appendChild(grayLine);
        
        // Blue progress line
        var blueLine = document.createElement('div');
        blueLine.id = 'blueprint-progress-line';
        blueLine.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 0%; background: linear-gradient(180deg, #0ea5e9, #38bdf8); border-radius: 2px; transition: height 0.6s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 10px rgba(14, 165, 233, 0.5);';
        lineContainer.appendChild(blueLine);
        
        ul.appendChild(lineContainer);
        
        // RTL support
        if (document.documentElement.getAttribute('dir') === 'rtl') {
            lineContainer.style.left = 'auto';
            lineContainer.style.right = '13px';
        }
        
        // ===== STEP ITEMS =====
        for (var i = 0; i < stepsCount; i++) {
            var li = document.createElement('li');
            li.style.cssText = 'position: relative; display: flex; align-items: center; gap: 14px; z-index: 2; padding: 10px 0;';
            
            // Dot
            var dot = document.createElement('div');
            dot.id = 'bp-dot-' + i;
            dot.style.cssText = 'width: 28px; height: 28px; border-radius: 50%; border: 2px solid #cbd5e1; background: #ffffff; display: flex; align-items: center; justify-content: center; transition: all 0.4s ease; flex-shrink: 0;';
            
            if (i === 0) {
                dot.style.borderColor = '#0ea5e9';
                dot.style.boxShadow = '0 0 0 4px rgba(14, 165, 233, 0.2)';
                dot.innerHTML = '<div style="width: 10px; height: 10px; background: #0ea5e9; border-radius: 50%;"></div>';
            }
            
            // Text
            var text = document.createElement('span');
            text.id = 'bp-text-' + i;
            text.style.cssText = 'font-size: 0.875rem; transition: all 0.3s ease;';
            
            if (i === 0) {
                text.style.color = '#0284c7';
                text.style.fontWeight = 'bold';
            } else {
                text.style.color = '#94a3b8';
                text.style.fontWeight = '500';
            }
            
            text.innerText = DICT.bpLabels ? DICT.bpLabels[i] : ('Step ' + (i + 1));
            
            li.appendChild(dot);
            li.appendChild(text);
            ul.appendChild(li);
        }
        
        updateBlueprint();
    }

    function updateBlueprint() {
        // Update dots
        for (var i = 0; i < stepsCount; i++) {
            var dot = document.getElementById('bp-dot-' + i);
            var text = document.getElementById('bp-text-' + i);
            if (!dot || !text) continue;
            
            if (i < currentStep) {
                // COMPLETED
                dot.style.borderColor = '#22c55e';
                dot.style.backgroundColor = '#22c55e';
                dot.style.boxShadow = '0 0 12px rgba(34, 197, 94, 0.5)';
                dot.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                text.style.color = '#1e293b';
                text.style.fontWeight = 'bold';
            } else if (i === currentStep) {
                // ACTIVE
                dot.style.borderColor = '#0ea5e9';
                dot.style.backgroundColor = '#ffffff';
                dot.style.boxShadow = '0 0 0 5px rgba(14, 165, 233, 0.15), 0 0 15px rgba(14, 165, 233, 0.3)';
                dot.innerHTML = '<div style="width: 10px; height: 10px; background: #0ea5e9; border-radius: 50%;"></div>';
                text.style.color = '#0284c7';
                text.style.fontWeight = 'bold';
            } else {
                // FUTURE
                dot.style.borderColor = '#cbd5e1';
                dot.style.backgroundColor = '#ffffff';
                dot.style.boxShadow = 'none';
                dot.innerHTML = '';
                text.style.color = '#94a3b8';
                text.style.fontWeight = '500';
            }
        }
        
        // Update progress line
        var progressLine = document.getElementById('blueprint-progress-line');
        if (progressLine) {
            var progressPercent = (currentStep / (stepsCount - 1)) * 100;
            progressLine.style.height = progressPercent + '%';
        }
        
        // Mobile progress
        var mobileText = document.getElementById('mobile-step-text');
        var mobilePercent = document.getElementById('mobile-percent-text');
        var mobileBar = document.getElementById('mobile-progress-bar');
        
        if (mobileText) mobileText.innerText = (DICT.stepWord || 'Step') + ' ' + (currentStep + 1) + ' / ' + stepsCount;
        if (mobilePercent) mobilePercent.innerText = Math.round(((currentStep + 1) / stepsCount) * 100) + '%';
        if (mobileBar) mobileBar.style.width = Math.round(((currentStep + 1) / stepsCount) * 100) + '%';
    }

    /* ========================================
       SHOW STEP
       ======================================== */
    function showStep(index) {
        var allSteps = document.querySelectorAll('.form-step');
        for (var i = 0; i < allSteps.length; i++) {
            allSteps[i].classList.remove('active');
            allSteps[i].style.display = 'none';
        }
        
        var targetStep = document.getElementById('step-' + (index + 1));
        if (targetStep) {
            targetStep.classList.add('active');
            targetStep.style.display = 'block';
        }
        
        currentStep = index;
        updateBlueprint();
        updateNavButtons();
        
        var formContainer = document.getElementById('form-container');
        if (formContainer) {
            var formTop = formContainer.getBoundingClientRect().top + window.scrollY - 120;
            window.scrollTo({ top: formTop, behavior: 'smooth' });
        }
    }

    /* ========================================
       NAV BUTTONS
       ======================================== */
    function updateNavButtons() {
        var btnPrev = document.getElementById('btn-prev');
        var nextText = document.getElementById('btn-next-text');
        
        if (currentStep === 0) {
            btnPrev.style.display = 'none';
        } else {
            btnPrev.style.display = 'flex';
        }
        
        if (currentStep === stepsCount - 2) {
            nextText.innerText = DICT.review || 'Review';
        } else if (currentStep === stepsCount - 1) {
            nextText.innerText = DICT.submit || 'Submit';
        } else {
            nextText.innerText = DICT.next || 'Next';
        }
    }

    /* ========================================
       VALIDATION
       ======================================== */
    function validateStep(step) {
        var isValid = true;
        
        if (step === 0) {
            if (selectedCategories.length === 0) {
                var err = document.getElementById('step-1-error');
                if (err) err.style.display = 'flex';
                isValid = false;
            } else {
                var err2 = document.getElementById('step-1-error');
                if (err2) err2.style.display = 'none';
            }
        } 
        else if (step === 1) {
            var name = document.getElementById('inp_name');
            var email = document.getElementById('inp_email');
            var phone = document.getElementById('inp_phone');
            
            if (!name.value.trim()) {
                name.classList.add('error-field');
                document.getElementById('err_name').style.display = 'block';
                isValid = false;
            } else {
                name.classList.remove('error-field');
                document.getElementById('err_name').style.display = 'none';
            }
            
            if (!isEmail(email.value)) {
                email.classList.add('error-field');
                document.getElementById('err_email').style.display = 'block';
                isValid = false;
            } else {
                email.classList.remove('error-field');
                document.getElementById('err_email').style.display = 'none';
            }
            
            if (!phone.value.trim()) {
                phone.classList.add('error-field');
                document.getElementById('err_phone').style.display = 'block';
                isValid = false;
            } else {
                phone.classList.remove('error-field');
                document.getElementById('err_phone').style.display = 'none';
            }
            
            var methodChecked = document.querySelector('input[name="contactMethod"]:checked');
            if (methodChecked && methodChecked.value === 'Telegram') {
                var tgId = document.getElementById('inp_contact_id');
                if (!tgId.value.trim()) {
                    tgId.classList.add('error-field');
                    document.getElementById('err_contact_id').style.display = 'block';
                    isValid = false;
                } else {
                    tgId.classList.remove('error-field');
                    document.getElementById('err_contact_id').style.display = 'none';
                }
            }
        } 
        else if (step === 2) {
            var desc = document.getElementById('inp_desc');
            if (!desc.value.trim()) {
                desc.classList.add('error-field');
                document.getElementById('err_desc').style.display = 'block';
                isValid = false;
            } else {
                desc.classList.remove('error-field');
                document.getElementById('err_desc').style.display = 'none';
            }
        } 
        else if (step === 5) {
            var consent = document.getElementById('consent_check');
            if (!consent.checked) {
                document.getElementById('err_consent').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('err_consent').style.display = 'none';
            }
        }
        
        return isValid;
    }

    /* ========================================
       CATEGORY SELECTION - STEP 1
       ======================================== */
    function initCategorySelection() {
        var cards = document.querySelectorAll('.selection-card');
        
        for (var i = 0; i < cards.length; i++) {
            (function(card) {
                card.addEventListener('click', function() {
                    var val = this.getAttribute('data-value');
                    var checkCircle = this.querySelector('.check-circle');
                    var checkIcon = this.querySelector('.check-circle i, .check-circle svg');
                    var iconWrap = this.querySelector('.icon-wrap');
                    
                    if (this.classList.contains('selected')) {
                        // DESELECT
                        this.classList.remove('selected');
                        
                        if (checkCircle) {
                            checkCircle.style.backgroundColor = '#ffffff';
                            checkCircle.style.borderColor = '#cbd5e1';
                            checkCircle.style.boxShadow = 'none';
                        }
                        if (checkIcon) {
                            checkIcon.style.display = 'none';
                        }
                        if (iconWrap) {
                            iconWrap.style.backgroundColor = '#f8fafc';
                            iconWrap.style.color = '#475569';
                            iconWrap.style.borderColor = '#f1f5f9';
                            iconWrap.style.boxShadow = 'none';
                        }
                        
                        var idx = selectedCategories.indexOf(val);
                        if (idx > -1) selectedCategories.splice(idx, 1);
                        
                    } else {
                        // SELECT
                        this.classList.add('selected');
                        
                        if (checkCircle) {
                            checkCircle.style.backgroundColor = '#0ea5e9';
                            checkCircle.style.borderColor = '#0ea5e9';
                            checkCircle.style.boxShadow = '0 0 10px rgba(14, 165, 233, 0.5)';
                        }
                        if (checkIcon) {
                            checkIcon.style.display = 'block';
                            checkIcon.style.color = '#ffffff';
                            checkIcon.style.opacity = '1';
                        }
                        if (iconWrap) {
                            iconWrap.style.backgroundColor = '#0ea5e9';
                            iconWrap.style.color = '#ffffff';
                            iconWrap.style.borderColor = '#0ea5e9';
                            iconWrap.style.boxShadow = '0 4px 12px rgba(14, 165, 233, 0.4)';
                        }
                        
                        selectedCategories.push(val);
                    }
                    
                    var err = document.getElementById('step-1-error');
                    if (err && selectedCategories.length > 0) {
                        err.style.display = 'none';
                    }
                });
            })(cards[i]);
        }
    }

    /* ========================================
       REVIEW POPULATION
       ======================================== */
    function populateReview() {
        var catDisplay = selectedCategories.join('، ');
        document.getElementById('rev_type').innerText = catDisplay || '-';
        
        var name = document.getElementById('inp_name').value;
        var comp = document.getElementById('inp_company').value;
        var email = document.getElementById('inp_email').value;
        var phone = document.getElementById('inp_phone').value;
        var methodChecked = document.querySelector('input[name="contactMethod"]:checked');
        var methodVal = methodChecked ? methodChecked.value : '';
        
        // Built with DOM APIs (never innerHTML) so user input can not inject markup (XSS).
        var revClient = document.getElementById('rev_client');
        revClient.textContent = '';
        var strong = document.createElement('strong');
        strong.textContent = name;
        revClient.appendChild(strong);
        if (comp) revClient.appendChild(document.createTextNode(' (' + comp + ')'));
        revClient.appendChild(document.createElement('br'));
        revClient.appendChild(document.createTextNode(email));
        revClient.appendChild(document.createElement('br'));
        revClient.appendChild(document.createTextNode(phone));
        revClient.appendChild(document.createElement('br'));
        var methodSpan = document.createElement('span');
        methodSpan.style.cssText = 'color: #0284c7; font-size: 0.75rem;';
        methodSpan.textContent = methodVal;
        revClient.appendChild(methodSpan);
        
        document.getElementById('rev_desc').innerText = document.getElementById('inp_desc').value;
        
        var timeChecked = document.querySelector('input[name="timeline"]:checked');
        var timeVal = timeChecked ? timeChecked.nextElementSibling.innerText : '';
        document.getElementById('rev_time').innerText = timeVal;
        
        var notes = document.getElementById('inp_notes').value;
        document.getElementById('rev_notes').innerText = notes || '-';
    }

    /* ========================================
       SUBMIT TO BACKEND (received-messages inbox)
       ======================================== */
    function gv(id) {
        var el = document.getElementById(id);
        return el ? el.value : '';
    }

    // Mirrors the server's FILTER_VALIDATE_EMAIL closely enough that anything
    // the wizard accepts here will also be accepted on submit (no dead-end).
    function isEmail(v) {
        v = (v || '').trim();
        return /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(v);
    }

    // Turnstile writes the solved token into a hidden input named
    // cf-turnstile-response inside the widget container.
    function getCaptchaToken() {
        var inp = document.querySelector('input[name="cf-turnstile-response"]');
        return inp ? (inp.value || '') : '';
    }

    function submitInquiry() {
        var fd = new FormData();
        fd.append('csrf_token', gv('inp_csrf'));
        // Honeypot: left empty by humans; bots fill it and get dropped server-side.
        fd.append('website', gv('inp_website'));

        // Optional CAPTCHA: the server refuses submissions without a solved
        // token, so never fire the request when the widget is unsolved.
        if (captchaEnabled) {
            var capToken = getCaptchaToken();
            if (!capToken) {
                return Promise.resolve({ ok: false, status: 0, code: 'captcha' });
            }
            fd.append('cf-turnstile-response', capToken);
        }

        fd.append('lang', LANG);
        fd.append('kind', 'project');
        fd.append('categories', selectedCategories.join(', '));
        fd.append('name', gv('inp_name'));
        fd.append('company', gv('inp_company'));
        fd.append('email', gv('inp_email'));
        fd.append('phone', gv('inp_phone'));

        var methodChecked = document.querySelector('input[name="contactMethod"]:checked');
        fd.append('contact_method', methodChecked ? methodChecked.value : '');
        fd.append('contact_id', gv('inp_contact_id'));

        var timeChecked = document.querySelector('input[name="timeline"]:checked');
        fd.append('timeline', (timeChecked && timeChecked.nextElementSibling) ? timeChecked.nextElementSibling.innerText : '');
        fd.append('body', gv('inp_desc'));
        fd.append('notes', gv('inp_notes'));

        var fileInput = document.getElementById('file_input');
        if (fileInput && fileInput.files) {
            for (var i = 0; i < fileInput.files.length; i++) {
                fd.append('files[]', fileInput.files[i], fileInput.files[i].name);
            }
        }

        var lang = (location.pathname.split('/')[1] === 'en') ? 'en' : 'fa';
        // Resolve to true only when the server actually stored the request.
        return fetch('/' + lang + '/inquiry', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': gv('inp_csrf') } })
            .then(function (r) {
                return r.json().then(function (j) {
                    return { ok: !!(r.ok && j && j.ok === true), status: r.status, code: (j && j.code) || '' };
                }).catch(function () { return { ok: false, status: r.status, code: '' }; });
            })
            .catch(function () { return { ok: false, status: 0, code: 'network' }; });
    }

    function showSubmitError(res) {
        var holder = document.getElementById('form-container');
        if (!holder) return;
        var box = document.createElement('div');
        box.className = 'bg-red-50 border border-red-200 text-red-600 rounded-2xl p-8 text-center';
        var msg = document.createElement('p');
        msg.className = 'font-bold text-lg leading-relaxed';
        var fallback = (LANG === 'en')
            ? 'Sending the request failed. Please try again, or contact us via Telegram.'
            : 'ارسال درخواست با خطا مواجه شد. لطفاً دوباره تلاش کنید یا از طریق تلگرام تماس بگیرید.';
        if (res && res.code === 'captcha') {
            var capFallback = (LANG === 'en')
                ? 'Please complete the security check ("I am not a robot") and try again.'
                : 'لطفاً تیک «من ربات نیستم» را بزنید و دوباره تلاش کنید.';
            msg.textContent = DICT.captcha || capFallback;
        } else {
            msg.textContent = DICT.submitError || fallback;
        }
        box.appendChild(msg);
        // Diagnostic detail so any environment-specific failure is visible in a
        // screenshot (status 0 = network/CORS, 500 = server/schema, 429 = rate).
        if (res) {
            var d = document.createElement('p');
            d.dir = 'ltr';
            d.style.cssText = 'direction:ltr;font-size:0.75rem;color:#94a3b8;margin-top:0.5rem;';
            d.textContent = 'status=' + res.status + (res.code ? ' code=' + res.code : '');
            box.appendChild(d);
        }
        var retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'mt-5 px-6 py-3 rounded-full bg-physio-900 text-white font-bold';
        retry.textContent = DICT.next || 'OK';
        retry.addEventListener('click', function () { location.reload(); });
        box.appendChild(retry);
        holder.appendChild(box);
    }

    /* ========================================
       INIT
       ======================================== */
    function init() {
        initBlueprint();
        
        // Hide all steps
        var allSteps = document.querySelectorAll('.form-step');
        for (var i = 0; i < allSteps.length; i++) {
            allSteps[i].classList.remove('active');
            allSteps[i].style.display = 'none';
        }
        
        // Show step 1
        var firstStep = document.getElementById('step-1');
        if (firstStep) {
            firstStep.classList.add('active');
            firstStep.style.display = 'block';
        }
        
        currentStep = 0;
        updateBlueprint();
        updateNavButtons();
        
        initCategorySelection();

        // Next
        document.getElementById('btn-next').addEventListener('click', function() {
            if (!validateStep(currentStep)) return;
            
            if (currentStep < stepsCount - 1) {
                if (currentStep === stepsCount - 2) populateReview();
                showStep(currentStep + 1);
            } else {
                document.getElementById('form-navigation').style.display = 'none';
                submitInquiry().then(function (res) {
                    var steps = document.querySelectorAll('.form-step');
                    for (var j = 0; j < steps.length; j++) {
                        steps[j].classList.remove('active');
                        steps[j].style.display = 'none';
                    }
                    if (res && res.ok) {
                        var success = document.getElementById('step-success');
                        if (success) {
                            success.classList.add('active');
                            success.style.display = 'block';
                        }
                    } else {
                        showSubmitError(res);
                    }
                });
            }
        });

        // Prev
        document.getElementById('btn-prev').addEventListener('click', function() {
            if (currentStep > 0) showStep(currentStep - 1);
        });

        // Edit buttons
        var editBtns = document.querySelectorAll('.edit-btn');
        for (var e = 0; e < editBtns.length; e++) {
            (function(btn) {
                btn.addEventListener('click', function() {
                    showStep(parseInt(this.getAttribute('data-step')));
                });
            })(editBtns[e]);
        }

        // Char counter
        var descInput = document.getElementById('inp_desc');
        var charCounter = document.getElementById('char-counter');
        if (descInput && charCounter) {
            descInput.addEventListener('input', function() {
                charCounter.innerText = this.value.length + ' / 3000';
                if (this.value.length > 0) {
                    this.classList.remove('error-field');
                    var errDesc = document.getElementById('err_desc');
                    if (errDesc) errDesc.style.display = 'none';
                }
            });
        }

        // Clear errors
        var clearOnInput = ['inp_name', 'inp_email', 'inp_phone', 'inp_contact_id'];
        for (var n = 0; n < clearOnInput.length; n++) {
            (function(id) {
                var el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function() {
                        this.classList.remove('error-field');
                        var errId = id.replace('inp_', 'err_');
                        var errEl = document.getElementById(errId);
                        if (errEl) errEl.style.display = 'none';
                    });
                }
            })(clearOnInput[n]);
        }

        // Contact method
        var radios = document.querySelectorAll('input[name="contactMethod"]');
        for (var r = 0; r < radios.length; r++) {
            (function(radio) {
                radio.addEventListener('change', function() {
                    var wrapper = document.getElementById('dynamic-contact-wrapper');
                    if (wrapper) wrapper.style.display = this.value === 'Telegram' ? 'block' : 'none';
                });
            })(radios[r]);
        }

        // Consent
        var consent = document.getElementById('consent_check');
        if (consent) {
            consent.addEventListener('change', function() {
                var errConsent = document.getElementById('err_consent');
                if (errConsent) errConsent.style.display = 'none';
            });
        }

        // File upload
        var dropzone = document.getElementById('dropzone');
        var fileInput = document.getElementById('file_input');
        var fileList = document.getElementById('file-list');
        
        if (dropzone && fileInput) {
            dropzone.addEventListener('click', function() { fileInput.click(); });
            fileInput.addEventListener('change', function() {
                // Mirror the server-side attachment policy so a bad file is
                // rejected here instead of failing the final submit.
                var MAX_FILES = 3, MAX_BYTES = 2 * 1024 * 1024;
                var ALLOWED = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'zip'];
                var problem = '';
                if (this.files.length > MAX_FILES) {
                    problem = (LANG === 'en') ? 'You can attach at most 3 files.' : 'حداکثر ۳ فایل می‌توانید پیوست کنید.';
                } else {
                    for (var c = 0; c < this.files.length; c++) {
                        var ext = (this.files[c].name.split('.').pop() || '').toLowerCase();
                        if (ALLOWED.indexOf(ext) === -1) {
                            problem = (LANG === 'en') ? 'Allowed file types: pdf, doc, docx, png, jpg, zip.' : 'فرمت‌های مجاز: pdf، doc، docx، png، jpg، zip.';
                            break;
                        }
                        if (this.files[c].size > MAX_BYTES) {
                            problem = (LANG === 'en') ? 'Each file must be 2 MB or smaller.' : 'حجم هر فایل باید حداکثر ۲ مگابایت باشد.';
                            break;
                        }
                    }
                }
                if (fileList) fileList.innerHTML = '';
                if (problem) {
                    this.value = '';
                    if (fileList) {
                        var warn = document.createElement('div');
                        warn.style.cssText = 'padding: 0.75rem 1rem; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; border-radius: 0.75rem; font-size: 0.875rem;';
                        warn.textContent = problem;
                        fileList.appendChild(warn);
                    }
                    return;
                }
                if (fileList) {
                    for (var f = 0; f < this.files.length; f++) {
                        var div = document.createElement('div');
                        div.style.cssText = 'display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.875rem;';
                        // textContent (not innerHTML): a crafted file name must never become markup.
                        var nameSpan = document.createElement('span');
                        nameSpan.textContent = this.files[f].name;
                        var sizeSpan = document.createElement('span');
                        sizeSpan.style.cssText = 'color: #94a3b8; font-size: 0.75rem;';
                        sizeSpan.textContent = (this.files[f].size / 1024).toFixed(1) + ' KB';
                        div.appendChild(nameSpan);
                        div.appendChild(sizeSpan);
                        fileList.appendChild(div);
                    }
                }
            });
        }
    }

    // Start
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();