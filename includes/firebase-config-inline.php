<script>
    // Konfigurasi client Firebase (hanya untuk membaca / mendengarkan realtime).
    // Penulisan data dilakukan lewat save.php (PHP + Database Secret) agar aman.
    window.FIREBASE_CONFIG = {
        apiKey: <?= json_encode($cfg['firebase']['web_api_key']) ?>,
        authDomain: <?= json_encode($cfg['firebase']['auth_domain']) ?>,
        databaseURL: <?= json_encode($cfg['firebase']['database_url']) ?>,
        projectId: <?= json_encode($cfg['firebase']['project_id']) ?>,
    };
    window.DB_PATH = <?= json_encode($cfg['db_path']) ?>;
    window.CANDIDATES = <?= json_encode($cfg['candidates']) ?>;
    window.TIDAK_SAH = <?= json_encode($cfg['tidak_sah']) ?>;
    window.TPS_LIST = <?= json_encode($cfg['tps']) ?>;
    window.TOTAL_DPT = <?= json_encode($cfg['app']['total_dpt']) ?>;
</script>
