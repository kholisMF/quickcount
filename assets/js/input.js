(function () {
    'use strict';

    if (!window.TPS_ID) {
        return;
    }

    firebase.initializeApp(window.FIREBASE_CONFIG);
    const db = firebase.database();
    const suaraRef = db.ref(window.DB_PATH + '/suara/' + window.TPS_ID);

    const CANDIDATES = window.CANDIDATES || [];
    const TIDAK_SAH = window.TIDAK_SAH || { id: 'tidaksah', nama: 'Suara Tidak Sah' };
    const ALL_IDS = [...CANDIDATES.map(c => c.id), TIDAK_SAH.id];
    const TPS_DPT = Number(window.TPS_DPT || 0);

    const candidateFields = Array.from(document.querySelectorAll('.candidate-field'));
    const sumTotal = document.getElementById('sum-total');
    const sumDpt   = document.getElementById('sum-dpt');
    const sumRow   = document.getElementById('sum-row');
    const toast    = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');
    const syncStatus = document.getElementById('sync-status');
    const syncText   = document.getElementById('sync-text');

    let localValues = {};
    let isSaving = false;
    let saveAgain = false;

    function fmt(n) {
        return new Intl.NumberFormat('id-ID').format(n || 0);
    }

    function showToast(message, type) {
        if (!toast || !toastMsg) return;
        toastMsg.textContent = message;
        toast.className = 'toast show ' + (type || 'success');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => toast.classList.remove('show'), 3800);
    }

    function setSyncState(state, text) {
        if (!syncStatus) return;
        syncStatus.className = 'sync-status ' + state;
        if (syncText) syncText.textContent = text;
    }

    function currentTotal() {
        let total = 0;
        ALL_IDS.forEach(id => total += Number(localValues[id] || 0));
        return total;
    }

    function recalcSum() {
        const total = currentTotal();
        if (sumTotal) sumTotal.textContent = fmt(total);
        if (sumDpt)   sumDpt.textContent = 'DPT ' + fmt(TPS_DPT);
        if (sumRow)   sumRow.classList.toggle('warn', TPS_DPT > 0 && total > TPS_DPT);
    }

    function findField(id) {
        return candidateFields.find(f => f.dataset.id === id);
    }

    function renderValue(id) {
        const field = findField(id);
        if (!field) return;
        const el = field.querySelector('.cf-value');
        if (!el) return;
        el.textContent = fmt(localValues[id] || 0);
        el.classList.remove('bump');
        void el.offsetWidth;
        el.classList.add('bump');
    }

    function renderAllValues() {
        ALL_IDS.forEach(id => {
            const field = findField(id);
            if (!field) return;
            const el = field.querySelector('.cf-value');
            if (el) el.textContent = fmt(localValues[id] || 0);
        });
        recalcSum();
    }

    // ---------- Load data awal dari Firebase (hanya TPS milik sendiri) ----------
    suaraRef.once('value').then(snap => {
        const row = snap.val() || {};
        localValues = {};
        ALL_IDS.forEach(id => localValues[id] = Number(row[id] || 0));
        renderAllValues();
        setSyncState('', 'Siap \u2014 ketuk + / \u2212 untuk menghitung suara');
    }).catch(err => {
        setSyncState('error', 'Gagal memuat data: ' + err.message);
    });

    // ---------- Stepper buttons ----------
    candidateFields.forEach(field => {
        const id = field.dataset.id;
        field.querySelectorAll('.step-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const dir = Number(btn.dataset.dir);
                const currentVal = Number(localValues[id] || 0);

                if (dir < 0 && currentVal <= 0) return;

                if (dir > 0 && TPS_DPT > 0 && currentTotal() + 1 > TPS_DPT) {
                    showToast('Total suara sudah mencapai DPT TPS ini (' + fmt(TPS_DPT) + ').', 'error');
                    return;
                }

                localValues[id] = currentVal + dir;
                renderValue(id);
                recalcSum();
                queueSave();
            });
        });
    });

    // ---------- Auto-save ----------
    function queueSave() {
        if (isSaving) {
            saveAgain = true;
            return;
        }
        doSave();
    }

    async function doSave() {
        isSaving = true;
        saveAgain = false;
        setSyncState('saving', 'Menyimpan\u2026');

        const payload = { tps_id: window.TPS_ID };
        ALL_IDS.forEach(id => payload[id] = Number(localValues[id] || 0));

        try {
            const res = await fetch('save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (!res.ok || !json.ok) {
                throw new Error(json.message || 'Gagal menyimpan data.');
            }
            const now = new Date().toLocaleTimeString('id-ID');
            setSyncState('saved', 'Tersimpan otomatis \u00b7 ' + now);
        } catch (err) {
            setSyncState('error', err.message);
            showToast(err.message, 'error');
        } finally {
            isSaving = false;
            if (saveAgain) doSave();
        }
    }

    renderAllValues();
    setSyncState('', 'Memuat data\u2026');
})();