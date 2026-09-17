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

    function genTpsId() {
        return 'tps' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
    }

    // ---------- Candidate rows ----------
    function addCandidateRow(id, no, nama, warna) {
        const row = document.createElement('div');
        row.className = 'cand-row';
        row.dataset.id = id || genId();
        row.innerHTML = `
            <div class="cand-field cand-field--no">
                <label>No.</label>
                <input type="number" min="1" step="1" class="cand-no" value="${no || ''}" placeholder="0">
            </div>
            <div class="cand-field cand-field--color">
                <label>Warna</label>
                <input type="color" class="cand-color" value="${warna || '#2563EB'}" title="Warna calon">
            </div>
            <div class="cand-field cand-field--name">
                <label>Nama Calon</label>
                <input type="text" class="cand-name" value="${nama || ''}" placeholder="Nama calon" autocomplete="off">
            </div>
            <button type="button" class="row-remove" title="Hapus calon">&times;</button>
        `;
        candidateRows.appendChild(row);
        bindRowRemove(row);
        bindCandidateRowInputs(row);
        return row;
    }

    function bindCandidateRowInputs(row) {
        row.querySelectorAll('input').forEach(inp => {
            inp.addEventListener('input', markDirty);
            inp.addEventListener('change', markDirty);
        });
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

    candidateRows.querySelectorAll('.cand-row').forEach(row => {
        bindRowRemove(row);
        bindCandidateRowInputs(row);
    });

    btnAddCandidate.addEventListener('click', () => {
        const existingNos = Array.from(candidateRows.querySelectorAll('.cand-no'))
            .map(inp => Number(inp.value || 0));
        const nextNo = existingNos.length ? Math.max(...existingNos) + 1 : 1;
        addCandidateRow(null, nextNo, '', '#2563EB');
        markDirty();
    });

    // ---------- TPS rows ----------
    function tpsRowCount() {
        return tpsDptRows.querySelectorAll('.tps-card').length;
    }

    function renumberTpsRows() {
        tpsDptRows.querySelectorAll('.tps-card').forEach((row, i) => {
            row.querySelector('.tps-card-num').textContent = 'TPS ' + (i + 1);
        });
        tpsCountDisplay.textContent = tpsRowCount();
    }

    function bindTpsCardInputs(card) {
        card.querySelectorAll('input').forEach(inp => {
            inp.addEventListener('input', () => {
                recalcTpsSum();
                markDirty();
            });
        });
        card.querySelector('.tps-card-remove').addEventListener('click', () => {
            if (tpsRowCount() <= 1) {
                showToast('Minimal harus ada 1 TPS.', 'error');
                return;
            }
            card.remove();
            renumberTpsRows();
            recalcTpsSum();
            markDirty();
        });
    }

    function addTpsCard(data) {
        const d = data || {};
        const n = tpsRowCount() + 1;
        const card = document.createElement('div');
        card.className = 'tps-card';
        card.dataset.id = d.id || genTpsId();
        card.innerHTML = `
            <div class="tps-card-head">
                <span class="tps-card-num">TPS ${n}</span>
                <button type="button" class="tps-card-remove" title="Hapus TPS ini">&times;</button>
            </div>
            <div class="tps-card-grid">
                <div class="tps-field">
                    <label>Nama TPS</label>
                    <input type="text" class="tps-nama-input" value="${d.nama || ('TPS ' + n)}" placeholder="Contoh: TPS 1 Dusun Krajan" autocomplete="off">
                </div>
                <div class="tps-field">
                    <label>Jumlah DPT</label>
                    <input type="number" min="0" step="1" class="tps-dpt-input" value="${d.dpt || 0}" placeholder="0">
                </div>
                <div class="tps-field">
                    <label>Nama Saksi / Petugas</label>
                    <input type="text" class="tps-saksi-input" value="${d.saksi || ''}" placeholder="Contoh: Budi Santoso" autocomplete="off">
                </div>
                <div class="tps-field">
                    <label>Username Login</label>
                    <input type="text" class="tps-user-input" value="${d.username || ('tps' + n)}" placeholder="username" autocomplete="off">
                </div>
                <div class="tps-field">
                    <label>Password Login</label>
                    <input type="text" class="tps-pass-input" value="${d.password || ('tps' + n)}" placeholder="password" autocomplete="off">
                </div>
            </div>
        `;
        tpsDptRows.appendChild(card);
        bindTpsCardInputs(card);
        return card;
    }

    tpsCountPlus.addEventListener('click', () => {
        if (tpsRowCount() >= 50) {
            showToast('Maksimal 50 TPS.', 'error');
            return;
        }
        addTpsCard(null);
        renumberTpsRows();
        recalcTpsSum();
        markDirty();
    });

    tpsCountMinus.addEventListener('click', () => {
        if (tpsRowCount() <= 1) {
            showToast('Minimal harus ada 1 TPS.', 'error');
            return;
        }
        const cards = tpsDptRows.querySelectorAll('.tps-card');
        cards[cards.length - 1].remove();
        renumberTpsRows();
        recalcTpsSum();
        markDirty();
    });

    // Bind initial TPS cards
    tpsDptRows.querySelectorAll('.tps-card').forEach(bindTpsCardInputs);

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
            no:    Number(row.querySelector('.cand-no').value || 0),
            nama: row.querySelector('.cand-name').value.trim(),
            warna: row.querySelector('.cand-color').value,
        }));

        if (candidates.some(c => c.nama === '')) {
            showToast('Nama calon tidak boleh kosong.', 'error');
            return;
        }

        if (candidates.some(c => !c.no || c.no < 1)) {
            showToast('Nomor urut tiap calon wajib diisi (minimal 1).', 'error');
            return;
        }

        const nos = candidates.map(c => c.no);
        if (new Set(nos).size !== nos.length) {
            showToast('Nomor urut antar calon tidak boleh sama.', 'error');
            return;
        }

        const tps = Array.from(tpsDptRows.querySelectorAll('.tps-card')).map((card, i) => ({
            id:       card.dataset.id,
            nama:     card.querySelector('.tps-nama-input').value.trim() || ('TPS ' + (i + 1)),
            dpt:      Number(card.querySelector('.tps-dpt-input').value || 0),
            saksi:    card.querySelector('.tps-saksi-input').value.trim(),
            username: card.querySelector('.tps-user-input').value.trim(),
            password: card.querySelector('.tps-pass-input').value.trim(),
        }));

        if (tps.some(t => !t.username || !t.password)) {
            showToast('Username & password tiap TPS wajib diisi.', 'error');
            return;
        }

        const usernames = tps.map(t => t.username.toLowerCase());
        if (new Set(usernames).size !== usernames.length) {
            showToast('Username antar TPS tidak boleh sama.', 'error');
            return;
        }

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