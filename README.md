# IoT Monitoring Dashboard

Dashboard monitoring IoT menggunakan ESP8266, sensor suhu, kelembapan, dan jarak. Data sensor dikirim ke server PHP, disimpan ke MySQL, kemudian ditampilkan pada dashboard.

## Fitur

- Monitoring suhu
- Monitoring kelembapan
- Monitoring jarak
- Status sensor NORMAL / BAHAYA / TERLALU DEKAT
- Grafik data sensor
- Last Update
- Dashboard diperbarui otomatis setiap 5 detik
- Penyimpanan data menggunakan MySQL
- Notifikasi Fonnte

## Struktur Project

```text
iot-dashboard/
├── config.php              # File lokal, TIDAK di-upload ke GitHub
├── config.example.php      # Template konfigurasi yang di-upload
├── esp8266.cpp
├── index.php
├── simpan_data.php
├── .gitignore
└── README.md
```

> `config.php` tetap digunakan seperti biasa di komputer lokal dan berisi token Fonnte. File ini sengaja tidak di-upload ke GitHub.

## 1. Persiapan

Software yang digunakan:

- Laragon
- PHP
- MySQL
- Arduino IDE
- ESP8266
- Git
- GitHub

Folder project:

```text
C:\laragon\www\iot-dashboard
```

## 2. Database

Buat database MySQL dengan nama:

```text
iot
```

Buat tabel `sensor` dengan field:

```text
id
suhu
kelembapan
jarak
waktu
```

Konfigurasi database disimpan di `config.php`.

## 3. Konfigurasi config.php

Repository GitHub menyediakan file:

```text
config.example.php
```

File tersebut hanya merupakan template dan tidak berisi token asli.

Setelah melakukan clone/download project, salin file tersebut menjadi `config.php`.

### Windows PowerShell

Jalankan dari folder project:

```powershell
Copy-Item config.example.php config.php
```

### Windows Command Prompt (CMD)

```cmd
copy config.example.php config.php
```

Setelah itu buka:

```text
config.php
```

Kemudian isi token Fonnte dan konfigurasi database sesuai kebutuhan.

Contoh bagian token:

```php
$FONNTE_TOKEN = "MASUKKAN_TOKEN_FONNTE";
```

Ganti `MASUKKAN_TOKEN_FONNTE` dengan token Fonnte milik sendiri.

> Jangan meng-upload `config.php` setelah mengisi token. `config.php` sudah dimasukkan ke `.gitignore`.

## 4. Isi config.example.php

Contoh template:

```php
<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "iot";

$conn = mysqli_connect($host, $user, $password, $database);

$FONNTE_TOKEN = "MASUKKAN_TOKEN_FONNTE";

$BATAS_SUHU = 35;
$BATAS_KELEMBAPAN = 80;
$BATAS_JARAK = 10;
```

Nilai batas sensor dapat disesuaikan dengan kebutuhan project.

## 5. Menjalankan Project di Laragon

Simpan project pada:

```text
C:\laragon\www\iot-dashboard
```

Aktifkan:

- Apache
- MySQL

Kemudian buka:

```text
http://localhost/iot-dashboard/
```

## 6. Konfigurasi ESP8266

Buka:

```text
esp8266.cpp
```

Sesuaikan:

- Nama WiFi
- Password WiFi
- IP komputer/server

Contoh alamat endpoint:

```cpp
http://192.168.x.x/iot-dashboard/simpan_data.php
```

ESP8266 dan komputer yang menjalankan Laragon harus berada pada jaringan yang sama jika menggunakan IP lokal.

## 7. Upload Project ke GitHub

Pastikan `config.php` sudah masuk `.gitignore`.

Buka terminal VS Code:

```powershell
cd C:\laragon\www\iot-dashboard
```

Jika belum menjadi repository Git:

```powershell
git init
```

Cek status:

```powershell
git status
```

Pastikan `config.php` tidak muncul sebagai file yang akan di-upload.

Kemudian:

```powershell
git add .
```

Cek kembali:

```powershell
git status
```

Commit:

```powershell
git commit -m "Initial IoT monitoring dashboard"
```

Hubungkan dengan repository GitHub:

```powershell
git remote add origin https://github.com/USERNAME/iot-dashboard.git
```

Ganti `USERNAME` dengan username GitHub.

Gunakan branch `main`:

```powershell
git branch -M main
```

Upload:

```powershell
git push -u origin main
```

## 8. Mengecek File yang Masuk Git

Gunakan:

```powershell
git ls-files
```

File yang boleh muncul:

```text
.gitignore
README.md
config.example.php
esp8266.cpp
index.php
simpan_data.php
```

File berikut tidak boleh muncul:

```text
config.php
```

## 9. Cara Menggunakan Project dari GitHub

Setelah melakukan clone:

```powershell
git clone https://github.com/USERNAME/iot-dashboard.git
```

Masuk ke folder:

```powershell
cd iot-dashboard
```

Buat `config.php` dari template:

```powershell
Copy-Item config.example.php config.php
```

Kemudian buka `config.php` dan masukkan:

- konfigurasi database
- token Fonnte
- batas sensor

Setelah itu project dapat dijalankan melalui Laragon.

## 10. Jika config.php Sudah Terlanjur Masuk GitHub

Jika `config.php` yang berisi token asli pernah ter-upload ke GitHub, token tersebut harus dianggap sudah terekspos. Segera ganti/nonaktifkan token Fonnte dan gunakan token baru.

## Alur Sistem

```text
ESP8266
   |
   | Suhu, kelembapan, jarak
   v
simpan_data.php
   |
   v
MySQL
   |
   v
index.php
   |
   +-- Nilai sensor terbaru
   +-- Status sensor
   +-- Grafik
   +-- Last Update
```

## Keamanan

Jangan upload ke repository:

- Token Fonnte
- Password database
- Password WiFi
- API key
- Credential lainnya

Gunakan `config.php` untuk konfigurasi lokal dan `config.example.php` sebagai template yang aman untuk repository.
