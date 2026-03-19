// ═══════════════════════════════════════════════════════════════
// PASTE THIS INSIDE THE <script> TAG IN index.html
// REPLACE the existing handleFormSubmit, handleFpSubmit functions
// ═══════════════════════════════════════════════════════════════

// ── API URL — change to your actual Hostinger domain ──
const API_URL = 'https://azaleakalyani.com/api/leads.php';
const SITE_NAME = 'azalea'; // Change to 'propnmore' on main site

// ══════════════════════════════════════════════════════
// GENERAL FORM SUBMIT (Enquiry / Brochure / Site Visit)
// type = 'enq' | 'brochure' | 'visit'
// ══════════════════════════════════════════════════════
async function handleFormSubmit(e, type) {
    e.preventDefault();
    const form = e.target;
    if (!form.checkValidity()) { form.reportValidity(); return; }

    // Map type to form_type value
    const formTypeMap = { enq: 'enquiry', brochure: 'brochure', visit: 'site_visit' };
    const form_type = formTypeMap[type] || 'enquiry';

    // Collect fields based on type prefix
    const prefix = type === 'enq' ? 'ef' : type === 'brochure' ? 'br' : 'sv';
    const payload = {
        site_name:     SITE_NAME,
        form_type:     form_type,
        name:          document.getElementById(prefix + '-name')?.value || '',
        phone:         document.getElementById(prefix + '-ph')?.value   || '',
        email:         document.getElementById(prefix + '-em')?.value   || '',
        interested_in: document.getElementById(prefix + '-int')?.value  || '',
        message:       document.getElementById(prefix + '-msg')?.value  || '',
        visit_date:    document.getElementById('sv-date')?.value        || '',
        visit_time:    document.getElementById('sv-time')?.value        || '',
    };

    // Show loading state on button
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting…';
    btn.disabled = true;

    try {
        const res  = await fetch(API_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            // Save token for floor plan / brochure unlocking
            if (data.token) sessionStorage.setItem('azalea_token', data.token);

            // Hide form, show success
            const formWrap = document.getElementById(type + '-form-wrap') || document.getElementById('enq-form-wrap');
            const sucEl    = document.getElementById(type + '-success')   || document.getElementById('enq-success');

            if (formWrap) formWrap.style.display = 'none';
            if (sucEl) {
                // Set custom success message from API
                const msgEl = sucEl.querySelector('.m-sm');
                if (msgEl) msgEl.innerHTML = data.message;
                sucEl.classList.add('show');
            }

            // Brochure type: unlock floor plans too (they filled a form)
            if (form_type === 'brochure') {
                unlockFloorPlans('All Floor Plans');
            }

        } else {
            alert(data.message || 'Something went wrong. Please try again.');
            btn.innerHTML = originalText;
            btn.disabled  = false;
        }

    } catch (err) {
        console.error('API error:', err);
        alert('Network error. Please check your connection and try again.');
        btn.innerHTML = originalText;
        btn.disabled  = false;
    }
}

// ══════════════════════════════════════════════════════
// FLOOR PLAN FORM SUBMIT
// Unlocks blurred floor plan images after successful submit
// ══════════════════════════════════════════════════════
async function handleFpSubmit(e) {
    e.preventDefault();
    const form = e.target;
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const plan_type = fpCurrentType; // set globally when modal opens

    const payload = {
        site_name:     SITE_NAME,
        form_type:     'floor_plan',
        name:          document.getElementById('ff-name')?.value || '',
        phone:         document.getElementById('ff-ph')?.value   || '',
        email:         document.getElementById('ff-em')?.value   || '',
        interested_in: document.getElementById('ff-int')?.value  || plan_type,
        plan_type:     plan_type,
    };

    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Verifying…';
    btn.disabled = true;

    try {
        const res  = await fetch(API_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            if (data.token) sessionStorage.setItem('azalea_token', data.token);

            // Show success in modal
            document.getElementById('fp-form-wrap').style.display = 'none';
            document.getElementById('fp-success').classList.add('show');

            // After 1.5s: close modal + remove blur from floor plan images
            setTimeout(() => {
                closeModal('m-fp');
                unlockFloorPlans(plan_type);
            }, 1600);

        } else {
            alert(data.message || 'Something went wrong. Please try again.');
            btn.innerHTML = originalText;
            btn.disabled  = false;
        }

    } catch (err) {
        console.error('API error:', err);
        alert('Network error. Please try again.');
        btn.innerHTML = originalText;
        btn.disabled  = false;
    }
}

// ══════════════════════════════════════════════════════
// ON PAGE LOAD: Check if user already has a token
// If they do — auto-unlock floor plans (returning visitor)
// ══════════════════════════════════════════════════════
(async function checkExistingToken() {
    const token = sessionStorage.getItem('azalea_token');
    if (!token) return;

    try {
        const res  = await fetch(API_URL + '?token=' + encodeURIComponent(token));
        const data = await res.json();
        if (data.success && data.access) {
            // Auto-unlock for returning visitors in same session
            unlockFloorPlans('All Floor Plans');
        }
    } catch (e) {
        // Silent fail — token check is optional
    }
})();
