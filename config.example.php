<?php
// 1. KONEKSI DATABASE
$host = "localhost";
$user = "root";
$pass = "";
$db   = "iot";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// 2. KONFIGURASI AMBANG BATAS & TOKEN
$BATAS_SUHU       = 35.0;
$BATAS_KELEMBAPAN = 80.0;
$BATAS_JARAK      = 10.0;

$FONNTE_TOKEN     = "MASUKAN_TOKEN_FONNTE";
$WHATSAPP_TARGET  = "NOMOR_TARGET";

// 3. FUNGSI KIRIM WHATSAPP
function kirimWhatsApp($token, $target, $pesan) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'target' => $target,
            'message' => $pesan,
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: $token"
        ],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}