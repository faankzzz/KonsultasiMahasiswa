<?php
// ================================================
// KampusKonsul - Konfigurasi Database
// ================================================
session_start();
date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';
$dbname = 'ujikom_konsultasi';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Cek apakah sudah login
function sudahLogin() {
    return isset($_SESSION['pengguna_id']);
}

// Ambil data pengguna yang sedang login
function ambilPengguna() {
    global $pdo;
    if (!sudahLogin()) return null;
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id = ?");
    $stmt->execute([$_SESSION['pengguna_id']]);
    return $stmt->fetch();
}

// Redirect halaman
function alihkan($url) {
    header("Location: $url");
    exit;
}

// Format tanggal ke bahasa Indonesia
function formatTanggal($tanggal) {
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $d = new DateTime($tanggal);
    return $hari[$d->format('w')] . ', ' . $d->format('j') . ' ' . $bulan[(int)$d->format('n')] . ' ' . $d->format('Y');
}

function formatTanggalSingkat($tanggal) {
    $bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $d = new DateTime($tanggal);
    return $d->format('j') . ' ' . $bulan[(int)$d->format('n')] . ' ' . $d->format('Y');
}

function formatWaktu($waktu) {
    return date('H:i', strtotime($waktu));
}

// Kelas CSS untuk badge status
function kelasStatus($status) {
    return ['menunggu'=>'badge-warning','disetujui'=>'badge-success','ditolak'=>'badge-danger'][$status] ?? 'badge-warning';
}

// Ikon untuk badge status
function ikonStatus($status) {
    return ['menunggu'=>'bx-time-five','disetujui'=>'bx-check-circle','ditolak'=>'bx-x-circle'][$status] ?? 'bx-time-five';
}
?>
