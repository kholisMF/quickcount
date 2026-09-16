(function () {
    'use strict';

    const openBtn = document.getElementById('btn-open-reset');
    const overlay = document.getElementById('reset-overlay');
    const cancelBtn = document.getElementById('btn-cancel-reset');
    const confirmBtn = document.getElementById('btn-confirm-reset');
    const passwordInput = document.getElementById('reset-password');
    const errorEl = document.getElementById('reset-error');

    if (!openBtn || !overlay) return; // navbar not present on this page

    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');

    function showToast(message, type) {
        if (!toast) return;
        toastMsg.textContent = message;
        toast.className = 'toast show ' + (type || 'success');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => toast.classList.remove('show'), 3800);
    }

    function openModal() {
        overlay.classList.add('show');
        errorEl.textContent = '';
        passwordInput.value = '';
        setTimeout(() => passwordInput.focus(), 80);
    }

    function closeModal() {
        if (confirmBtn.disabled) return; // don't allow closing mid-request
        overlay.classList.remove('show');
    }

    openBtn.addEventListener('click', openModal);
    cancelBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('show')) closeModal();
    });

    passwordInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') confirmBtn.click();
    });

    confirmBtn.addEventListener('click', async () => {
        const password = passwordInput.value;
        if (!password) {
            errorEl.textContent = 'Password wajib diisi.';
            return;
        }

        confirmBtn.disabled = true;
        cancelBtn.disabled = true;
        confirmBtn.textContent = 'Mereset\u2026';
        errorEl.textContent = '';

        try {
            const res = await fetch('reset.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password }),
            });
            const json = await res.json();
            if (!res.ok || !json.ok) {
                throw new Error(json.message || 'Gagal mereset data.');
            }
            overlay.classList.remove('show');
            showToast('Seluruh data quick count berhasil direset.', 'success');
        } catch (err) {
            errorEl.textContent = err.message;
        } finally {
            confirmBtn.disabled = false;
            cancelBtn.disabled = false;
            confirmBtn.textContent = 'Ya, Reset Data';
        }
    });
})();
