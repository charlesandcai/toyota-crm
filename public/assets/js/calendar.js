/* Toyota CRM - Calendar */

(function () {
    'use strict';

    const STATE = {
        view: 'month',
        cursor: new Date(), // focused day (first-of-month / week monday / single day)
        events: []
    };

    const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    let grid = null;
    let dateLabel = null;
    let loading = null;
    let eventModal = null;

    function pad(n) { return String(n).padStart(2, '0'); }
    function toISO(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
    function fromISO(s) { return new Date(s + 'T00:00:00'); }
    function startOfWeek(d) {
        const out = new Date(d);
        const offset = (out.getDay() + 6) % 7; // Monday-first
        out.setDate(out.getDate() - offset);
        return out;
    }
    function addDays(d, n) {
        const out = new Date(d);
        out.setDate(out.getDate() + n);
        return out;
    }
    function todayISO() {
        const n = new Date();
        return toISO(n);
    }
    function fmtLong(d) {
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return days[d.getDay()] + ', ' + MONTHS[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
    }

    function apiUrl(start, end) {
        const url = new URL(location.href);
        url.searchParams.set('route', 'api/calendar/events');
        url.searchParams.set('start', start);
        url.searchParams.set('end', end);
        return url.toString();
    }

    async function fetchEvents(start, end) {
        const res = await fetch(apiUrl(start, end), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        return (data && Array.isArray(data.events)) ? data.events : [];
    }

    // ---------- DOM helpers ----------

    function el(tag, cls, text) {
        const node = document.createElement(tag);
        if (cls) node.className = cls;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    }

    function eventChip(ev, compact) {
        const chip = el('div', 'cal-event' + (compact ? ' cal-event-compact' : ''));
        chip.style.borderLeftColor = ev.color;
        const title = el('span', 'cal-event-title', ev.title);
        const sub = el('span', 'cal-event-sub', compact ? '' : eventSub(ev));
        chip.appendChild(title);
        if (sub.textContent) chip.appendChild(sub);
        chip.title = ev.title;
        chip.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            openEventModal(ev);
        });
        return chip;
    }

    function eventSub(ev) {
        const parts = [];
        if (ev.model_name) parts.push(ev.model_name);
        if (ev.stage_name) parts.push(ev.stage_name);
        if (ev.priority_name) parts.push(ev.priority_name);
        return parts.join(' · ');
    }

    // ---------- Rendering ----------

    function windowRange(view, cursor) {
        if (view === 'month') {
            const first = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
            const last = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0);
            return { start: toISO(first), end: toISO(last) };
        }
        if (view === 'week') {
            const monday = startOfWeek(cursor);
            return { start: toISO(monday), end: toISO(addDays(monday, 6)) };
        }
        return { start: toISO(cursor), end: toISO(cursor) };
    }

    function renderMonth(events, cursor) {
        const first = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
        const offset = (first.getDay() + 6) % 7; // Monday-first
        const gridStart = addDays(first, -offset);
        const today = todayISO();

        const header = el('div', 'cal-grid-row cal-grid-header cal-grid-row-7');
        WEEKDAYS.forEach((name) => header.appendChild(el('div', 'cal-cell cal-dow', name)));

        const body = el('div', 'cal-grid-body');
        let gridDate = gridStart;
        for (let week = 0; week < 6; week++) {
            const row = el('div', 'cal-grid-row cal-grid-row-7');
            for (let i = 0; i < 7; i++) {
                const iso = toISO(gridDate);
                const inMonth = gridDate.getMonth() === cursor.getMonth();
                const cell = el('div', 'cal-cell cal-day' + (inMonth ? '' : ' cal-muted') + (iso === today ? ' cal-today' : ''));

                const dayNum = el('span', 'cal-day-num', String(gridDate.getDate()));
                if (inMonth) {
                    dayNum.addEventListener('click', () => setDate(gridDate));
                }
                cell.appendChild(dayNum);

                const daysEvents = events.filter((ev) => ev.date === iso);
                const shown = daysEvents.slice(0, 3);
                shown.forEach((ev) => cell.appendChild(eventChip(ev, true)));
                if (daysEvents.length > 3) {
                    const more = el('span', 'cal-more', '+' + (daysEvents.length - 3) + ' more');
                    more.addEventListener('click', () => setDate(gridDate));
                    cell.appendChild(more);
                }

                row.appendChild(cell);
                gridDate = addDays(gridDate, 1);
            }
            body.appendChild(row);
        }

        grid.innerHTML = '';
        grid.appendChild(header);
        grid.appendChild(body);
    }

    function renderWeek(events, cursor) {
        const monday = startOfWeek(cursor);
        const today = todayISO();
        const header = el('div', 'cal-grid-row cal-grid-header cal-grid-row-7');
        const body = el('div', 'cal-grid-body cal-grid-body-week');

        for (let i = 0; i < 7; i++) {
            const d = addDays(monday, i);
            const iso = toISO(d);
            header.appendChild(el('div', 'cal-cell cal-dow', WEEKDAYS[i]));

            const col = el('div', 'cal-cell cal-week-col' + (i >= 5 ? ' cal-weekend' : '') + (iso === today ? ' cal-today' : ''));

            const dateHead = el('div', 'cal-week-date' + (iso === today ? ' fw-bold' : ''));
            const numSpan = el('span', 'cal-day-num', String(d.getDate()));
            if (i >= 5) dateHead.classList.add('text-muted');
            numSpan.addEventListener('click', () => setDate(d));
            dateHead.appendChild(numSpan);
            dateHead.appendChild(el('span', 'small', MONTHS[d.getMonth()].slice(0, 3)));
            col.appendChild(dateHead);

            events.filter((ev) => ev.date === iso).forEach((ev) => col.appendChild(eventChip(ev, false)));

            body.appendChild(col);
        }

        grid.innerHTML = '';
        grid.appendChild(header);
        grid.appendChild(body);
    }

    function renderDay(events, cursor) {
        const iso = toISO(cursor);
        const today = todayISO();
        const daysEvents = events.filter((ev) => ev.date === iso);

        grid.innerHTML = '';
        const list = el('div', 'cal-day-list' + (iso === today ? ' cal-today' : ''));
        const head = el('div', 'cal-day-list-head fw-semibold', fmtLong(cursor));
        list.appendChild(head);

        if (daysEvents.length === 0) {
            list.appendChild(el('div', 'text-muted text-center py-4', 'No scheduled events.'));
        } else {
            daysEvents.forEach((ev) => {
                list.appendChild(eventChip(ev, false));
            });
        }
        grid.appendChild(list);
    }

    // ---------- Modal ----------

    function openEventModal(ev) {
        if (!eventModal) return;
        const mTitle = document.getElementById('calEventTitle');
        const mBody = document.getElementById('calEventBody');
        const mLeadBtn = document.getElementById('calEventLeadBtn');

        mTitle.innerHTML = '';
        const dot = el('span', 'cal-dot me-2');
        dot.style.background = ev.color;
        mTitle.appendChild(dot);
        mTitle.appendChild(document.createTextNode(ev.type_label || ev.title));

        mBody.innerHTML = '';
        const rows = [
            ['Date', fmtLong(fromISO(ev.date)) + (ev.recurring ? ' (repeats yearly)' : '')],
        ];
        if (ev.lead_name) rows.push(['Customer', ev.lead_name]);
        if (ev.model_name) rows.push(['Model', ev.model_name]);
        if (ev.stage_name) rows.push(['Stage', ev.stage_name]);
        if (ev.priority_name) rows.push(['Priority', ev.priority_name]);
        if (ev.next_step) rows.push(['Next Step', ev.next_step]);
        if (ev.notes) rows.push(['Notes', ev.notes]);

        rows.forEach(([k, v]) => {
            const row = el('div', 'mb-3');
            row.appendChild(el('div', 'small text-muted text-uppercase', k));
            row.appendChild(el('div', '', v));
            mBody.appendChild(row);
        });

        if (ev.lead_url && ev.lead_id) {
            mLeadBtn.href = ev.lead_url;
            mLeadBtn.classList.remove('d-none');
        } else {
            mLeadBtn.classList.add('d-none');
        }

        eventModal.show();
    }

    // ---------- Navigation ----------

    function shiftCursor(n) {
        if (STATE.view === 'month') {
            STATE.cursor = new Date(STATE.cursor.getFullYear(), STATE.cursor.getMonth() + n, 1);
        } else if (STATE.view === 'week') {
            STATE.cursor = addDays(STATE.cursor, n * 7);
        } else {
            STATE.cursor = addDays(STATE.cursor, n);
        }
        loadAndRender();
    }

    function setDate(d) {
        STATE.view = 'day';
        STATE.cursor = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        syncViewButtons();
        loadAndRender();
    }

    function syncViewButtons() {
        document.querySelectorAll('.cal-view-btn').forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.view === STATE.view);
        });
    }

    function updateDateLabel(cursor) {
        if (STATE.view === 'month') {
            dateLabel.textContent = MONTHS[cursor.getMonth()] + ' ' + cursor.getFullYear();
        } else if (STATE.view === 'week') {
            const monday = startOfWeek(cursor);
            const sunday = addDays(monday, 6);
            const sameMonth = monday.getMonth() === sunday.getMonth();
            dateLabel.textContent = MONTHS[monday.getMonth()].slice(0, 3) + ' ' + monday.getDate() +
                ' – ' + (sameMonth ? '' : MONTHS[sunday.getMonth()].slice(0, 3) + ' ') + sunday.getDate() + ', ' + sunday.getFullYear();
        } else {
            dateLabel.textContent = fmtLong(cursor);
        }
    }

    async function loadAndRender() {
        const { start, end } = windowRange(STATE.view, STATE.cursor);
        updateDateLabel(STATE.cursor);

        loading.classList.remove('d-none');
        try {
            STATE.events = await fetchEvents(start, end);
        } catch (err) {
            grid.innerHTML = '';
            grid.appendChild(el('div', 'text-danger text-center py-4', 'Unable to load events. Please reload the page.'));
            return;
        } finally {
            loading.classList.add('d-none');
        }

        if (STATE.view === 'month') renderMonth(STATE.events, STATE.cursor);
        else if (STATE.view === 'week') renderWeek(STATE.events, STATE.cursor);
        else renderDay(STATE.events, STATE.cursor);
    }

    // ---------- Wire up ----------

    function init() {
        grid = document.getElementById('calendarGrid');
        dateLabel = document.getElementById('calDateLabel');
        loading = document.getElementById('calendarLoading');
        const eventModalEl = document.getElementById('calEventModal');

        if (!grid) return;

        if (eventModalEl) {
            eventModal = new bootstrap.Modal(eventModalEl);
        }

        document.querySelectorAll('.cal-view-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                STATE.view = btn.dataset.view;
                syncViewButtons();
                persistView();
                loadAndRender();
            });
        });

        document.getElementById('calToday').addEventListener('click', () => {
            const n = new Date();
            STATE.cursor = STATE.view === 'month'
                ? new Date(n.getFullYear(), n.getMonth(), 1)
                : new Date(n.getFullYear(), n.getMonth(), n.getDate());
            loadAndRender();
        });

        document.getElementById('calPrev').addEventListener('click', () => shiftCursor(-1));
        document.getElementById('calNext').addEventListener('click', () => shiftCursor(1));

        try {
            const saved = localStorage.getItem('crm-cal-view');
            if (saved === 'week' || saved === 'day' || saved === 'month') STATE.view = saved;
        } catch (e) { /* ignore */ }

        syncViewButtons();
        loadAndRender();
    }

    function persistView() {
        try { localStorage.setItem('crm-cal-view', STATE.view); } catch (e) { /* ignore */ }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();