/* =========================================================
   NEXUS · Student Council (SC) side
   ========================================================= */
(function () {
    'use strict';

    /* ---------- Phone / tablet: slide-in sidebar ---------- */
    const sidebar = document.getElementById('scSidebar');
    const menuBtn = document.querySelector('.sc-menu-btn');
    const scrim = document.querySelector('.sc-scrim');

    function setMenu(open) {
        sidebar.classList.toggle('open', open);
        scrim.hidden = !open;
        menuBtn.setAttribute('aria-expanded', String(open));
        menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    }

    if (sidebar && menuBtn && scrim) {
        menuBtn.addEventListener('click', () => setMenu(!sidebar.classList.contains('open')));
        scrim.addEventListener('click', () => setMenu(false));
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                setMenu(false);
                menuBtn.focus();
            }
        });
    }

    /* ---------- Filter bar: apply as soon as a dropdown changes ---------- */
    document.querySelectorAll('select[data-autosubmit]').forEach(select => {
        select.addEventListener('change', () => select.form.submit());
    });

    /* ---------- Submissions: tabs + detail panel ---------- */
    const dataEl = document.getElementById('subData');
    if (!dataEl) return;

    let details = {};
    try {
        details = JSON.parse(dataEl.textContent) || {};
    } catch (e) { /* leave empty */ }

    const rows = Array.from(document.querySelectorAll('.sub-row'));
    const tabs = Array.from(document.querySelectorAll('.sc-tab'));
    const filterEmpty = document.querySelector('.filter-empty');
    const panel = document.getElementById('subDetail');
    const emptyView = panel.querySelector('.detail-empty');
    const bodyView = panel.querySelector('.detail-body');
    const field = name => panel.querySelector('[data-d="' + name + '"]');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const filter = tab.dataset.filter;

            tabs.forEach(t => {
                const on = t === tab;
                t.classList.toggle('active', on);
                t.setAttribute('aria-selected', String(on));
            });

            let shown = 0;
            rows.forEach(row => {
                const show = filter === 'all' || row.dataset.group === filter;
                row.hidden = !show;
                if (show) shown++;
            });

            if (filterEmpty) filterEmpty.hidden = shown > 0 || rows.length === 0;
        });
    });

    function showDetail(row) {
        const d = details[row.dataset.index];
        if (!d) return;

        rows.forEach(r => r.classList.toggle('selected', r === row));

        const step = field('step');
        step.textContent = d.stepLabel;
        step.className = 'pill ' + d.stepClass;

        ['title', 'student', 'submitted', 'repository', 'yearLevel'].forEach(key => {
            field(key).textContent = d[key] || '—';
        });

        const list = field('tracking');
        list.replaceChildren();
        (d.tracking || []).forEach(item => {
            const li = document.createElement('li');
            li.className = item.state || 'todo';

            const name = document.createElement('span');
            name.className = 'step-name';
            name.textContent = item.step;

            const note = document.createElement('span');
            note.className = 'step-note';
            note.textContent = item.note || '';

            li.append(name, note);
            list.append(li);
        });

        const fileLink = field('fileUrl');
        if (d.fileUrl) {
            fileLink.href = d.fileUrl;
            fileLink.removeAttribute('aria-disabled');
        } else {
            fileLink.removeAttribute('href');
            fileLink.setAttribute('aria-disabled', 'true');
        }

        emptyView.hidden = true;
        bodyView.hidden = false;
    }

    rows.forEach(row => {
        row.addEventListener('click', () => showDetail(row));
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                showDetail(row);
            }
        });
    });

    // Open the first submission by default (matches the design)
    if (rows.length) showDetail(rows[0]);
})();
