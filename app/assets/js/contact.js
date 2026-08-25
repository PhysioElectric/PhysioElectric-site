(function() {
    'use strict';
    
    var LANG = window.PE_CONTACT_LANG || 'fa';
    var DICT = window.PE_CONTACT_DICT || {};
    var stepsCount = 6;
    var currentStep = 0;
    var selectedCategories = [];

    /* ============ BLUEPRINT (Sidebar) ============ */
    function initBlueprint() {
        var ul = document.getElementById('blueprint-list');
        if (!ul) return;
        ul.innerHTML = '';
        ul.setAttribute('data-progress', '0');
        
        for (var i = 0; i < stepsCount; i++) {
            var li = document.createElement('li');
            li.className = 'relative flex items-center gap-4';
            li.style.position = 'relative';
            li.style.zIndex = '2';
            
            var dot = document.createElement('div');
            dot.id = 'bp-dot-' + i;
            dot.className = 'w-7 h-7 rounded-full border-2 bg-white flex items-center justify-center transition-all duration-300 flex-shrink-0 ' + (i === 0 ? 'border-physio-500' : 'border-slate-300');
            dot.style.cssText = 'width: 28px; height: 28px; border-radius: 50%; border: 2px solid #cbd5e1; background: #ffffff; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; flex-shrink: 0;';
            
            if (i === 0) {
                dot.style.borderColor = '#0ea5e9';
                dot.innerHTML = '<div style="width: 10px; height: 10px; background: #0ea5e9; border-radius: 50%;"></div>';
            }
            
            var text = document.createElement('span');
            text.id = 'bp-text-' + i;
            text.className = 'text-sm transition-all duration-300';
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
    }

    function updateBlueprint() {
        for (var i = 0; i < stepsCount; i++) {
            var dot = document.getElementById('bp-dot-' + i);
            var text = document.getElementById('bp-text-' + i);
            if (!dot || !text) continue;
            
            if (i < currentStep) {
                // COMPLETED - Green with check
                dot.style.borderColor = '#22c55e';
                dot.style.backgroundColor = '#22c55e';
                dot.style.boxShadow = '0 0 10px rgba(34, 197, 94, 0.4)';
                dot.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                text.style.color = '#1e293b';
                text.style.fontWeight = 'bold';
            } else if (i === currentStep) {
                // ACTIVE - Blue with inner dot
                dot.style.borderColor = '#0ea5e9';
                dot.style.backgroundColor = '#ffffff';
                dot.style.boxShadow = '0 0 0 4px rgba(14, 165, 233, 0.15)';
                dot.innerHTML = '<div style="width: 10px; height: 10px; background: #0ea5e9; border-radius: 50%;"></div>';
                text.style.color = '#0284c7';
                text.style.fontWeight = 'bold';
            } else {
                // FUTURE - Gray
                dot.style.borderColor = '#cbd5e1';
                dot.style.backgroundColor = '#ffffff';
                dot.style.boxShadow = 'none';
                dot.innerHTML = '';
                text.style.color = '#94a3b8';
                text.style.fontWeight = '500';
            }
        }
        
        // Update progress line
        var blueprintList = document.getElementById('blueprint-list');
        if (blueprintList) {
            blueprintList.setAttribute('data-progress', currentStep);
        }
        
        // Mobile progress
        var mobileText = document.getElementById('mobile-step-text');
        var mobilePercent = document.getElementById('mobile-percent-text');
        var mobileBar = document.getElementById('mobile-progress-bar');
        
        if (mobileText) mobileText.innerText = (DICT.stepWord || 'Step') + ' ' + (currentStep + 1) + ' / ' + stepsCount;
        if (mobilePercent) mobilePercent.innerText = Math.round(((currentStep + 1) / stepsCount) * 100) + '%';
        if (mobileBar) mobileBar.style.width = Math.round(((currentStep + 1) / stepsCount) * 100) + '%';
    }

    /* ============ SHOW STEP ============ */
    function showStep(index) {
        // Hide all steps
        var allSteps = document.querySelectorAll('.form-step');
        for (var i = 0; i < allSteps.length; i++) {
            allSteps[i].classList.remove('active');
            allSteps[i].style.display = 'none';
        }
        
        // Show target step
        var targetStep = document.getElementById('step-' + (index + 1));
        if (targetStep) {
            targetStep.classList.add('active');
            targetStep.style.display = 'block';
        }
        
        currentStep = index;
        updateBlueprint();
        updateNavButtons();
        
        // Scroll to form
        var formContainer = document.getElementById('form-container');
        if (formContainer) {
            var formTop = formContainer.getBoundingClientRect().top + window.scrollY - 120;
            window.scrollTo({ top: formTop, behavior: 'smooth' });
        }
    }

    /* ============ NAV BUTTONS ============ */
    function updateNavButtons() {
        var btnPrev = document.getElementById('btn-prev');
        var btnNext = document.getElementById('btn-next');
        var nextText = document.getElementById('btn-next-text');
        
        if (!btnPrev || !btnNext || !nextText) return;
        
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

    /* ============ VALIDATION ============ */
    function validateStep(step) {
        var isValid = true;
        
        if (step === 0) {
            // Step 1: Category
            if (selectedCategories.length === 0) {
                showError('step-1-error', true);
                isValid = false;
            } else {
                showError('step-1-error', false);
            }
        } 
        else if (step === 1) {
            // Step 2: Client Info
            var name = document.getElementById('inp_name');
            var email = document.getElementById('inp_email');
            var phone = document.getElementById('inp_phone');
            
            if (!name.value.trim()) {
                markError(name, 'err_name', true);
                isValid = false;
            } else {
                markError(name, 'err_name', false);
            }
            
            if (!email.value.trim() || email.value.indexOf('@') === -1) {
                markError(email, 'err_email', true);
                isValid = false;
            } else {
                markError(email, 'err_email', false);
            }
            
            if (!phone.value.trim()) {
                markError(phone, 'err_phone', true);
                isValid = false;
            } else {
                markError(phone, 'err_phone', false);
            }
            
            var methodChecked = document.querySelector('input[name="contactMethod"]:checked');
            if (methodChecked && methodChecked.value === 'Telegram') {
                var tgId = document.getElementById('inp_contact_id');
                if (!tgId.value.trim()) {
                    markError(tgId, 'err_contact_id', true);
                    isValid = false;
                } else {
                    markError(tgId, 'err_contact_id', false);
                }
            }
        } 
        else if (step === 2) {
            // Step 3: Description
            var desc = document.getElementById('inp_desc');
            if (!desc.value.trim()) {
                markError(desc, 'err_desc', true);
                isValid = false;
            } else {
                markError(desc, 'err_desc', false);
            }
        } 
        else if (step === 5) {
            // Step 6: Consent
            var consent = document.getElementById('consent_check');
            if (!consent.checked) {
                showError('err_consent', true);
                isValid = false;
            } else {
                showError('err_consent', false);
            }
        }
        
        return isValid;
    }

    function markError(input, errId, isError) {
        if (input) {
            if (isError) {
                input.classList.add('error-field');
            } else {
                input.classList.remove('error-field');
            }
        }
        var errEl = document.getElementById(errId);
        if (errEl) {
            errEl.style.display = isError ? 'block' : 'none';
        }
    }

    function showError(errId, isError) {
        var errEl = document.getElementById(errId);
        if (errEl) {
            errEl.style.display = isError ? 'flex' : 'none';
        }
    }

    /* ============ REVIEW POPULATION ============ */
    function populateReview() {
        var catDisplay = selectedCategories.join('، ');
        document.getElementById('rev_type').innerText = catDisplay || '-';
        
        var name = document.getElementById('inp_name').value;
        var comp = document.getElementById('inp_company').value;
        var email = document.getElementById('inp_email').value;
        var phone = document.getElementById('inp_phone').value;
        var methodChecked = document.querySelector('input[name="contactMethod"]:checked');
        var methodVal = methodChecked ? methodChecked.value : '';
        
        var clientHtml = '<strong>' + name + '</strong>';
        if (comp) clientHtml += ' (' + comp + ')';
        clientHtml += '<br>' + email + '<br>' + phone;
        clientHtml += '<br><span style="color: #0284c7; font-size: 0.75rem;">' + methodVal + '</span>';
        
        document.getElementById('rev_client').innerHTML = clientHtml;
        document.getElementById('rev_desc').innerText = document.getElementById('inp_desc').value;
        
        var timeChecked = document.querySelector('input[name="timeline"]:checked');
        var timeVal = timeChecked ? timeChecked.nextElementSibling.innerText : '';
        document.getElementById('rev_time').innerText = timeVal;
        
        var notes = document.getElementById('inp_notes').value;
        document.getElementById('rev_notes').innerText = notes || '-';
    }

    /* ============ INIT ============ */
    function init() {
        initBlueprint();
        
        // Hide all steps first
        var allSteps = document.querySelectorAll('.form-step');
        for (var i = 0; i < allSteps.length; i++) {
            allSteps[i].classList.remove('active');
            allSteps[i].style.display = 'none';
        }
        
        // Show only step 1
        var firstStep = document.getElementById('step-1');
        if (firstStep) {
            firstStep.classList.add('active');
            firstStep.style.display = 'block';
        }
        
        currentStep = 0;
        updateBlueprint();
        updateNavButtons();
        
        /* --- Category Selection --- */
        var cards = document.querySelectorAll('.selection-card');
        for (var i = 0; i < cards.length; i++) {
            (function(card) {
                card.addEventListener('click', function() {
                    var val = this.getAttribute('data-value');
                    var checkIcon = this.querySelector('.check-circle i, .check-circle svg');
                    var iconWrap = this.querySelector('.icon-wrap');
                    
                    if (this.classList.contains('selected')) {
                        // Deselect
                        this.classList.remove('selected');
                        if (checkIcon) checkIcon.style.display = 'none';
                        if (iconWrap) {
                            iconWrap.style.backgroundColor = '#f8fafc';
                            iconWrap.style.color = '#475569';
                        }
                        var idx = selectedCategories.indexOf(val);
                        if (idx > -1) selectedCategories.splice(idx, 1);
                    } else {
                        // Select
                        this.classList.add('selected');
                        if (checkIcon) checkIcon.style.display = 'block';
                        if (iconWrap) {
                            iconWrap.style.backgroundColor = '#0ea5e9';
                            iconWrap.style.color = '#ffffff';
                        }
                        selectedCategories.push(val);
                    }
                    
                    var err = document.getElementById('step-1-error');
                    if (err && selectedCategories.length > 0) err.style.display = 'none';
                });
            })(cards[i]);
        }

        /* --- Next Button --- */
        var btnNext = document.getElementById('btn-next');
        if (btnNext) {
            btnNext.addEventListener('click', function() {
                if (!validateStep(currentStep)) {
                    return;
                }
                
                if (currentStep < stepsCount - 1) {
                    if (currentStep === stepsCount - 2) {
                        populateReview();
                    }
                    showStep(currentStep + 1);
                } else {
                    // Final submit
                    var nav = document.getElementById('form-navigation');
                    if (nav) nav.style.display = 'none';
                    
                    var steps = document.querySelectorAll('.form-step');
                    for (var j = 0; j < steps.length; j++) {
                        steps[j].classList.remove('active');
                        steps[j].style.display = 'none';
                    }
                    
                    var success = document.getElementById('step-success');
                    if (success) {
                        success.classList.add('active');
                        success.style.display = 'block';
                    }
                    
                    var ul = document.getElementById('blueprint-list');
                    if (ul) {
                        ul.innerHTML = '<li style="display: flex; align-items: center; gap: 1rem;"><div style="width: 32px; height: 32px; border-radius: 50%; background: #22c55e; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 15px rgba(34, 197, 94, 0.5);"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></div><span style="font-weight: bold; color: #16a34a;">' + (DICT.ready || 'READY') + '</span></li>';
                    }
                }
            });
        }

        /* --- Prev Button --- */
        var btnPrev = document.getElementById('btn-prev');
        if (btnPrev) {
            btnPrev.addEventListener('click', function() {
                if (currentStep > 0) {
                    showStep(currentStep - 1);
                }
            });
        }

        /* --- Edit Buttons --- */
        var editBtns = document.querySelectorAll('.edit-btn');
        for (var e = 0; e < editBtns.length; e++) {
            (function(btn) {
                btn.addEventListener('click', function() {
                    showStep(parseInt(this.getAttribute('data-step')));
                });
            })(editBtns[e]);
        }

        /* --- Char Counter --- */
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

        /* --- Clear Errors on Input --- */
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

        /* --- Contact Method Toggle --- */
        var radios = document.querySelectorAll('input[name="contactMethod"]');
        for (var r = 0; r < radios.length; r++) {
            (function(radio) {
                radio.addEventListener('change', function() {
                    var wrapper = document.getElementById('dynamic-contact-wrapper');
                    if (wrapper) {
                        if (this.value === 'Telegram') {
                            wrapper.style.display = 'block';
                        } else {
                            wrapper.style.display = 'none';
                        }
                    }
                });
            })(radios[r]);
        }

        /* --- Consent --- */
        var consent = document.getElementById('consent_check');
        if (consent) {
            consent.addEventListener('change', function() {
                var errConsent = document.getElementById('err_consent');
                if (errConsent) errConsent.style.display = 'none';
            });
        }

        /* --- File Upload --- */
        var dropzone = document.getElementById('dropzone');
        var fileInput = document.getElementById('file_input');
        var fileList = document.getElementById('file-list');
        
        if (dropzone && fileInput) {
            dropzone.addEventListener('click', function() {
                fileInput.click();
            });
            
            fileInput.addEventListener('change', function() {
                if (fileList) {
                    fileList.innerHTML = '';
                    for (var f = 0; f < this.files.length; f++) {
                        var div = document.createElement('div');
                        div.style.cssText = 'display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.875rem;';
                        div.innerHTML = '<span>' + this.files[f].name + '</span><span style="color: #94a3b8; font-size: 0.75rem;">' + (this.files[f].size / 1024).toFixed(1) + ' KB</span>';
                        fileList.appendChild(div);
                    }
                }
            });
        }
    }

    /* ============ START ============ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();