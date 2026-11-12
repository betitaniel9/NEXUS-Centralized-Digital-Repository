/* =========================================================
   NEXUS · Technical Committee workspace
   Dialogs (Review, My sections, Semester review) + review panel + file preview
   ========================================================= */
(function () {
    'use strict';

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    /* ---------------------------------------------------------
       Dialogs
       <dialog class="sc-modal" id="x">               the dialog
       [data-modal-open="x"] [data-payload='{...}']   opens it, filling values from the payload
       [data-modal-close]                             closes the dialog it is in
       [data-fill="key"]        → textContent
       [data-fill-value="key"]  → input/select value
       [data-fill-action="key"] → form action
       [data-fill-href="key"]   → link href (disabled when empty)
       [data-submit-form="formId"] → submits that form (used by confirm steps)
       --------------------------------------------------------- */
    let payload = {};

    function fill(dialog, data) {
        $$('form', dialog).forEach(f => f.reset());

        $$('[data-fill]', dialog).forEach(el => {
            el.textContent = data[el.dataset.fill] ?? '';
        });
        $$('[data-fill-value]', dialog).forEach(el => {
            el.value = data[el.dataset.fillValue] ?? '';
        });
        $$('[data-fill-action]', dialog).forEach(el => {
            if (data[el.dataset.fillAction]) el.action = data[el.dataset.fillAction];
        });
        $$('[data-fill-href]', dialog).forEach(el => {
            const href = data[el.dataset.fillHref];
            if (href) {
                el.href = href;
                el.removeAttribute('aria-disabled');
            } else {
                el.removeAttribute('href');
                el.setAttribute('aria-disabled', 'true');
            }
        });
    }

    function openModal(id, data) {
        const dialog = document.getElementById(id);
        if (!dialog) return null;

        if (data) {
            payload = data;
            fill(dialog, data);
        }

        if (!dialog.open) dialog.showModal();
        dialog.dispatchEvent(new CustomEvent('modal:open', { detail: payload }));
        return dialog;
    }

    function closeModal(dialog) {
        if (dialog && dialog.open) dialog.close();
    }

    document.addEventListener('click', e => {
        const opener = e.target.closest('[data-modal-open]');
        if (opener && !opener.disabled) {
            let data = null;
            if (opener.dataset.payload) {
                try { data = JSON.parse(opener.dataset.payload); } catch (err) { data = {}; }
            }
            openModal(opener.dataset.modalOpen, data);
            return;
        }

        const closer = e.target.closest('[data-modal-close]');
        if (closer) {
            closeModal(closer.closest('dialog'));
            return;
        }

        const submitter = e.target.closest('[data-submit-form]');
        if (submitter) {
            const form = document.getElementById(submitter.dataset.submitForm);
            if (form) {
                submitter.disabled = true;
                form.submit(); // already validated before the confirm step opened
            }
        }
    });

    // Click on the dimmed backdrop closes the dialog
    $$('dialog.sc-modal').forEach(dialog => {
        dialog.addEventListener('click', e => {
            if (e.target === dialog) closeModal(dialog);
        });
    });

    // Result pop-ups rendered by the server open by themselves
    $$('dialog[data-autoopen]').forEach(d => d.showModal());

    /* ---------------------------------------------------------
       Review page
       --------------------------------------------------------- */
    const reviewData = $('#reviewData');
    if (reviewData) {
        let data = { items: {}, checkCount: 0 };
        try { data = JSON.parse(reviewData.textContent) || data; } catch (e) { /* empty */ }

        const panel = $('#reviewPanel');
        const rows = $$('.queue-row');
        const checks = $$('.check-item', panel);
        const acceptBtn = $('[data-action="accept"]', panel);
        const allChecked = $('[data-r="allChecked"]', panel);
        let current = null;

        function updateChecks() {
            const done = checks.filter(c => c.checked).length;
            const complete = done === checks.length && checks.length > 0;
            acceptBtn.disabled = !complete;
            allChecked.hidden = !complete;
            $('[data-r="progress"]', panel).textContent = complete
                ? 'All checks complete · Ready to accept'
                : done + ' of ' + checks.length + ' checks complete';
        }

        function select(row) {
            current = data.items[row.dataset.index];
            if (!current) return;

            rows.forEach(r => r.classList.toggle('selected', r === row));
            ['title', 'student', 'submitted', 'daysWaiting', 'section', 'yearLevel', 'subject', 'fileType', 'fileSize'].forEach(k => {
                const el = $('[data-r="' + k + '"]', panel);
                if (el) el.textContent = current[k] || '—';
            });
            $('[data-r="daysWaiting"]', panel).classList.toggle('pill-soft-amber', !!current.late);
            $('[data-r="daysWaiting"]', panel).classList.toggle('pill-gray', !current.late);

            checks.forEach(c => { c.checked = false; });
            updateChecks();

            $('.detail-empty', panel).hidden = true;
            $('.detail-body', panel).hidden = false;
        }

        rows.forEach(row => {
            row.addEventListener('click', () => select(row));
            row.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    select(row);
                }
            });
        });
        checks.forEach(c => c.addEventListener('change', updateChecks));

        panel.addEventListener('click', e => {
            const btn = e.target.closest('[data-action]');
            if (!btn || !current) return;
            const action = btn.dataset.action;
            if (action === 'preview') openModal('previewModal', current);
            if (action === 'return') openModal('returnModal', current);
            if (action === 'accept' && !btn.disabled) openModal('acceptModal', current);
        });

        if (rows.length) select(rows[0]);
    }

    /* ---------- File preview (PDF via pdf.js, images inline) ---------- */
    const preview = $('#previewModal');
    if (preview) {
        const PDFJS = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
        const WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        const pv = name => $('[data-pv="' + name + '"]', preview);
        const stage = pv('stage');
        let pdf = null;
        let pageNo = 1;
        let zoom = 1;
        let mode = 'none';
        let loadToken = 0;
        let pdfjsReady = null;

        function loadPdfJs() {
            if (window.pdfjsLib) return Promise.resolve(window.pdfjsLib);
            pdfjsReady = pdfjsReady || new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = PDFJS;
                s.onload = () => {
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER;
                    resolve(window.pdfjsLib);
                };
                s.onerror = reject;
                document.head.append(s);
            });
            return pdfjsReady;
        }

        function message(text) {
            stage.replaceChildren();
            const p = document.createElement('p');
            p.className = 'viewer-msg';
            p.textContent = text;
            stage.append(p);
        }

        function setControls() {
            const pages = mode === 'pdf' ? pdf.numPages : (mode === 'image' ? 1 : 0);
            pv('page').textContent = pages ? pageNo : '—';
            pv('pages').textContent = pages || '—';
            pv('zoom').textContent = Math.round(zoom * 100) + '%';
            pv('prev').disabled = mode !== 'pdf' || pageNo <= 1;
            pv('next').disabled = mode !== 'pdf' || pageNo >= pages;
            pv('zoomIn').disabled = mode === 'none' || zoom >= 2;
            pv('zoomOut').disabled = mode === 'none' || zoom <= 0.5;
            pv('pageInfo').textContent = mode === 'pdf' ? ' · ' + pdf.numPages + (pdf.numPages === 1 ? ' page' : ' pages') : '';
        }

        async function renderPdfPage() {
            const page = await pdf.getPage(pageNo);
            const fit = (stage.clientWidth - 32) / page.getViewport({ scale: 1 }).width;
            const viewport = page.getViewport({ scale: Math.max(fit, 0.1) * zoom });
            const ratio = window.devicePixelRatio || 1;
            const canvas = document.createElement('canvas');
            canvas.width = viewport.width * ratio;
            canvas.height = viewport.height * ratio;
            canvas.style.width = viewport.width + 'px';
            canvas.style.height = viewport.height + 'px';
            canvas.className = 'viewer-page-img';
            await page.render({
                canvasContext: canvas.getContext('2d'),
                viewport,
                transform: ratio !== 1 ? [ratio, 0, 0, ratio, 0, 0] : null,
            }).promise;
            stage.replaceChildren(canvas);
            setControls();
        }

        function renderImage() {
            const img = stage.querySelector('img');
            if (img) img.style.width = (zoom * 100) + '%';
            setControls();
        }

        function rerender() {
            if (mode === 'pdf') renderPdfPage();
            if (mode === 'image') renderImage();
        }

        preview.addEventListener('modal:open', async e => {
            const item = e.detail || {};
            const token = ++loadToken;
            pdf = null;
            pageNo = 1;
            zoom = 1;
            mode = 'none';
            setControls();

            const url = item.fileUrl;
            const type = (item.fileType || '').toLowerCase();

            if (!url) {
                message('No file is attached to this submission.');
                return;
            }

            if (['png', 'jpg', 'jpeg', 'gif', 'webp'].includes(type)) {
                const img = document.createElement('img');
                img.src = url;
                img.alt = 'Preview of ' + (item.title || 'file');
                img.className = 'viewer-page-img';
                stage.replaceChildren(img);
                mode = 'image';
                renderImage();
                return;
            }

            if (type !== 'pdf') {
                message('Preview is not available for ' + (item.fileType || 'this file type') + '. Download the file to check it.');
                return;
            }

            message('Loading preview…');
            try {
                const lib = await loadPdfJs();
                const doc = await lib.getDocument(url).promise;
                if (token !== loadToken) return; // another file was opened meanwhile
                pdf = doc;
                mode = 'pdf';
                await renderPdfPage();
            } catch (err) {
                if (token === loadToken) message('The file could not be loaded. Try downloading it instead.');
            }
        });

        pv('prev').addEventListener('click', () => { if (pageNo > 1) { pageNo--; rerender(); } });
        pv('next').addEventListener('click', () => { if (pdf && pageNo < pdf.numPages) { pageNo++; rerender(); } });
        pv('zoomIn').addEventListener('click', () => { zoom = Math.min(2, zoom + 0.25); rerender(); });
        pv('zoomOut').addEventListener('click', () => { zoom = Math.max(0.5, zoom - 0.25); rerender(); });
    }

    /* ---------------------------------------------------------
       My sections
       --------------------------------------------------------- */
    const formatSize = bytes => {
        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0;
        while (bytes >= 1024 && i < units.length - 1) { bytes /= 1024; i++; }
        return (i === 0 ? bytes : bytes.toFixed(1)) + ' ' + units[i];
    };

    // Upload: show the chosen file and suggest a title
    $$('[data-pick-input]').forEach(input => {
        input.addEventListener('change', () => {
            const box = input.closest('.file-pick');
            const file = input.files[0];
            $('[data-pick="name"]', box).textContent = file ? file.name : 'No file chosen';
            $('[data-pick="meta"]', box).textContent = file
                ? (file.name.split('.').pop() || '').toUpperCase() + ' · ' + formatSize(file.size) + ' · Ready to upload'
                : 'PDF, DOCX or image';

            const title = document.getElementById(input.dataset.fillTitle);
            if (file && title && !title.value) title.value = file.name.replace(/\.[^.]+$/, '');
        });
    });

    // Upload dialog: clear the picked-file text when it opens
    const uploadModal = $('#uploadModal');
    if (uploadModal) {
        uploadModal.addEventListener('modal:open', () => {
            $('form', uploadModal).reset();
            $('[data-pick="name"]', uploadModal).textContent = 'No file chosen';
            $('[data-pick="meta"]', uploadModal).textContent = 'PDF, DOCX or image';
        });
    }

    // Edit: save → confirm (details) or confirm (replacement); delete → confirm
    const editModal = $('#editModal');
    if (editModal) {
        const form = $('#editForm');
        const replaceInput = $('[data-replace-input]', editModal);
        const replaceName = $('[data-replace-name]', editModal);

        editModal.addEventListener('modal:open', () => {
            replaceName.textContent = '';
        });

        replaceInput.addEventListener('change', () => {
            const file = replaceInput.files[0];
            replaceName.textContent = file ? ' → ' + file.name : '';
        });

        editModal.addEventListener('click', e => {
            const step = e.target.closest('[data-step]');
            if (!step) return;

            if (step.dataset.step === 'delete') {
                closeModal(editModal);
                openModal('deleteModal', payload);
                return;
            }

            if (!form.reportValidity()) return;

            const state = $('#editState', form);
            const next = Object.assign({}, payload, {
                originalTitle: payload.title,
                title: $('#editName', form).value,
                section: $('#editSection', form).selectedOptions[0]?.textContent || payload.section,
                yearLevel: $('#editYear', form).selectedOptions[0]?.textContent || payload.yearLevel,
                type: $('#editType', form).value || payload.type,
                stateLabel: state.selectedOptions[0]?.textContent || '',
                replacement: replaceInput.files[0] ? replaceInput.files[0].name : '',
            });

            closeModal(editModal);
            // Keep the edit form's values: show the confirm step without refilling the form
            const confirmId = next.replacement ? 'replaceModal' : 'saveModal';
            const confirm = document.getElementById(confirmId);
            $$('[data-fill]', confirm).forEach(el => { el.textContent = next[el.dataset.fill] ?? ''; });
            confirm.showModal();
        });
    }

    /* ---------------------------------------------------------
       Semester review: fix link → confirm
       --------------------------------------------------------- */
    const fixModal = $('#fixLinkModal');
    if (fixModal) {
        fixModal.addEventListener('click', e => {
            if (!e.target.closest('[data-step="verify"]')) return;
            const form = $('#fixLinkForm');
            if (!form.reportValidity()) return;

            const next = Object.assign({}, payload, { newLink: $('#fixNew', form).value });
            closeModal(fixModal);
            const confirm = $('#verifyModal');
            $$('[data-fill]', confirm).forEach(el => { el.textContent = next[el.dataset.fill] ?? ''; });
            confirm.showModal();
        });
    }
})();
