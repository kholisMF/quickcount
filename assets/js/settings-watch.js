(function () {
    'use strict';
    // Firebase app is already initialized by dashboard.js / input.js on this page.
    if (typeof firebase === 'undefined' || !firebase.apps || !firebase.apps.length) return;

    const db = firebase.database();
    const settingsRef = db.ref(window.DB_PATH + '/settings');

    let first = true;
    settingsRef.on('value', () => {
        if (first) { first = false; return; } // skip the initial snapshot on page load
        // Pengaturan (calon/TPS/DPT) berubah di tempat lain — muat ulang supaya
        // halaman ini selalu sinkron tanpa perlu refresh manual.
        location.reload();
    });
})();
