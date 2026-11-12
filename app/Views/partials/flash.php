<?php
/**
 * Pop-up notice for the result of a login / register / logout.
 * Reads the 'success' or 'error' flash message set by the controller.
 * Styles live in assets/css/login.css (.flash-toast).
 */
$flashType    = session()->getFlashdata('success') ? 'success' : (session()->getFlashdata('error') ? 'error' : null);
$flashMessage = $flashType ? session()->getFlashdata($flashType) : null;
?>
<?php if ($flashType): ?>
    <div class="flash-toast flash-<?= $flashType ?>" role="<?= $flashType === 'error' ? 'alert' : 'status' ?>" aria-live="polite">
        <i class="fa-solid <?= $flashType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> flash-icon" aria-hidden="true"></i>
        <div class="flash-text">
            <strong><?= $flashType === 'success' ? 'Success' : 'Something went wrong' ?></strong>
            <span><?= esc($flashMessage) ?></span>
        </div>
        <button type="button" class="flash-close" aria-label="Dismiss message">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
        <span class="flash-timer" aria-hidden="true"></span>
    </div>

    <script>
        (function () {
            const toast = document.currentScript.previousElementSibling;
            const close = () => {
                toast.classList.add('flash-hide');
                setTimeout(() => toast.remove(), 300);
            };
            toast.querySelector('.flash-close').addEventListener('click', close);
            setTimeout(close, 6000);
        })();
    </script>
<?php endif; ?>
