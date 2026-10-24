<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'dosen') { alihkan('../index.php'); }
$user = ambilPengguna();

// Aksi Setuju/Tolak
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $aksi = $_GET['aksi'];
    $status = ($aksi === 'setuju') ? 'disetujui' : 'ditolak';
    
    $stmt = $pdo->prepare("UPDATE konsultasi SET status = ? WHERE id = ? AND dosen_id = ?");
    $stmt->execute([$status, $id, $user['id']]);
    alihkan('permintaan.php?pesan=berhasil');
}

// Daftar Permintaan
$stmt = $pdo->prepare("SELECT k.*, p.nama as nama_mahasiswa, p.nim, p.jurusan FROM konsultasi k JOIN pengguna p ON k.mahasiswa_id = p.id WHERE k.dosen_id = ? ORDER BY k.dibuat_pada DESC");
$stmt->execute([$user['id']]);
$permintaan = $stmt->fetchAll();

$blnSingkat = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Konsultasi - JadwalinKampus</title>
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
        .sidebar-user .info{flex:1}.sidebar-user .info .name{font-size:15px;font-weight:600}
        .main{margin-left:260px;flex:1;padding:32px;min-height:100vh}
        .page-title{font-size:28px;font-weight:700;margin-bottom:4px}.page-subtitle{color:#64748B;font-size:16px;margin-bottom:28px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden}
        .card-body{padding:0}
        table{width:100%;border-collapse:collapse}
        th{background:#F8FAFC;padding:16px 20px;text-align:left;font-size:15px;font-weight:600;color:#64748B;border-bottom:1px solid #E2E8F0}
        td{padding:18px 20px;font-size:16px;border-bottom:1px solid #F1F5F9}
        .badge{padding:6px 12px;border-radius:20px;font-size:14px;font-weight:600}
        .badge-warning{background:#FEF3C7;color:#D97706}.badge-success{background:#D1FAE5;color:#059669}.badge-danger{background:#FEE2E2;color:#DC2626}
        .btn-action{width:36px;height:36px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all .2s;border:none;cursor:pointer}
        .btn-success{background:#D1FAE5;color:#059669}.btn-success:hover{background:#059669;color:#fff}
        .btn-danger{background:#FEE2E2;color:#DC2626}.btn-danger:hover{background:#DC2626;color:#fff}
        .alert{padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px}
        .alert-success{background:#D1FAE5;color:#065F46;border:1px solid #A7F3D0}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Permintaan Konsultasi Mahasiswa</small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="permintaan.php" class="active"><i class='bx bx-message-square-detail'></i> Permintaan</a>
            <a href="jadwal.php"><i class='bx bx-calendar'></i> Jadwal Konsultasi</a>
            <a href="mahasiswa.php"><i class='bx bxs-user-detail'></i> Mahasiswa Konsultasi</a>
            <a href="ketersediaan.php"><i class='bx bx-time-five'></i> Ketersediaan</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Saya</a>
        </nav>
        <div class="sidebar-user">
            <div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div>
            <div class="info"><div class="name"><?= htmlspecialchars($user['nama']) ?></div></div>
            <a href="../logout.php" style="color:#94A3B8;font-size:20px"><i class='bx bx-log-out'></i></a>
        </div>
    </aside>

    <div class="main">
        <h1 class="page-title">Permintaan Konsultasi</h1>
        <p class="page-subtitle">Daftar semua permintaan konsultasi dari mahasiswa.</p>

        <?php if(isset($_GET['pesan'])): ?>
            <div class="alert alert-success"><i class='bx bx-check-circle'></i> Perubahan berhasil disimpan!</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Tanggal & Waktu</th>
                            <th>Topik</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($permintaan)): ?>
                            <tr><td colspan="5" style="text-align:center;padding:40px;color:#94A3B8">Tidak ada data permintaan</td></tr>
                        <?php else: foreach($permintaan as $p): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600"><?= htmlspecialchars($p['nama_mahasiswa']) ?></div>
                                    <div style="font-size:12px;color:#64748B"><?= $p['nim'] ?> · <?= $p['jurusan'] ?></div>
                                </td>
                                <td>
                                    <div><?= formatTanggalSingkat($p['tanggal']) ?></div>
                                    <div style="font-size:12px;color:#64748B"><?= formatWaktu($p['waktu_mulai']) ?> - <?= formatWaktu($p['waktu_selesai']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($p['topik']) ?></td>
                                <td><span class="badge <?= kelasStatus($p['status']) ?>"><?= ucfirst($p['status']) ?></span></td>
                                <td>
                                    <?php if($p['status'] === 'menunggu'): ?>
                                        <div style="display:flex;gap:6px">
                                            <a href="?aksi=setuju&id=<?=$p['id']?>" class="btn-action btn-success" title="Setujui"><i class='bx bx-check'></i></a>
                                            <a href="?aksi=tolak&id=<?=$p['id']?>" class="btn-action btn-danger" title="Tolak"><i class='bx bx-x'></i></a>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size:12px;color:#94A3B8">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
