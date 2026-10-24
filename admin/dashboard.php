<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'admin') { alihkan('../index.php'); }
$user = ambilPengguna();

// Statistik
$jmlMahasiswa = $pdo->query("SELECT COUNT(*) FROM pengguna WHERE peran='mahasiswa'")->fetchColumn();
$jmlDosen = $pdo->query("SELECT COUNT(*) FROM pengguna WHERE peran='dosen'")->fetchColumn();
$jmlBooking = $pdo->query("SELECT COUNT(*) FROM konsultasi")->fetchColumn();
$jmlSelesai = $pdo->query("SELECT COUNT(*) FROM konsultasi WHERE status='disetujui' AND tanggal < CURDATE()")->fetchColumn();

// Aktivitas Terbaru (Konsultasi terbaru yang diinput)
$stmtTerbaru = $pdo->query("SELECT k.*, m.nama as m_nama, d.nama as d_nama FROM konsultasi k JOIN pengguna m ON k.mahasiswa_id=m.id JOIN pengguna d ON k.dosen_id=d.id ORDER BY k.dibuat_pada DESC LIMIT 5");
$aktivitas = $stmtTerbaru->fetchAll();

// Data Grafik Sederhana (Booking per status)
$statStatus = $pdo->query("SELECT status, COUNT(*) as jml FROM konsultasi GROUP BY status")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - JadwalinKampus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#F0F4F8;min-height:100vh;display:flex;color:#1E293B}
        .sidebar{width:260px;background:#1E293B;height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;z-index:100;color:#fff}
        .sidebar-brand{padding:24px 20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid rgba(255,255,255,0.1)}
        .sidebar-brand .icon{width:40px;height:40px;background:#3B82F6;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px}
        .sidebar-brand h2{font-size:20px;font-weight:700}.sidebar-brand small{font-size:13px;color:#94A3B8;display:block}
        .sidebar-nav{padding:16px 12px;flex:1}
        .sidebar-nav a{display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;color:#94A3B8;text-decoration:none;font-size:16px;font-weight:500;margin-bottom:4px;transition:all .2s}
        .sidebar-nav a:hover{background:rgba(255,255,255,0.05);color:#fff}
        .sidebar-nav a.active{background:#3B82F6;color:#fff}
        .sidebar-nav a i{font-size:20px}
        .sidebar-user{padding:16px 20px;border-top:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;gap:12px}
        .sidebar-user .avatar{width:40px;height:40px;background:rgba(255,255,255,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:16px}
        .main{margin-left:260px;flex:1;padding:32px;min-height:100vh}
        .page-title{font-size:28px;font-weight:700;margin-bottom:4px}
        .page-subtitle{color:#64748B;font-size:16px;margin-bottom:28px}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px}
        .stat-card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.06);display:flex;align-items:center;gap:16px}
        .stat-icon{width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:26px}
        .stat-icon.blue{background:#DBEAFE;color:#1E40AF}.stat-icon.purple{background:#F3E8FF;color:#7E22CE}.stat-icon.orange{background:#FFEDD5;color:#C2410C}.stat-icon.green{background:#D1FAE5;color:#059669}
        .stat-value{font-size:32px;font-weight:800;line-height:1}.stat-label{font-size:14px;color:#64748B;margin-top:2px}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:24px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden}
        .card-header{padding:18px 20px;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;justify-content:space-between}
        .card-header h3{font-size:18px;font-weight:600}
        .card-body{padding:20px}
        .item{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid #F1F5F9}
        .item:last-child{border-bottom:none}
        .badge{padding:6px 12px;border-radius:20px;font-size:13px;font-weight:600}
        .badge-success{background:#D1FAE5;color:#059669}.badge-warning{background:#FEF3C7;color:#D97706}.badge-danger{background:#FEE2E2;color:#DC2626}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Dashboard Admin</small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="pengguna.php"><i class='bx bxs-user-account'></i> Kelola Pengguna</a>
            <a href="monitoring.php"><i class='bx bx-calendar-event'></i> Monitoring Jadwal</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Admin</a>
        </nav>
        <div class="sidebar-user">
            <div class="avatar">A</div>
            <div class="info"><div style="font-size:13px;font-weight:600"><?= $user['nama'] ?></div><div style="font-size:11px;color:#94A3B8">Administrator</div></div>
            <a href="../logout.php" style="color:#94A3B8;font-size:20px"><i class='bx bx-log-out'></i></a>
        </div>
    </aside>

    <div class="main">
        <h1 class="page-title">Dashboard Admin</h1>
        <p class="page-subtitle">Selamat datang di panel kontrol sistem JadwalinKampus.</p>

        <div class="stats">
            <div class="stat-card"><div class="stat-icon blue"><i class='bx bxs-user-badge'></i></div><div><div class="stat-value"><?= $jmlMahasiswa ?></div><div class="stat-label">Mahasiswa</div></div></div>
            <div class="stat-card"><div class="stat-icon purple"><i class='bx bxs-graduation'></i></div><div><div class="stat-value"><?= $jmlDosen ?></div><div class="stat-label">Dosen</div></div></div>
            <div class="stat-card"><div class="stat-icon orange"><i class='bx bx-calendar-star'></i></div><div><div class="stat-value"><?= $jmlBooking ?></div><div class="stat-label">Total Booking</div></div></div>
            <div class="stat-card"><div class="stat-icon green"><i class='bx bx-check-double'></i></div><div><div class="stat-value"><?= $jmlSelesai ?></div><div class="stat-label">Selesai</div></div></div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-header"><h3>Aktivitas Booking Terbaru</h3></div>
                <div class="card-body">
                    <?php foreach($aktivitas as $a): ?>
                        <div class="item">
                            <div style="flex:1">
                                <div style="font-size:13px;font-weight:600"><?= $a['m_nama'] ?> &rarr; <?= $a['d_nama'] ?></div>
                                <div style="font-size:11px;color:#64748B"><?= formatTanggalSingkat($a['tanggal']) ?> · <?= formatWaktu($a['waktu_mulai']) ?></div>
                            </div>
                            <span class="badge <?= kelasStatus($a['status']) ?>"><?= ucfirst($a['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Statistik Status Konsultasi</h3></div>
                <div class="card-body">
                    <?php foreach($statStatus as $s): 
                        $persen = round(($s['jml'] / ($jmlBooking ?: 1)) * 100);
                    ?>
                        <div style="margin-bottom:16px">
                            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px;font-weight:600">
                                <span><?= ucfirst($s['status']) ?></span>
                                <span><?= $s['jml'] ?> (<?= $persen ?>%)</span>
                            </div>
                            <div style="height:8px;background:#F1F5F9;border-radius:10px;overflow:hidden">
                                <div style="height:100%;width:<?=$persen?>%;background:<?= $s['status']=='disetujui'?'#10B981':($s['status']=='menunggu'?'#F59E0B':'#EF4444') ?>"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
