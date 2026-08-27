/* Toyota CRM - Dynamic customer form (type toggle, spouse toggle, years stayed) */

(function () {
    'use strict';

    function panelState(panel, shown) {
        if (!panel) {
            return;
        }
        panel.classList.toggle('d-none', !shown);
        panel.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.disabled = !shown;
        });
    }

    function toggleType() {
        var corp = document.querySelector('input[name="customer_type"]:checked');
        var corporate = corp && corp.value === 'Corporate';
        panelState(document.querySelector('.cust-corporate'), corporate);
        panelState(document.querySelector('.cust-individual'), !corporate);
        panelState(document.querySelector('.spouse-wrapper'), !corporate);
        toggleSpouse();
    }

    function toggleSpouse() {
        var spouse = document.querySelector('input[name="spouse_exists"]:checked');
        var yes = spouse && spouse.value === 'Yes';
        panelState(document.querySelector('.spouse-section'), yes);
    }

    function calcYearsSince(dateStr) {
        if (!dateStr) {
            return '';
        }
        var d = new Date(dateStr + 'T00:00:00');
        if (isNaN(d.getTime())) {
            return '';
        }
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        if (d > today) {
            return '0 years';
        }
        var years = today.getFullYear() - d.getFullYear();
        var beforeAnniversary =
            today.getMonth() < d.getMonth() ||
            (today.getMonth() === d.getMonth() && today.getDate() < d.getDate());
        if (beforeAnniversary) {
            years--;
        }
        return years + (years === 1 ? ' year' : ' years');
    }

    function updateYears(el) {
        var target = document.getElementById(el.getAttribute('data-years-target'));
        if (target) {
            target.textContent = calcYearsSince(el.value);
        }
    }

    function init() {
        document.querySelectorAll('input[name="customer_type"]').forEach(function (el) {
            el.addEventListener('change', toggleType);
        });
        document.querySelectorAll('input[name="spouse_exists"]').forEach(function (el) {
            el.addEventListener('change', toggleSpouse);
        });
        document.querySelectorAll('input[data-years-target]').forEach(function (el) {
            el.addEventListener('change', function () { updateYears(el); });
            el.addEventListener('input', function () { updateYears(el); });
        });
        toggleType();
        toggleSpouse();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();