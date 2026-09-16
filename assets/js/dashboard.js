(function () {
    'use strict';

    // ---------- Init Firebase (read-only listener) ----------
    firebase.initializeApp(window.FIREBASE_CONFIG);
    const db = firebase.database();
    const suaraRef = db.ref(window.DB_PATH + '/suara');

    const CANDIDATES = window.CANDIDATES;
    const TIDAK_SAH = window.TIDAK_SAH;
    const TPS_LIST = window.TPS_LIST;
    const TOTAL_DPT = window.TOTAL_DPT;

    let pieChart = null;

    // ---------- DOM refs ----------
    const el = {
        lastUpdated: document.getElementById('last-updated'),
        statSuaraMasuk: document.getElementById('stat-suara-masuk'),
        barSuaraMasuk: document.getElementById('bar-suara-masuk'),
        statPartisipasi: document.getElementById('stat-partisipasi'),
        barPartisipasi: document.getElementById('bar-partisipasi'),
        statTpsLapor: document.getElementById('stat-tps-lapor'),
        barTpsLapor: document.getElementById('bar-tps-lapor'),
        statUnggul: document.getElementById('stat-unggul'),
        barUnggul: document.getElementById('bar-unggul'),
        legend: document.getElementById('legend'),
        leaderboard: document.getElementById('leaderboard'),
        tbody: document.getElementById('tps-table-body'),
        centerPct: document.getElementById('chart-center-pct'),
    };

    function fmt(n) {
        return new Intl.NumberFormat('id-ID').format(n || 0);
    }

    // ---------- Core: recompute + render on every realtime event ----------
    function handleSnapshot(snapshot) {
        const data = snapshot.val() || {}; // { tps01: {yarpan:.., marta:.., ...}, ... }

        // Aggregate totals per candidate + tidak sah
        const totals = {};
        CANDIDATES.forEach(c => totals[c.id] = 0);
        totals[TIDAK_SAH.id] = 0;

        let totalSuaraMasuk = 0;
        let tpsLaporCount = 0;
        const perTps = {};

        TPS_LIST.forEach(tps => {
            const row = data[tps.id];
            perTps[tps.id] = row || null;
            if (row) {
                tpsLaporCount++;
                CANDIDATES.forEach(c => {
                    const v = Number(row[c.id] || 0);
                    totals[c.id] += v;
                    totalSuaraMasuk += v;
                });
                const tv = Number(row[TIDAK_SAH.id] || 0);
                totals[TIDAK_SAH.id] += tv;
                totalSuaraMasuk += tv;
            }
        });

        renderStats(totalSuaraMasuk, tpsLaporCount, totals);
        renderPie(totals, totalSuaraMasuk);
        renderLegend(totals, totalSuaraMasuk);
        renderLeaderboard(totals, totalSuaraMasuk);
        renderTable(perTps);

        el.lastUpdated.textContent = 'Diperbarui otomatis ' + new Date().toLocaleTimeString('id-ID');
    }

    function renderStats(totalSuaraMasuk, tpsLaporCount, totals) {
        el.statSuaraMasuk.innerHTML = fmt(totalSuaraMasuk) + ' <small>/ ' + fmt(TOTAL_DPT) + '</small>';
        const pctMasuk = TOTAL_DPT ? (totalSuaraMasuk / TOTAL_DPT * 100) : 0;
        el.barSuaraMasuk.style.width = Math.min(pctMasuk, 100).toFixed(1) + '%';

        el.statPartisipasi.textContent = pctMasuk.toFixed(1) + '%';
        el.barPartisipasi.style.width = Math.min(pctMasuk, 100).toFixed(1) + '%';

        const tpsPct = TPS_LIST.length ? (tpsLaporCount / TPS_LIST.length * 100) : 0;
        el.statTpsLapor.innerHTML = tpsLaporCount + ' <small>/ ' + TPS_LIST.length + ' TPS</small>';
        el.barTpsLapor.style.width = tpsPct.toFixed(1) + '%';

        let leader = null;
        CANDIDATES.forEach(c => {
            if (!leader || totals[c.id] > totals[leader.id]) leader = c;
        });
        if (leader && totals[leader.id] > 0) {
            const leaderPct = totalSuaraMasuk ? (totals[leader.id] / totalSuaraMasuk * 100) : 0;
            el.statUnggul.textContent = leader.nama;
            el.statUnggul.style.color = leader.warna;
            el.barUnggul.style.width = leaderPct.toFixed(1) + '%';
            el.barUnggul.style.background = leader.warna;
        } else {
            el.statUnggul.textContent = '\u2014';
            el.barUnggul.style.width = '0%';
        }
    }

    function renderPie(totals, totalSuaraMasuk) {
        const labels = [...CANDIDATES.map(c => c.nama), TIDAK_SAH.nama];
        const colors = [...CANDIDATES.map(c => c.warna), TIDAK_SAH.warna];
        const values = [...CANDIDATES.map(c => totals[c.id]), totals[TIDAK_SAH.id]];

        const hasData = totalSuaraMasuk > 0;
        const dataValues = hasData ? values : [1];
        const dataColors = hasData ? colors : ['#1C2740'];
        const dataLabels = hasData ? labels : ['Belum ada data'];

        if (pieChart) {
            pieChart.data.labels = dataLabels;
            pieChart.data.datasets[0].data = dataValues;
            pieChart.data.datasets[0].backgroundColor = dataColors;
            pieChart.update();
        } else {
            const ctx = document.getElementById('pieChart').getContext('2d');
            pieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: dataLabels,
                    datasets: [{
                        data: dataValues,
                        backgroundColor: dataColors,
                        borderColor: '#0F172A',
                        borderWidth: 3,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: hasData,
                            callbacks: {
                                label: (ctx) => {
                                    const v = ctx.parsed;
                                    const pct = totalSuaraMasuk ? (v / totalSuaraMasuk * 100).toFixed(1) : 0;
                                    return ' ' + ctx.label + ': ' + fmt(v) + ' suara (' + pct + '%)';
                                }
                            }
                        }
                    },
                    animation: { duration: 500 },
                }
            });
        }

        const pctOfDpt = TOTAL_DPT ? (totalSuaraMasuk / TOTAL_DPT * 100) : 0;
        el.centerPct.textContent = pctOfDpt.toFixed(1) + '%';
    }

    function renderLegend(totals, totalSuaraMasuk) {
        const items = [...CANDIDATES, TIDAK_SAH];
        el.legend.innerHTML = items.map(c => {
            const v = totals[c.id] || 0;
            const pct = totalSuaraMasuk ? (v / totalSuaraMasuk * 100) : 0;
            return `<div class="legend-row">
                <span class="legend-dot" style="background:${c.warna}"></span>
                <span class="legend-name">${c.nama}</span>
                <span class="legend-votes">${fmt(v)} suara</span>
                <span class="legend-pct">${pct.toFixed(1)}%</span>
            </div>`;
        }).join('');
    }

    function renderLeaderboard(totals, totalSuaraMasuk) {
        const ranked = [...CANDIDATES].sort((a, b) => totals[b.id] - totals[a.id]);
        el.leaderboard.innerHTML = ranked.map((c, i) => {
            const v = totals[c.id] || 0;
            const pct = totalSuaraMasuk ? (v / totalSuaraMasuk * 100) : 0;
            return `<div class="leader-row">
                <div class="leader-rank">${i + 1}</div>
                <div class="leader-info">
                    <div class="leader-name">No. ${c.no} &middot; ${c.nama}</div>
                    <div class="leader-meta">${fmt(v)} suara</div>
                    <div class="leader-track"><span style="width:${pct.toFixed(1)}%;background:${c.warna}"></span></div>
                </div>
                <div class="leader-pct" style="color:${c.warna}">${pct.toFixed(1)}%</div>
            </div>`;
        }).join('');
    }

    function renderTable(perTps) {
        el.tbody.innerHTML = TPS_LIST.map(tps => {
            const row = perTps[tps.id];
            const cells = CANDIDATES.map(c => `<td class="num">${row ? fmt(row[c.id] || 0) : '&mdash;'}</td>`).join('');
            const tidakSah = row ? Number(row[TIDAK_SAH.id] || 0) : 0;
            let total = 0;
            if (row) {
                CANDIDATES.forEach(c => total += Number(row[c.id] || 0));
                total += tidakSah;
            }
            const statusBadge = row
                ? `<span class="badge-status in"><span class="dot"></span>Sudah lapor</span>`
                : `<span class="badge-status out"><span class="dot"></span>Belum lapor</span>`;

            return `<tr>
                <td>
                    <div class="tps-name">${tps.nama}</div>
                </td>
                ${cells}
                <td class="num">${row ? fmt(tidakSah) : '&mdash;'}</td>
                <td class="num"><b>${row ? fmt(total) : '&mdash;'}</b></td>
                <td class="num">${fmt(tps.dpt)}</td>
                <td>${statusBadge}</td>
            </tr>`;
        }).join('');
    }

    // ---------- Event handler: fires on every insert/update in Firebase ----------
    suaraRef.on('value', handleSnapshot, (error) => {
        el.lastUpdated.textContent = 'Gagal memuat data realtime: ' + error.message;
    });
})();
