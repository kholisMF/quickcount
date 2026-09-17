(function () {
    'use strict';

    const candidateRows = document.getElementById('candidate-rows');
    const btnAddCandidate = document.getElementById('btn-add-candidate');
    const tidakSahColor = document.getElementById('tidaksah-color');
    const tidakSahName = document.getElementById('tidaksah-name');

    const tpsCountDisplay = document.getElementById('tps-count-display');
    const tpsCountMinus = document.getElementById('tps-count-minus');
    const tpsCountPlus = document.getElementById('tps-count-plus');
    const tpsDptRows = document.getElementById('tps-dpt-rows');
    const tpsSumTotal = document.getElementById('tps-sum-total');
    const tpsSumRow = document.getElementById('tps-sum-row');
    const btnCopySum = document.getElementById('btn-copy-sum');
    const totalDptInput = document.getElementById('total-dpt');

    const btnSave = document.getElementById('btn-save-settings');
    const statusEl = document.getElementById('settings-status');
    const statusText = document.getElementById('settings-sync-text');

    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');

    function fmt(n) {
        return new Intl.NumberFormat('id-ID').format(n || 0);
    }

    function showToast(message, type) {
        toastMsg.textContent = message;
        toast.className = 'toast show ' + (type || 'success');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => toast.classList.remove('show'), 3800);
    }

    function markDirty() {
        statusEl.className = 'sync-status';
        statusText.textContent = 'Ada perubahan belum disimpan';
    }

    function genId() {
        return 'cand' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
    }

    // ---------- Candidate rows ----------
    function addCandidateRow(id, nama, warna) {
        const row = document.createElement('div');
        row.className = 'cand-row';
        row.dataset.id = id || genId();
        row.innerHTML = `
            <input type="color" class="cand-color" value="${warna || '#D4A537'}" title="Warna calon">
            <input type="text" class="cand-name" value="${nama || ''}" placeholder="Nama calon">
            <button type="button" class="row-remove" title="Hapus calon">&times;</button>
        `;
        candidateRows.appendChild(row);
        bindRowRemove(row);
        return row;
    }

    function bindRowRemove(row) {
        row.querySelector('.row-remove').addEventListener('click', () => {
            if (candidateRows.querySelectorAll('.cand-row').length <= 2) {
                showToast('Minimal harus ada 2 calon.', 'error');
                return;
            }
            row.remove();
            markDirty();
        });
    }

    candidateRows.querySelectorAll('.cand-row').forEach(bindRowRemove);

    btnAddCandidate.addEventListener('click', () => {
        addCandidateRow(null, '', '#D4A537');
        markDirty();
    });

    // ---------- TPS rows ----------
    function tpsRowCount() {
        return tpsDptRows.querySelectorAll('.tps-dpt-row').length;
    }

    function renumberTpsRows() {
        tpsDptRows.querySelectorAll('.tps-dpt-row').forEach((row, i) => {
            row.querySelector('.tps-dpt-label').textContent = 'TPS ' + (i + 1);
        });
        tpsCountDisplay.textContent = tpsRowCount();
    }

    function addTpsRow(dpt) {
        const row = document.createElement('div');
        row.className = 'tps-dpt-row';
        row.innerHTML = `
            <span class="tps-dpt-label">TPS</span>
            <input type="number" min="0" step="1" class="tps-dpt-input" value="${dpt || 0}">
        `;
        tpsDptRows.appendChild(row);
        row.querySelector('.tps-dpt-input').addEventListener('input', () => {
            recalcTpsSum();
            markDirty();
        });
        return row;
    }

    tpsCountPlus.addEventListener('click', () => {
        if (tpsRowCount() >= 50) {
            showToast('Maksimal 50 TPS.', 'error');
            return;
        }
        addTpsRow(0);
        renumberTpsRows();
        recalcTpsSum();
        markDirty();
    });

    tpsCountMinus.addEventListener('click', () => {
        if (tpsRowCount() <= 1) {
            showToast('Minimal harus ada 1 TPS.', 'error');
            return;
        }
        const rows = tpsDptRows.querySelectorAll('.tps-dpt-row');
        rows[rows.length - 1].remove();
        renumberTpsRows();
        recalcTpsSum();
        markDirty();
    });

    tpsDptRows.querySelectorAll('.tps-dpt-input').forEach(inp => {
        inp.addEventListener('input', () => {
            recalcTpsSum();
            markDirty();
        });
    });

    function recalcTpsSum() {
        let sum = 0;
        tpsDptRows.querySelectorAll('.tps-dpt-input').forEach(inp => sum += Number(inp.value || 0));
        tpsSumTotal.textContent = fmt(sum);
        const totalDpt = Number(totalDptInput.value || 0);
        tpsSumRow.classList.toggle('warn', sum !== totalDpt);
        return sum;
    }

    btnCopySum.addEventListener('click', () => {
        totalDptInput.value = recalcTpsSum();
        recalcTpsSum();
        markDirty();
    });

    totalDptInput.addEventListener('input', () => {
        recalcTpsSum();
        markDirty();
    });

    [tidakSahColor, tidakSahName].forEach(el => el.addEventListener('input', markDirty));

    // ---------- Save ----------
    btnSave.addEventListener('click', async () => {
        const candidates = Array.from(candidateRows.querySelectorAll('.cand-row')).map(row => ({
            id: row.dataset.id,
            nama: row.querySelector('.cand-name').value.trim(),
            warna: row.querySelector('.cand-color').value,
        }));

        if (candidates.some(c => c.nama === '')) {
            showToast('Nama calon tidak boleh kosong.', 'error');
            return;
        }

        const tps = Array.from(tpsDptRows.querySelectorAll('.tps-dpt-input')).map(inp => ({
            dpt: Number(inp.value || 0),
        }));

        const payload = {
            password: (document.getElementById('settings-token') || {}).value || '',
            candidates,
            tidak_sah: {
                nama: tidakSahName.value.trim(),
                warna: tidakSahColor.value,
            },
            tps,
            total_dpt: Number(totalDptInput.value || 0),
        };

        btnSave.disabled = true;
        btnSave.textContent = 'Menyimpan\u2026';
        statusEl.className = 'sync-status saving';
        statusText.textContent = 'Menyimpan pengaturan\u2026';

        try {
            const res = await fetch('save_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (!res.ok || !json.ok) {
                throw new Error(json.message || 'Gagal menyimpan pengaturan.');
            }
            statusEl.className = 'sync-status saved';
            statusText.textContent = 'Tersimpan \u00b7 ' + new Date().toLocaleTimeString('id-ID');
            showToast('Pengaturan berhasil disimpan.', 'success');
        } catch (err) {
            statusEl.className = 'sync-status error';
            statusText.textContent = err.message;
            showToast(err.message, 'error');
        } finally {
            btnSave.disabled = false;
            btnSave.textContent = 'Simpan Pengaturan';
        }
    });

    recalcTpsSum();
})();