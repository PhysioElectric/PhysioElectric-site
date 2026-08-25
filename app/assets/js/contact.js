(function() {
    'use strict';
    
    var LANG = window.PE_CONTACT_LANG || 'fa';
    var DICT = window.PE_CONTACT_DICT || {};
    var stepsCount = 6;
    var currentStep = 0;
    var selectedCategories = [];

    function initBlueprint() {
        var ul = document.getElementById('blueprint-list');
        if (!ul) return;
        ul.innerHTML = '';
        
        for (var i = 0; i < stepsCount; i++) {
            var li = document.createElement('li');
            li.className = 'flex items-center gap-4 relative';
            li.innerHTML = 
                '<div id="bp-dot-' + i + '" class="w-7 h-7 rounded-full border-2 bg-white flex items-center justify-center transition-all relative z-10 ' + (i === 0 ? 'border-physio-500' : 'border-slate-200') + '"></div>' +
                '<span id="bp-text-' + i + '" class="text-sm transition-all ' + (i === 0 ? 'text-physio-950 font-bold' : 'text-slate-400 font-medium') + '">' + (DICT.bpLabels ? DICT.bpLabels[i] : '') + '</span>';
            ul.appendChild(li);
        }
    }

    function updateBlueprint() {
        for (var i = 0; i < stepsCount; i++) {
            var dot = document.getElementById('bp-dot-' + i);
            var text = document.getElementById('bp-text-' + i);
            if (!dot || !text) continue;
            
            if (i < currentStep) {
                dot.className = 'w-7 h-7 rounded-full border-2 border-physio-500 bg-physio-500 flex items-center justify-center relative z-10';
                dot.innerHTML = '<i data-lucide="check" class="w-4 h-4 text-white"></i>';
                text.className = 'text-sm font-bold text-slate-800 transition-all';
            } else if (i === currentStep) {
                dot.className = 'w-7 h-7 rounded-full border-2 border-physio-500 bg-white flex items-center justify-center relative z-10';
                dot.innerHTML = '<div class="w-2.5 h-2.5 bg-physio-500 rounded-full"></div>';
                text.className = 'text-sm font-bold text-physio-600 transition-all';
            } else {
                dot.className = 'w-7 h-7 rounded-full border-2 border-slate-200 bg-white flex items-center justify-center relative z-10';
                dot.innerHTML = '';
                text.className = 'text-sm font-medium text-slate-400 transition-all';
            }
        }
        
        if (window.lucide) window.lucide.createIcons();
        
        var mobileText = document.getElementById('mobile-step-text');
        var mobilePercent = document.getElementById('mobile-percent-text');
        var mobileBar = document.getElementById('mobile-progress-bar');
        
        if (mobileText) mobileText.innerText = (DICT.stepWord || 'Step') + ' ' + (currentStep + 1) + ' / ' + stepsCount;
        if (mobilePercent) mobilePercent.innerText = Math.round(((currentStep + 1) / stepsCount) * 100) + '%';
        if (mobileBar) mobileBar.style.width = Math.round(((currentStep + 1) / stepsCount) * 100) + '%';
    }

    function showStep(index) {
        var steps = document.querySelectorAll('.form-step');
        for (var i = 0; i < steps.length; i++) {
            if (i === index) {
                steps[i].classList.add('active');
            } else {
                steps[i].classList.remove('active');
            }
        }
        currentStep = index;
        updateBlueprint();
        updateNav();
        
        var formContainer = document.getElementById('form-container');
        if (formContainer) {
            var formTop = formContainer.getBoundingClientRect().top + window.scrollY - 120;
            window.scrollTo({ top: formTop, behavior: 'smooth' });
        }
    }

    function updateNav() {
        var btnPrev = document.getElementById('btn-prev');
        var nextText = document.getElementById('btn-next-text');
        
        if (currentStep === 0) {
            btnPrev.classList.add('hidden');
        } else {
            btnPrev.classList.remove('hidden');
        }
        
        if (currentStep === stepsCount - 2) {
            nextText.innerText = DICT.review || 'Review';
        } else if (currentStep === stepsCount - 1) {
            nextText.innerText = DICT.submit || 'Submit';
        } else {
            nextText.innerText = DICT.next || 'Next';
        }
    }

    function validateStep(step) {
        var isValid = true;
        
        if (step === 0) {
            if (selectedCategories.length === 0) {
                var err = document.getElementById('step-1-error');
                if (err) err.style.display = 'flex';
                isValid = false;
            }
        } else if (step === 1) {
            var name = document.getElementById('inp_name');
            var email = document.getElementById('inp_email');
            var phone = document.getElementById('inp_phone');
            
            if (!name.value.trim()) {
                name.classList.add('error-field');
                document.getElementById('err_name').style.display = 'block';
                isValid = false;
            }
            if (!email.value.trim() || email.value.indexOf('@') === -1) {
                email.classList.add('error-field');
                document.getElementById('err_email').style.display = 'block';
                isValid = false;
            }
            if (!phone.value.trim()) {
                phone.classList.add('error-field');
                document.getElementById('err_phone').style.display = 'block';
                isValid = false;
            }
        } else if (step === 2) {
            var desc = document.getElementById('inp_desc');
            if (!desc.value.trim()) {
                desc.classList.add('error-field');
                document.getElementById('err_desc').style.display = 'block';
                isValid = false;
            }
        } else if (step === 5) {
            var consent = document.getElementById('consent_check');
            if (!consent.checked) {
                document.getElementById('err_consent').style.display = 'block';
                isValid = false;
            }
        }
        
        return isValid;
    }

    function populateReview() {
        var catNames = [];
        for (var i = 0; i < selectedCategories.length; i++) {
            catNames.push(selectedCategories[i]);
        }
        document.getElementById('rev_type').innerText = catNames.join(', ') || '-';
        
        var name = document.getElementById('inp_name').value;
        var comp = document.getElementById('inp_company').value;
        var email = document.getElementById('inp_email').value;
        var phone = document.getElementById('inp_phone').value;
        var method = document.querySelector('input[name="contactMethod"]:checked');
        var methodVal = method ? method.value : '';
        
        var clientHtml = '<strong>' + name + '</strong>';
        if (comp) clientHtml += ' (' + comp + ')';
        clientHtml += '<br>' + email + '<br>' + phone + '<br>' + methodVal;
        
        document.getElementById('rev_client').innerHTML = clientHtml;
        document.getElementById('rev_desc').innerText = document.getElementById('inp_desc').value;
        
        var timeChecked = document.querySelector('input[name="timeline"]:checked');
        var timeVal = timeChecked ? timeChecked.nextElementSibling.innerText : '';
        document.getElementById('rev_time').innerText = timeVal;
        
        var notes = document.getElementById('inp_notes').value || '-';
        document.getElementById('rev_notes').innerText = notes;
    }

    // Init
    function init() {
        initBlueprint();
        showStep(0);
        
        // Category selection
        var cards = document.querySelectorAll('.selection-card');
        for (var i = 0; i < cards.length; i++) {
            (function(card) {
                card.addEventListener('click', function() {
                    var val = this.getAttribute('data-value');
                    var icon = this.querySelector('.check-circle i');
                    
                    if (this.classList.contains('selected')) {
                        this.classList.remove('selected');
                        if (icon) icon.style.display = 'none';
                        var idx = selectedCategories.indexOf(val);
                        if (idx > -1) selectedCategories.splice(idx, 1);
                    } else {
                        this.classList.add('selected');
                        if (icon) icon.style.display = 'block';
                        selectedCategories.push(val);
                    }
                    
                    var err = document.getElementById('step-1-error');
                    if (err) err.style.display = 'none';
                });
            })(cards[i]);
        }

        // Next button
        document.getElementById('btn-next').addEventListener('click', function() {
            if (currentStep < stepsCount - 1) {
                if (validateStep(currentStep)) {
                    if (currentStep === stepsCount - 2) {
                        populateReview();
                    }
                    showStep(currentStep + 1);
                }
            } else {
                if (validateStep(currentStep)) {
                    document.getElementById('form-navigation').style.display = 'none';
                    var steps = document.querySelectorAll('.form-step');
                    for (var j = 0; j < steps.length; j++) steps[j].classList.remove('active');
                    var success = document.getElementById('step-success');
                    success.classList.add('active');
                    success.classList.remove('hidden');
                    
                    // Update blueprint
                    var ul = document.getElementById('blueprint-list');
                    if (ul) {
                        ul.innerHTML = '<li class="flex items-center gap-4"><div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center"><i data-lucide="check" class="w-5 h-5 text-white"></i></div><span class="text-base font-bold text-green-600">' + (DICT.ready || 'READY') + '</span></li>';
                        if (window.lucide) window.lucide.createIcons();
                    }
                }
            }
        });

        // Prev button
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
            });
        }

        // Clear errors on input
        var inputs = ['inp_name', 'inp_email', 'inp_phone'];
        for (var n = 0; n < inputs.length; n++) {
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
            })(inputs[n]);
        }

        // Contact method toggle
        var radios = document.querySelectorAll('input[name="contactMethod"]');
        for (var r = 0; r < radios.length; r++) {
            (function(radio) {
                radio.addEventListener('change', function() {
                    var wrapper = document.getElementById('dynamic-contact-wrapper');
                    if (this.value === 'Telegram') {
                        wrapper.classList.remove('hidden');
                    } else {
                        wrapper.classList.add('hidden');
                    }
                });
            })(radios[r]);
        }

        // Consent
        var consent = document.getElementById('consent_check');
        if (consent) {
            consent.addEventListener('change', function() {
                document.getElementById('err_consent').style.display = 'none';
            });
        }

        // File upload
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
                        div.className = 'text-sm text-slate-600 flex items-center gap-2';
                        div.innerHTML = '<i data-lucide="file" class="w-4 h-4 text-physio-500"></i> ' + this.files[f].name;
                        fileList.appendChild(div);
                    }
                    if (window.lucide) window.lucide.createIcons();
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', init);
})();