<?php // Koneksi database MySQL (PDO)
// Setting koneksi — sesuaikan user/pass kalau MySQL kamu beda
$DB_HOST = "localhost";
$DB_NAME = "junior_web";
$DB_USER = "root";
$DB_PASS = "";

try {
  // Buka koneksi PDO + hasil query jadi array assoc
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  // MySQL mati / nama database salah — berhenti dengan pesan jelas
  http_response_code(500);
  exit("Koneksi database gagal: periksa MySQL sudah jalan + database junior_web ada.");
}
