<?php
include "config.php";

date_default_timezone_set("Asia/Jakarta");

// Validasi input
if (!isset($_POST["suhu"], $_POST["kelembapan"], $_POST["jarak"])) {
    http_response_code(400);
    exit("Data tidak lengkap");
}

$suhu = floatval($_POST["suhu"]);
$kelembapan = floatval($_POST["kelembapan"]);
$jarak = floatval($_POST["jarak"]);
$waktu = date("Y-m-d H:i:s");

// Simpan ke DB
$stmt = mysqli_prepare($conn, "INSERT INTO sensor (suhu, kelembapan, jarak, waktu) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ddds", $suhu, $kelembapan, $jarak, $waktu);
if (!mysqli_stmt_execute($stmt)) exit("Gagal menyimpan data");

// Cek batas sensor
$peringatan = [];
if ($suhu >= $BATAS_SUHU) $peringatan[] = "🌡 Suhu tinggi: {$suhu} °C";
if ($kelembapan >= $BATAS_KELEMBAPAN) $peringatan[] = "💧 Kelembapan tinggi: {$kelembapan} %";
if ($jarak <= $BATAS_JARAK) $peringatan[] = "📏 Jarak dekat: {$jarak} cm";

// Anti-spam WA (cooldown 10 menit)
$file_alert = __DIR__ . "/last_alert.txt";
$cooldown = 600;

if (!empty($peringatan)) {
    $last_sent = file_exists($file_alert) ? (int)file_get_contents($file_alert) : 0;

    if ((time() - $last_sent) >= $cooldown) {
        $pesan = "⚠️ PERINGATAN IoT\n\n" . implode("\n", $peringatan) . "\n\n"
               . "📊 DATA SENSOR\nSuhu: {$suhu} °C\nKelembapan: {$kelembapan} %\nJarak: {$jarak} cm\nWaktu: {$waktu} WIB";

        kirimWhatsApp($FONNTE_TOKEN, $WHATSAPP_TARGET, $pesan);
        file_put_contents($file_alert, time());
    }
} elseif (file_exists($file_alert)) {
    unlink($file_alert); // Reset penanda saat kondisi kembali normal
}

echo "Data berhasil disimpan";