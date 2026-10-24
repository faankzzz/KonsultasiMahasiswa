<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'dosen') { alihkan('../index.php'); }
$user = ambilPengguna();

// Tambah Ketersediaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $hari = $_POST['hari'];
    $mulai = $_POST['mulai'];
    $selesai = $_POST['selesai'];
    
    $stmt = $pdo->prepare("INSERT INTO ketersediaan (dosen_id, hari, waktu_mulai, waktu_selesai) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user['id'], $hari, $mulai, $selesai]);
    alihkan('ketersediaan.php?pesan=tambah');
}

// Hapus Ketersediaan
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM ketersediaan WHERE id = ? AND dosen_id = ?");
    $stmt->execute([$id, $user['id']]);
    alihkan('ketersediaan.php?pesan=hapus');
}

// Ambil Ketersediaan
$stmt = $pdo->prepare("SELECT * FROM ketersediaan WHERE dosen_id = ? ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");
$stmt->execute([$user['id']]);
$ketersediaan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ketersediaan - JadwalinKampus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#F0F4F8;min-height:100vh;display:flex;color:#1E293B}
        .sidebar{width:260px;background:#fff;border-right:1px solid #E2E8F0;height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;z-index:100}
        .sidebar-brand{padding:24px 20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid #E2E8F0}
        .sidebar-brand .icon{width:40px;height:40px;background:linear-gradient(135deg,#1E40AF,#3B82F6);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px}
        .sidebar-brand h2{font-size:20px;font-weight:700}.sidebar-brand small{font-size:13px;color:#64748B;display:block}
        .sidebar-nav{padding:16px 12px;flex:1}
        .sidebar-nav a{display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;color:#475569;text-decoration:none;font-size:16px;font-weight:500;margin-bottom:4px;transition:all .2s}
        .sidebar-nav a:hover{background:#EFF6FF;color:#1E40AF}.sidebar-nav a.active{background:#1E40AF;color:#fff}
        .sidebar-user{padding:16px 20px;border-top:1px solid #E2E8F0;display:flex;align-items:center;gap:12px}
        .sidebar-user .avatar{width:40px;height:40px;background:#DBEAFE;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#1E40AF;font-weight:700;font-size:16px}
        .main{margin-left:260px;flex:1;padding:32px;min-height:100vh}
        .page-title{font-size:28px;font-weight:700;margin-bottom:4px}.page-subtitle{color:#64748B;font-size:16px;margin-bottom:28px}
        .grid{display:grid;grid-template-columns:350px 1fr;gap:24px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);padding:24px}
        .card-title{font-size:16px;font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:8px}
        .form-group{margin-bottom:16px}
        .form-group label{display:block;font-size:15px;font-weight:600;color:#475569;margin-bottom:6px}
        .form-control{width:100%;padding:12px 16px;border:1.5px solid #E2E8F0;border-radius:10px;font-family:inherit;font-size:16px;outline:none;background:#F8FAFC;transition:all .2s}
        .form-control:focus{border-color:#3B82F6;background:#fff;box-shadow:0 0 0 3px rgba(59,130,246,0.1)}
        .btn{padding:12px 24px;border-radius:10px;font-family:inherit;font-size:15px;font-weight:600;cursor:pointer;border:none;transition:all .2s;display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none}
        .btn-primary{background:#1E40AF;color:#fff;width:100%}.btn-primary:hover{background:#1D4ED8}
        .btn-danger-sm{background:#FEE2E2;color:#DC2626;padding:8px 12px;font-size:14px;border-radius:8px}.btn-danger-sm:hover{background:#DC2626;color:#fff}
        .avail-list{margin-top:0}
        .avail-item{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;margin-bottom:10px}
        .avail-day{font-weight:600;font-size:16px}
        .avail-time{font-size:14px;color:#64748B}
        .alert{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px;display:flex;align-items:center;gap:8px}
        .alert-success{background:#D1FAE5;color:#065F46;border:1px solid #A7F3D0}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Informasi Ketersediaan Konsultasi</small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="permintaan.php"><i class='bx bx-message-square-detail'></i> Permintaan</a>
            <a href="jadwal.php"><i class='bx bx-calendar'></i> Jadwal Konsultasi</a>
            <a href="mahasiswa.php"><i class='bx bxs-user-detail'></i> Mahasiswa Konsultasi</a>
            <a href="ketersediaan.php" class="active"><i class='bx bx-time-five'></i> Ketersediaan</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Saya</a>
        </nav>
        <div class="sidebar-user"><div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div><div class="info"><div class="name"><?= htmlspecialchars($user['nama']) ?></div></div><a href="../logout.php" style="color:#94A3B8;font-size:20px"><i class='bx bx-log-out'></i></a></div>
    </aside>

    <div class="main">
        <h1 class="page-title">Atur Ketersediaan</h1>
        <p class="page-subtitle">Tentukan hari dan jam kerja Anda agar mahasiswa dapat melakukan booking.</p>

        <?php if(isset($_GET['pesan'])): ?>
            <div class="alert alert-success"><i class='bx bx-check-circle'></i> Berhasil memperbarui ketersediaan!</div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                <h3 class="card-title"><i class='bx bx-plus-circle'></i> Tambah Jadwal</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Hari</label>
                        <select name="hari" class="form-control" required>
                            <option value="Senin">Senin</option><option value="Selasa">Selasa</option><option value="Rabu">Rabu</option><option value="Kamis">Kamis</option><option value="Jumat">Jumat</option><option value="Sabtu">Sabtu</option><option value="Minggu">Minggu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Waktu Mulai</label>
                        <input type="time" name="mulai" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Waktu Selesai</label>
                        <input type="time" name="selesai" class="form-control" required>
                    </div>
                    <button type="submit" name="tambah" class="btn btn-primary"><i class='bx bx-save'></i> Simpan Jadwal</button>
                </form>
            </div>

            <div class="card">
                <h3 class="card-title"><i class='bx bx-list-ul'></i> Jadwal Ketersediaan Saat Ini</h3>
                <div class="avail-list">
                    <?php if(empty($ketersediaan)): ?>
                        <div style="text-align:center;padding:20px;color:#94A3B8">Belum ada jadwal yang diatur.</div>
                    <?php else: foreach($ketersediaan as $k): ?>
                        <div class="avail-item">
                            <div>
                                <div class="avail-day"><?= $k['hari'] ?></div>
                                <div class="avail-time"><?= formatWaktu($k['waktu_mulai']) ?> - <?= formatWaktu($k['waktu_selesai']) ?></div>
                            </div>
                            <a href="?hapus=<?=$k['id']?>" class="btn-danger-sm" onclick="return confirm('Hapus jadwal ini?')"><i class='bx bx-trash'></i></a>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
