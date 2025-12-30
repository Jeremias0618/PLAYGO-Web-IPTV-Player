document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('submitBtn');
    const username = document.getElementById('usuario');
    const pwd = document.getElementById('senha');
    const toggle = document.getElementById('togglePassword');
    const forgotLink = document.getElementById('forgotLink');
    const modal = document.getElementById('forgotModal');
    const modalDialog = modal ? modal.querySelector('.modal-dialog') : null;
    const closeEls = modal ? modal.querySelectorAll('[data-modal-close]') : null;
    let lastFocus = null;

    function validateForm() {
        if (!username || !pwd || !btn) return;
        
        const usernameValue = username.value.trim();
        const passwordValue = pwd.value.trim();
        const isValid = usernameValue.length > 0 && passwordValue.length > 0;
        
        btn.disabled = !isValid;
        if (isValid) {
            btn.classList.add('is-enabled');
        } else {
            btn.classList.remove('is-enabled');
        }
    }

    if (username && pwd && btn) {
        username.addEventListener('input', validateForm);
        pwd.addEventListener('input', validateForm);
        validateForm();
    }

    if (toggle) {
        toggle.addEventListener('click', function(){
            const isPw = pwd.getAttribute('type') === 'password';
            const iconOpen = toggle.querySelector('.icon-eye-open');
            const iconClosed = toggle.querySelector('.icon-eye-closed');
            
            pwd.setAttribute('type', isPw ? 'text' : 'password');
            this.classList.toggle('is-on', isPw);
            
            if (iconOpen && iconClosed) {
                if (isPw) {
                    iconOpen.style.display = 'none';
                    iconClosed.style.display = 'block';
                } else {
                    iconOpen.style.display = 'block';
                    iconClosed.style.display = 'none';
                }
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e){
            if (btn.disabled) {
                e.preventDefault();
                return false;
            }
            btn.disabled = true;
            btn.classList.add('is-loading');
            const btnText = btn.querySelector('span');
            const btnArrow = btn.querySelector('.button-arrow');
            if (btnText) {
                btnText.textContent = 'Verificando…';
            }
            if (btnArrow) {
                btnArrow.outerHTML = '<svg class="spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25" /><path fill="currentColor" d="M4 12a8 8 0 018-8V0a12 12 0 00-12 12h4z" opacity="0.75" /></svg>';
            }
        }, { passive: false });
    }

    function openModal() {
        if (!modal) return;
        lastFocus = document.activeElement;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('is-open');
        document.body.classList.add('modal-open');
        const focusable = modal.querySelector('button.button-secondary') || modalDialog;
        if (focusable) focusable.focus();
        document.addEventListener('keydown', handleKey);
    }

    function closeModal() {
        if (!modal) return;
        modal.setAttribute('aria-hidden', 'true');
        modal.classList.remove('is-open');
        document.body.classList.remove('modal-open');
        document.removeEventListener('keydown', handleKey);
        if (lastFocus) lastFocus.focus();
    }

    function handleKey(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
        if (e.key === 'Tab' && modal.classList.contains('is-open')) {
            const focusables = modal.querySelectorAll('button, [href], [tabindex]:not([tabindex="-1"])');
            const list = Array.from(focusables).filter(el => !el.hasAttribute('disabled'));
            if (!list.length) return;
            const first = list[0];
            const last = list[list.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }

    if (forgotLink && modal) {
        forgotLink.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    if (closeEls) {
        closeEls.forEach(el => el.addEventListener('click', closeModal));
    }

    if (modalDialog) {
        modalDialog.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal || e.target.classList.contains('modal-backdrop')) {
                closeModal();
            }
        });
    }
});

