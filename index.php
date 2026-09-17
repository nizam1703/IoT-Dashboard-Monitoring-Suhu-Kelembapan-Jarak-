<?php
include "config.php";

// =====================================
// AMBIL DATA TERBARU
// =====================================
$queryTerbaru = "SELECT * FROM sensor ORDER BY id DESC LIMIT 1";
$resultTerbaru = mysqli_query($conn, $queryTerbaru);
$dataTerbaru = mysqli_fetch_assoc($resultTerbaru);

// Kalau belum ada data
if (!$dataTerbaru) {
    $dataTerbaru = [
        "suhu" => 0,
        "kelembapan" => 0,
        "jarak" => 0,
        "waktu" => "-"
    ];
}

// =====================================
// DATA UNTUK GRAFIK
// =====================================
$queryChart = "SELECT waktu, suhu, kelembapan, jarak FROM sensor ORDER BY id DESC LIMIT 20";
$resultChart = mysqli_query($conn, $queryChart);
$dataChart = [];

while ($row = mysqli_fetch_assoc($resultChart)) {
    $dataChart[] = $row;
}

// Balik urutan supaya grafik dari lama -> baru
$dataChart = array_reverse($dataChart);

// =====================================
// STATUS SENSOR
// =====================================
$statusSuhu = $dataTerbaru["suhu"] >= $BATAS_SUHU ? "BAHAYA" : "NORMAL";
$statusKelembapan = $dataTerbaru["kelembapan"] >= $BATAS_KELEMBAPAN ? "BAHAYA" : "NORMAL";
$statusJarak = $dataTerbaru["jarak"] <= $BATAS_JARAK ? "TERLALU DEKAT" : "NORMAL";

$semuaNormal = (
    $statusSuhu === "NORMAL" &&
    $statusKelembapan === "NORMAL" &&
    $statusJarak === "NORMAL"
);

$chartJson = json_encode($dataChart);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="5">
    <title>IoT Monitoring Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #020b1f;
            --panel: #07183a;
            --panel-2: #091e46;
            --border: rgba(59, 130, 246, .28);
            --text: #f4f8ff;
            --muted: #8ea7ce;
            --blue: #22a7ff;
            --cyan: #24d9ff;
            --green: #21e6a3;
            --red: #ff4d6d;
            --yellow: #ffc857;
            --purple: #8b6cff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 80% 0%, rgba(28, 93, 190, .20), transparent 32%),
                radial-gradient(circle at 15% 100%, rgba(30, 76, 170, .15), transparent 28%),
                var(--bg);
        }

        .container {
            width: min(1450px, calc(100% - 40px));
            margin: 0 auto;
            padding: 24px 0 34px;
        }

        /* HEADER */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 4px 0 24px;
            border-bottom: 1px solid rgba(93, 142, 216, .20);
            margin-bottom: 24px;
        }

        .brand h1 {
            margin: 0;
            font-size: clamp(1.6rem, 2.5vw, 2.25rem);
            font-weight: 700;
            letter-spacing: -.5px;
        }

        .brand h1 span {
            color: var(--blue);
        }

        .brand p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: .9rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .online {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--green);
            font-size: .86rem;
            font-weight: 600;
        }

        .online-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 14px rgba(33, 230, 163, .8);
        }

        .last-update {
            padding-left: 22px;
            border-left: 1px solid rgba(93, 142, 216, .25);
        }

        .last-update .label {
            color: var(--muted);
            font-size: .72rem;
            margin-bottom: 1px;
        }

        .last-update .time {
            font-size: .92rem;
            font-weight: 600;
        }

        .last-update .date {
            color: var(--muted);
            font-size: .68rem;
        }

        /* SENSOR CARDS */
        .sensor-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 20px;
        }

        .sensor-card {
            position: relative;
            overflow: hidden;
            padding: 23px;
            min-height: 165px;
            background: linear-gradient(145deg, rgba(9, 30, 70, .96), rgba(4, 17, 42, .96));
            border: 1px solid var(--border);
            border-radius: 17px;
            box-shadow: 0 16px 35px rgba(0, 0, 0, .25);
        }

        .sensor-card::after {
            content: "";
            position: absolute;
            right: -45px;
            top: -55px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(34, 167, 255, .06);
        }

        .sensor-card.humidity::after {
            background: rgba(33, 230, 163, .06);
        }

        .sensor-card.distance::after {
            background: rgba(139, 108, 255, .08);
        }

        .sensor-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sensor-name {
            display: flex;
            align-items: center;
            gap: 11px;
            color: #b9cbea;
            font-size: .9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .sensor-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: rgba(34, 167, 255, .14);
            color: var(--cyan);
            font-size: 1.3rem;
        }

        .humidity .sensor-icon {
            background: rgba(33, 230, 163, .12);
            color: var(--green);
        }

        .distance .sensor-icon {
            background: rgba(139, 108, 255, .14);
            color: #a891ff;
        }

        .sensor-wave {
            font-size: 1.45rem;
            color: var(--blue);
            opacity: .9;
        }

        .humidity .sensor-wave {
            color: var(--green);
        }

        .distance .sensor-wave {
            color: #9b83ff;
        }

        .value {
            margin: 13px 0 4px 53px;
            font-size: 2.35rem;
            line-height: 1;
            font-weight: 700;
        }

        .unit {
            color: var(--muted);
            font-size: .9rem;
            font-weight: 400;
        }

        .limit {
            margin-left: 53px;
            color: var(--muted);
            font-size: .7rem;
        }

        .status {
            position: absolute;
            right: 22px;
            bottom: 22px;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .4px;
        }

        .normal {
            color: var(--green);
            background: rgba(33, 230, 163, .12);
            border: 1px solid rgba(33, 230, 163, .35);
        }

        .danger {
            color: var(--red);
            background: rgba(255, 77, 109, .12);
            border: 1px solid rgba(255, 77, 109, .35);
        }

        /* CHARTS */
        .chart-section {
            padding: 20px;
            background: rgba(7, 24, 58, .88);
            border: 1px solid var(--border);
            border-radius: 17px;
            box-shadow: 0 16px 35px rgba(0, 0, 0, .20);
            margin-bottom: 20px;
        }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            font-size: 1.05rem;
        }

        .section-title .chart-icon {
            color: var(--blue);
        }

        .range {
            display: flex;
            gap: 7px;
        }

        .range span {
            padding: 6px 12px;
            border-radius: 8px;
            color: var(--muted);
            background: rgba(255,255,255,.025);
            border: 1px solid rgba(255,255,255,.04);
            font-size: .68rem;
        }

        .range span.active {
            color: white;
            background: #176de0;
            border-color: #2385ff;
        }

        .charts {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .chart-box {
            min-width: 0;
            padding: 15px;
            border-radius: 13px;
            background: rgba(2, 11, 31, .58);
            border: 1px solid rgba(65, 115, 188, .17);
        }

        .chart-box h3 {
            margin: 0 0 10px;
            font-size: .8rem;
            font-weight: 600;
            color: #dbe8ff;
        }

        canvas {
            display: block;
            width: 100%;
            height: 190px;
        }

        /* BOTTOM */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 18px;
        }

        .system-card,
        .log-card {
            padding: 22px;
            background: linear-gradient(145deg, rgba(8, 27, 65, .96), rgba(3, 15, 38, .96));
            border: 1px solid var(--border);
            border-radius: 17px;
        }

        .system-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .system-check {
            flex: 0 0 82px;
            width: 82px;
            height: 82px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            font-size: 2.1rem;
            color: var(--green);
            background: rgba(33, 230, 163, .10);
            border: 1px solid rgba(33, 230, 163, .35);
            box-shadow: 0 0 35px rgba(33, 230, 163, .10);
        }

        .system-content h2 {
            margin: 0 0 5px;
            font-size: 1.15rem;
        }

        .system-content .system-ok {
            color: var(--green);
        }

        .system-content p {
            margin: 0;
            color: var(--muted);
            font-size: .75rem;
            line-height: 1.6;
        }

        .mini-status {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 9px;
            margin-top: 20px;
        }

        .mini {
            padding: 10px;
            border-radius: 10px;
            background: rgba(255,255,255,.025);
            border: 1px solid rgba(255,255,255,.05);
        }

        .mini small {
            display: block;
            color: var(--muted);
            font-size: .65rem;
        }

        .mini strong {
            display: block;
            margin-top: 3px;
            color: var(--green);
            font-size: .72rem;
        }

        .mini strong::before {
            content: "";
            display: inline-block;
            width: 6px;
            height: 6px;
            margin-right: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .log-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(93, 142, 216, .18);
        }

        .log-head h2 {
            margin: 0;
            font-size: 1.05rem;
        }

        .log-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .68rem;
        }

        .log-table th,
        .log-table td {
            padding: 9px 4px;
            text-align: left;
            border-bottom: 1px solid rgba(93, 142, 216, .10);
        }

        .log-table th {
            color: var(--muted);
            font-weight: 500;
        }

        .log-table td {
            color: #dce8fb;
        }

        .log-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            color: var(--green);
            background: rgba(33, 230, 163, .10);
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            color: #627da7;
            font-size: .68rem;
        }

        @media (max-width: 1000px) {
            .charts {
                grid-template-columns: 1fr;
            }

            .bottom-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .container {
                width: min(100% - 24px, 1450px);
                padding-top: 14px;
            }

            .header {
                align-items: flex-start;
            }

            .header-right {
                gap: 10px;
                flex-direction: column;
                align-items: flex-end;
            }

            .last-update {
                padding-left: 0;
                border-left: 0;
            }

            .sensor-grid {
                grid-template-columns: 1fr;
            }

            .mini-status {
                grid-template-columns: 1fr;
            }

            .system-content {
                align-items: flex-start;
            }

            .range {
                display: none;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <!-- HEADER -->
    <header class="header">
        <div class="brand">
            <h1>IoT <span>Monitoring Dashboard</span></h1>
            <p>Monitoring suhu, kelembapan dan jarak secara real-time</p>
        </div>

        <div class="header-right">
            <div class="online">
                <span class="online-dot"></span>
                Sistem Online
            </div>

            <div class="last-update">
                <div class="label">LAST UPDATE</div>
                <div class="time"><?= htmlspecialchars($dataTerbaru["waktu"]) ?> WIB</div>
                <div class="date"><?= date("d M Y") ?></div>
            </div>
        </div>
    </header>

    <!-- SENSOR CARDS -->
    <section class="sensor-grid">

        <div class="sensor-card temperature">
            <div class="sensor-top">
                <div class="sensor-name">
                    <div class="sensor-icon">🌡</div>
                    Suhu
                </div>
                <div class="sensor-wave">〰</div>
            </div>

            <div class="value">
                <?= htmlspecialchars($dataTerbaru["suhu"]) ?>
                <span class="unit">°C</span>
            </div>

            <div class="limit">
                Batas normal: &lt; <?= htmlspecialchars($BATAS_SUHU) ?> °C
            </div>

            <div class="status <?= $statusSuhu === "NORMAL" ? "normal" : "danger" ?>">
                ● <?= htmlspecialchars($statusSuhu) ?>
            </div>
        </div>

        <div class="sensor-card humidity">
            <div class="sensor-top">
                <div class="sensor-name">
                    <div class="sensor-icon">💧</div>
                    Kelembapan
                </div>
                <div class="sensor-wave">〰</div>
            </div>

            <div class="value">
                <?= htmlspecialchars($dataTerbaru["kelembapan"]) ?>
                <span class="unit">%</span>
            </div>

            <div class="limit">
                Batas normal: &lt; <?= htmlspecialchars($BATAS_KELEMBAPAN) ?> %
            </div>

            <div class="status <?= $statusKelembapan === "NORMAL" ? "normal" : "danger" ?>">
                ● <?= htmlspecialchars($statusKelembapan) ?>
            </div>
        </div>

        <div class="sensor-card distance">
            <div class="sensor-top">
                <div class="sensor-name">
                    <div class="sensor-icon">◉</div>
                    Jarak
                </div>
                <div class="sensor-wave">〰</div>
            </div>

            <div class="value">
                <?= htmlspecialchars($dataTerbaru["jarak"]) ?>
                <span class="unit">cm</span>
            </div>

            <div class="limit">
                Batas aman: &gt; <?= htmlspecialchars($BATAS_JARAK) ?> cm
            </div>

            <div class="status <?= $statusJarak === "NORMAL" ? "normal" : "danger" ?>">
                ● <?= htmlspecialchars($statusJarak) ?>
            </div>
        </div>

    </section>

    <!-- CHARTS -->
    <section class="chart-section">
        <div class="section-head">
            <h2 class="section-title">
                <span class="chart-icon">▥</span>
                Grafik Data Sensor
                <small style="color:var(--muted);font-size:.7rem;font-weight:400;">(20 data terakhir)</small>
            </h2>

            <div class="range">
                <span class="active">20 Data</span>
                <span>6 Jam</span>
                <span>12 Jam</span>
                <span>24 Jam</span>
            </div>
        </div>

        <div class="charts">
            <div class="chart-box">
                <h3>🌡 Suhu (°C)</h3>
                <canvas id="chartSuhu"></canvas>
            </div>

            <div class="chart-box">
                <h3>💧 Kelembapan (%)</h3>
                <canvas id="chartKelembapan"></canvas>
            </div>

            <div class="chart-box">
                <h3>◉ Jarak (cm)</h3>
                <canvas id="chartJarak"></canvas>
            </div>
        </div>
    </section>

    <!-- STATUS + LOG -->
    <section class="bottom-grid">

        <div class="system-card">
            <div class="system-content">
                <div class="system-check">
                    <?= $semuaNormal ? "✓" : "!" ?>
                </div>

                <div>
                    <h2 class="<?= $semuaNormal ? "system-ok" : "" ?>">
                        <?= $semuaNormal ? "Semua Sensor Normal" : "Perlu Perhatian" ?>
                    </h2>
                    <p>
                        <?= $semuaNormal
                            ? "Suhu, kelembapan dan jarak masih dalam batas aman."
                            : "Terdapat sensor yang nilainya berada di luar batas normal." ?>
                    </p>
                </div>
            </div>

            <div class="mini-status">
                <div class="mini">
                    <small>Suhu</small>
                    <strong><?= htmlspecialchars($statusSuhu) ?></strong>
                </div>

                <div class="mini">
                    <small>Kelembapan</small>
                    <strong><?= htmlspecialchars($statusKelembapan) ?></strong>
                </div>

                <div class="mini">
                    <small>Jarak</small>
                    <strong><?= htmlspecialchars($statusJarak) ?></strong>
                </div>
            </div>
        </div>

        <div class="log-card">
            <div class="log-head">
                <h2>Log Terbaru</h2>
            </div>

            <table class="log-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Sensor</th>
                        <th>Nilai</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $logData = array_slice(array_reverse($dataChart), 0, 5);

                    foreach ($logData as $log):
                        $suhuLog = floatval($log["suhu"]);
                        $kelembapanLog = floatval($log["kelembapan"]);
                        $jarakLog = floatval($log["jarak"]);
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($log["waktu"]) ?></td>
                            <td>Suhu</td>
                            <td><?= htmlspecialchars($suhuLog) ?> °C</td>
                            <td><span class="log-status"><?= $suhuLog >= $BATAS_SUHU ? "BAHAYA" : "NORMAL" ?></span></td>
                        </tr>
                        <tr>
                            <td><?= htmlspecialchars($log["waktu"]) ?></td>
                            <td>Kelembapan</td>
                            <td><?= htmlspecialchars($kelembapanLog) ?> %</td>
                            <td><span class="log-status"><?= $kelembapanLog >= $BATAS_KELEMBAPAN ? "BAHAYA" : "NORMAL" ?></span></td>
                        </tr>
                        <tr>
                            <td><?= htmlspecialchars($log["waktu"]) ?></td>
                            <td>Jarak</td>
                            <td><?= htmlspecialchars($jarakLog) ?> cm</td>
                            <td><span class="log-status"><?= $jarakLog <= $BATAS_JARAK ? "TERLALU DEKAT" : "NORMAL" ?></span></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($logData)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;color:var(--muted);">Belum ada data sensor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </section>

    <div class="footer">
        Dashboard diperbarui otomatis setiap 5 detik • Last update: <?= htmlspecialchars($dataTerbaru["waktu"]) ?> WIB
    </div>

</div>

<script>
const sensorData = <?= $chartJson ?: "[]" ?>;

function drawChart(canvasId, key, lineColor, minValue, maxValue) {
    const canvas = document.getElementById(canvasId);
    const ctx = canvas.getContext("2d");

    function render() {
        const ratio = window.devicePixelRatio || 1;
        const width = canvas.clientWidth;
        const height = canvas.clientHeight;

        canvas.width = width * ratio;
        canvas.height = height * ratio;
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

        ctx.clearRect(0, 0, width, height);

        const pad = { top: 12, right: 10, bottom: 24, left: 36 };
        const chartW = width - pad.left - pad.right;
        const chartH = height - pad.top - pad.bottom;

        // Grid
        ctx.strokeStyle = "rgba(80,120,180,.18)";
        ctx.lineWidth = 1;
        ctx.setLineDash([4, 5]);

        for (let i = 0; i <= 4; i++) {
            const y = pad.top + chartH * (i / 4);
            ctx.beginPath();
            ctx.moveTo(pad.left, y);
            ctx.lineTo(width - pad.right, y);
            ctx.stroke();
        }

        ctx.setLineDash([]);

        // Labels
        ctx.fillStyle = "#718bb3";
        ctx.font = "10px Poppins, sans-serif";
        ctx.textAlign = "right";

        for (let i = 0; i <= 4; i++) {
            const value = maxValue - ((maxValue - minValue) * i / 4);
            const y = pad.top + chartH * (i / 4) + 3;
            ctx.fillText(Number(value.toFixed(0)), pad.left - 6, y);
        }

        if (!sensorData.length) return;

        const values = sensorData.map(row => Number(row[key]) || 0);
        const range = maxValue - minValue || 1;

        const points = values.map((value, index) => {
            const x = values.length === 1
                ? pad.left + chartW / 2
                : pad.left + (index / (values.length - 1)) * chartW;

            const clamped = Math.max(minValue, Math.min(maxValue, value));
            const y = pad.top + chartH - ((clamped - minValue) / range) * chartH;

            return { x, y };
        });

        // Area
        const gradient = ctx.createLinearGradient(0, pad.top, 0, height);
        gradient.addColorStop(0, lineColor.replace("1)", ".20)"));
        gradient.addColorStop(1, "rgba(0,0,0,0)");

        ctx.beginPath();
        ctx.moveTo(points[0].x, pad.top + chartH);
        points.forEach(point => ctx.lineTo(point.x, point.y));
        ctx.lineTo(points[points.length - 1].x, pad.top + chartH);
        ctx.closePath();
        ctx.fillStyle = gradient;
        ctx.fill();

        // Line
        ctx.beginPath();
        points.forEach((point, index) => {
            if (index === 0) ctx.moveTo(point.x, point.y);
            else ctx.lineTo(point.x, point.y);
        });

        ctx.strokeStyle = lineColor;
        ctx.lineWidth = 2.5;
        ctx.lineJoin = "round";
        ctx.lineCap = "round";
        ctx.stroke();

        // Last point
        const last = points[points.length - 1];
        ctx.beginPath();
        ctx.arc(last.x, last.y, 4, 0, Math.PI * 2);
        ctx.fillStyle = lineColor;
        ctx.fill();
        ctx.strokeStyle = "#07183a";
        ctx.lineWidth = 2;
        ctx.stroke();

        // Time labels
        ctx.fillStyle = "#718bb3";
        ctx.font = "9px Poppins, sans-serif";
        ctx.textAlign = "center";

        const labelIndexes = values.length > 5
            ? [0, Math.floor(values.length / 2), values.length - 1]
            : values.map((_, i) => i);

        labelIndexes.forEach(index => {
            const row = sensorData[index];
            const label = String(row.waktu ?? "").substring(0, 5);
            ctx.fillText(label, points[index].x, height - 7);
        });
    }

    render();
    window.addEventListener("resize", render);
}

drawChart("chartSuhu", "suhu", "rgba(34,167,255,1)", 0, Math.max(40, <?= floatval($BATAS_SUHU) ?> + 10));
drawChart("chartKelembapan", "kelembapan", "rgba(33,230,163,1)", 0, 100);
drawChart("chartJarak", "jarak", "rgba(139,108,255,1)", 0, Math.max(100, <?= floatval($BATAS_JARAK) ?> + 20));
</script>

</body>
</html>
