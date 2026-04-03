const header = document.querySelector('[data-site-header]');
const navToggle = document.querySelector('[data-nav-toggle]');
const navWrap = document.querySelector('[data-nav-wrap]');

if (header) {
    const syncHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 12);
    syncHeader();
    window.addEventListener('scroll', syncHeader, { passive: true });
}

if (navToggle && navWrap) {
    navToggle.addEventListener('click', () => {
        const isOpen = navWrap.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', String(isOpen));
    });
}

document.querySelectorAll('[data-async-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const status = form.querySelector('[data-form-status]');
        const submitButton = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json();

            if (!response.ok || !payload.ok) {
                throw new Error(payload.message || 'Something went wrong while sending your request.');
            }

            if (status) {
                status.hidden = false;
                status.className = 'form-status is-success';
                status.textContent = payload.message;
            }

            form.reset();
        } catch (error) {
            if (status) {
                status.hidden = false;
                status.className = 'form-status is-error';
                status.textContent = error.message || 'Unable to send the form right now.';
            }
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    });
});
