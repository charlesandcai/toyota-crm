/* Toyota Silang CRM - Main JS */

document.addEventListener('DOMContentLoaded', function() {

    // Toast notifications
    window.showToast = function(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const icons = {
            success: 'bi-check-circle-fill',
            error: 'bi-exclamation-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };

        const colors = {
            success: 'text-success',
            error: 'text-danger',
            warning: 'text-warning',
            info: 'text-primary'
        };

        const id = 'toast-' + Date.now();
        const html = `
            <div id="${id}" class="toast align-items-center border-0 shadow-sm" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${icons[type] || icons.info} ${colors[type] || colors.info} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);

        const el = document.getElementById(id);
        const toast = new bootstrap.Toast(el, { delay: 3500 });
        toast.show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    };

    // Generic form submission via AJAX
    window.submitAjaxForm = async function(form, options = {}) {
        event.preventDefault();
        
        const btn = form.querySelector('[type="submit"]');
        const origText = btn ? btn.innerHTML : '';
        
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
        }

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message || 'Saved successfully.', 'success');
                if (options.onSuccess) {
                    options.onSuccess(data);
                }
            } else {
                showToast(data.message || 'An error occurred.', 'error');
                if (data.errors) {
                    displayValidationErrors(form, data.errors);
                }
            }
        } catch (err) {
            showToast('A network error occurred. Please try again.', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        }
    };

    // Display validation errors on form fields
    function displayValidationErrors(form, errors) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        for (const [field, message] of Object.entries(errors)) {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = message;
                input.parentNode.appendChild(feedback);
            }
        }
    }

    // API helper
    window.apiRequest = async function(url, method = 'GET', body = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        if (body) {
            options.body = JSON.stringify(body);
        }
        const response = await fetch(url, options);
        return response.json();
    };

    // Quick filter buttons
    document.querySelectorAll('.quick-filter-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;
            const url = new URL(window.location);
            if (filter && filter !== 'all') {
                url.searchParams.set('filter', filter);
            } else {
                url.searchParams.delete('filter');
            }
            window.location = url;
        });
    });

    // Confirmation dialogs
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };

    // Format date for display
    window.formatDate = function(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    // Calculate follow-up status client-side
    window.getFollowUpStatus = function(nextStepDate) {
        if (!nextStepDate) return { text: 'No follow-up', class: 'badge-no-followup' };
        
        const today = new Date();
        today.setHours(0,0,0,0);
        const due = new Date(nextStepDate + 'T00:00:00');
        
        if (due < today) {
            const days = Math.floor((today - due) / (1000 * 60 * 60 * 24));
            return { text: 'Overdue', class: 'badge-overdue', days: days };
        } else if (due.getTime() === today.getTime()) {
            return { text: 'Due Today', class: 'badge-due-today' };
        } else {
            return { text: 'Upcoming', class: 'badge-upcoming' };
        }
    };
});
