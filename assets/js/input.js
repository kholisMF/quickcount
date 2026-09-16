(function () {
    'use strict';

    firebase.initializeApp(window.FIREBASE_CONFIG);
    const db = firebase.database();
    const suaraRef = db.ref(window.DB_PATH + '/suara');

    const CANDIDATES = window.CANDIDATES;
    const TIDAK_SAH = window.TIDAK_SAH;
    const TPS_LIST = window.TPS_LIST;
    const ALL_IDS = [...CANDIDATES.map(c => c.id), TIDAK_SAH.id];

    const tpsSelect = document.getElementById('tps-select');
    const candidateFields = Array.from(document.querySelectorAll('.candidate-field'));
    const sumTotal = document.getElementById('sum-total');
    const sumDpt = document.getElementById('sum-dpt');
    const sumRow = document.getElementById('sum-row');
    const statusList = document.getElementById('status-list');
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');
    const syncStatus = document.getElementById('sync-status');
    const syncText = document.getElementById('sync-text');

    let currentData = {};       // snapshot cache from Firebase (source of truth for other TPS + status list)
    let selectedTps = null;     // id of TPS currently being edited
    let localValues = {};       // working values for the selected TPS { yarpan: 0, marta: 0, ... }
    let isSaving = false;
    let saveAgain = false;

    function fmt(n) {
        return new Intl.NumberFormat('id-ID').format(n || 0);
    }

    function showToast(message, type) {
        toastMsg.textContent = message;
        toast.className = 'toast show ' + (type || 'success');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => toast.classList.remove('show'), 3800);
    }

    function setSyncState(state, text) {
        syncStatus.className = 'sync-status ' + state;
        syncText.textContent = text;
    }

    function setFieldsEnabled(enabled) {
        candidateFields.forEach(f => f.classList.toggle('is-disabled', !enabled));
    }

    function currentTotal() {
        let total = 0;
        ALL_IDS.forEach(id => total += Number(localValues[id] || 0));
        return total;
    }

    function currentDpt() {
        const opt = tpsSelect.selectedOptions[0];
        return opt ? Number(opt.dataset.dpt || 0) : 0;
    }

    function recalcSum() {
        const total = currentTotal();
        const dpt = currentDpt();
        sumTotal.textContent = fmt(total);
        sumDpt.textContent = tpsSelect.value ? 'DPT ' + fmt(dpt) : 'DPT \u2014';
        sumRow.classList.toggle('warn', !!tpsSelect.value && total > dpt);
    }

    function renderValue(id) {
        const el = candidateFields.find(f => f.dataset.id === id).querySelector('.cf-value');
        el.textContent = fmt(localValues[id] || 0);
        el.classList.remove('bump');
        void el.offsetWidth; // restart bump animation
        el.classList.add('bump');
    }

    function renderAllValues() {
        ALL_IDS.forEach(id => {
            const el = candidateFields.find(f => f.dataset.id === id).querySelector('.cf-value');
            el.textContent = fmt(localValues[id] || 0);
        });
        recalcSum();
    }

    function loadTpsIntoLocal(tpsId) {
        const row = currentData[tpsId];
        localValues = {};
        ALL_IDS.forEach(id => localValues[id] = row ? Number(row[id] || 0) : 0);
        renderAllValues();
    }

    function renderStatusList() {
        statusList.innerHTML = TPS_LIST.map(tps => {
            const row = currentData[tps.id];
            const reported = !!row;
            const isSelected = selectedTps === tps.id;
            return `<div class="status-item ${isSelected ? 'selected' : ''}" data-id="${tps.id}">
                <div class="si-name">${tps.nama}</div>
                <span class="badge-status ${reported ? 'in' : 'out'}">
                    <span class="dot"></span>${reported ? 'Sudah lapor' : 'Belum lapor'}
                </span>
            </div>`;
        }).join('');

        statusList.querySelectorAll('.status-item').forEach(node => {
            node.addEventListener('click', () => {
                tpsSelect.value = node.dataset.id;
                tpsSelect.dispatchEvent(new Event('change'));
            });
        });
    }

    // ---------- Realtime listener: syncs status list + other TPS' data ----------
    suaraRef.on('value', (snapshot) => {
        currentData = snapshot.val() || {};
        renderStatusList();
    });

    // ---------- TPS selection ----------
    tpsSelect.addEventListener('change', () => {
        selectedTps = tpsSelect.value || null;
        renderStatusList();
        if (selectedTps) {
            loadTpsIntoLocal(selectedTps);
            setFieldsEnabled(true);
            setSyncState('', 'Siap \u2014 ketuk + / \u2212 untuk menghitung suara');
        } else {
            localValues = {};
            renderAllValues();
            setFieldsEnabled(false);
            setSyncState('', 'Pilih TPS untuk mulai input \u2014 setiap tambah/kurang otomatis tersimpan');
        }
    });

    // ---------- Stepper buttons ----------
    candidateFields.forEach(field => {
        const id = field.dataset.id;
        field.querySelectorAll('.step-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!selectedTps) return;
                const dir = Number(btn.dataset.dir);
                const currentVal = Number(localValues[id] || 0);

                if (dir < 0 && currentVal <= 0) return; // never go below 0

                if (dir > 0) {
                    const dpt = currentDpt();
                    if (dpt && currentTotal() + 1 > dpt) {
                        showToast('Total suara sudah mencapai DPT TPS ini (' + fmt(dpt) + ').', 'error');
                        return;
                    }
                }

                localValues[id] = currentVal + dir;
                renderValue(id);
                recalcSum();
                queueSave();
            });
        });
    });

    // ---------- Auto-save (sequential, always reflects latest local state) ----------
    function queueSave() {
        if (isSaving) {
            saveAgain = true;
            return;
        }
        doSave();
    }

    async function doSave() {
        if (!selectedTps) return;
        isSaving = true;
        saveAgain = false;
        setSyncState('saving', 'Menyimpan\u2026');

        const payload = { tps_id: selectedTps };
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

    setFieldsEnabled(false);
    recalcSum();
})();
