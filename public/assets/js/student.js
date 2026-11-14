/* NEXUS · Student library: sidebar, account menu, sort, list/grid toggle, file preview. */
(function () {
    'use strict';

    // ---- Folder sidebar (phones / tablets) ------------------------------
    const side = document.getElementById('libSide');
    const menuBtn = document.querySelector('.lib-menu-btn');
    const scrim = document.querySelector('.lib-scrim');

    function setSide(open) {
        side.classList.toggle('open', open);
        scrim.hidden = !open;
        menuBtn.setAttribute('aria-expanded', String(open));
    }

    if (side && menuBtn && scrim) {
        menuBtn.addEventListener('click', () => setSide(!side.classList.contains('open')));
        scrim.addEventListener('click', () => setSide(false));
    }

    // ---- Account menu ----------------------------------------------------
    const avatar = document.querySelector('.lib-avatar');
    const userMenu = document.getElementById('libUserMenu');

    function setMenu(open) {
        userMenu.hidden = !open;
        avatar.setAttribute('aria-expanded', String(open));
    }

    if (avatar && userMenu) {
        avatar.addEventListener('click', (e) => {
            e.stopPropagation();
            setMenu(userMenu.hidden);
        });
        document.addEventListener('click', (e) => {
            if (!userMenu.hidden && !userMenu.contains(e.target)) setMenu(false);
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (userMenu && !userMenu.hidden) { setMenu(false); avatar.focus(); }
        if (side && side.classList.contains('open')) { setSide(false); menuBtn.focus(); }
    });

    // Disabled "Share your notes" links (no form URL set yet)
    document.querySelectorAll('a[aria-disabled="true"]').forEach((a) => {
        a.addEventListener('click', (e) => e.preventDefault());
    });

    // ---- Sort: apply as soon as it changes --------------------------------
    document.querySelectorAll('[data-autosubmit]').forEach((el) => {
        el.addEventListener('change', () => el.form.submit());
    });

    // ---- List / grid layout (remembered on this device) ------------------
    const files = document.querySelector('[data-files]');
    const layoutBtns = document.querySelectorAll('[data-layout]');
    const LAYOUT_KEY = 'nexus.library.layout';

    function setLayout(layout) {
        if (!files) return;
        files.classList.toggle('is-list', layout === 'list');
        files.classList.toggle('is-grid', layout === 'grid');
        layoutBtns.forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.layout === layout)));
    }

    if (files) {
        let saved = 'list';
        try { saved = localStorage.getItem(LAYOUT_KEY) || 'list'; } catch (e) { /* storage blocked */ }
        setLayout(saved === 'grid' ? 'grid' : 'list');

        layoutBtns.forEach((b) => b.addEventListener('click', () => {
            setLayout(b.dataset.layout);
            try { localStorage.setItem(LAYOUT_KEY, b.dataset.layout); } catch (e) { /* storage blocked */ }
        }));
    }

    // ---- File preview pop-up ---------------------------------------------
    const modal = document.getElementById('libPreview');
    if (!modal) return;

    const q = (sel) => modal.querySelector(sel);
    const badge = q('[data-preview-badge]');
    const title = q('[data-preview-title]');
    const path = q('[data-preview-path]');
    const download = q('[data-preview-download]');
    const frame = q('[data-preview-frame]');
    const image = q('[data-preview-image]');
    const none = q('[data-preview-none]');
    const meta = q('[data-preview-meta]');
    let opener = null;

    function openPreview(btn) {
        const d = btn.dataset;
        opener = btn;

        badge.textContent = d.badge;
        badge.className = 'file-badge ' + d.color;
        title.textContent = d.title;
        path.textContent = d.path;
        meta.textContent = d.meta;
        download.href = d.downloadUrl;

        frame.hidden = d.kind !== 'pdf';
        image.hidden = d.kind !== 'image';
        none.hidden = d.kind === 'pdf' || d.kind === 'image';

        if (d.kind === 'pdf') {
            frame.title = 'Preview of ' + d.title;
            frame.src = d.viewUrl;
        } else if (d.kind === 'image') {
            image.alt = d.title;
            image.src = d.viewUrl;
        }

        modal.showModal();
    }

    function resetPreview() {
        frame.removeAttribute('src');
        image.removeAttribute('src');
        if (opener) opener.focus();
    }

    document.querySelectorAll('[data-preview]').forEach((btn) => {
        btn.addEventListener('click', () => openPreview(btn));
    });

    q('[data-preview-close]').addEventListener('click', () => modal.close());
    modal.addEventListener('close', resetPreview);
    // Click on the dimmed backdrop closes it
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.close();
    });
})();
