<script>
    <?php
    $cfg = isset($cfg) && is_array($cfg) ? $cfg : [];
    $firebase = is_array($cfg['firebase'] ?? null) ? $cfg['firebase'] : [];
    $app = is_array($cfg['app'] ?? null) ? $cfg['app'] : [];
    ?>
    window.FIREBASE_CONFIG = {
        apiKey: <?= json_encode($firebase['web_api_key'] ?? null) ?>,
        authDomain: <?= json_encode($firebase['auth_domain'] ?? null) ?>,
        databaseURL: <?= json_encode($firebase['database_url'] ?? null) ?>,
        projectId: <?= json_encode($firebase['project_id'] ?? null) ?>,
    };
    window.DB_PATH = <?= json_encode($cfg['db_path'] ?? null) ?>;
    window.CANDIDATES = <?= json_encode($cfg['candidates'] ?? []) ?>;
    window.TIDAK_SAH = <?= json_encode($cfg['tidak_sah'] ?? null) ?>;
    window.TPS_LIST = <?= json_encode($cfg['tps'] ?? []) ?>;
    window.TOTAL_DPT = <?= json_encode($app['total_dpt'] ?? null) ?>;
</script>
