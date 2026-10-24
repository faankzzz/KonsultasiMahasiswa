<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'dosen') { alihkan('../index.php'); }
$user = ambilPengguna();

// Statistik
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM konsultasi WHERE dosen_id = ?");
$stmtTotal->execute([$user['id']]);
$totalBooking = $stmtTotal->fetchColumn();

$stmtSetuju = $pdo->prepare("SELECT COUNT(*) FROM konsultasi WHERE dosen_id = ? AND status = 'disetujui'");
$stmtSetuju->execute([$user['id']]);
$totalSetuju = $stmtSetuju->fetchColumn();

$stmtTunggu = $pdo->prepare("SELECT COUNT(*) FROM konsultasi WHERE dosen_id = ? AND status = 'menunggu'");
$stmtTunggu->execute([$user['id']]);
$totalTunggu = $stmtTunggu->fetchColumn();

// Permintaan Mendatang (Status Menunggu)
$stmtPermintaan = $pdo->prepare("SELECT k.*, p.nama as nama_mahasiswa, p.nim FROM konsultasi k JOIN pengguna p ON k.mahasiswa_id = p.id WHERE k.dosen_id = ? AND k.status = 'menunggu' ORDER BY k.tanggal, k.waktu_mulai LIMIT 5");
$stmtPermintaan->execute([$user['id']]);
$permintaanList = $stmtPermintaan->fetchAll();

// Jadwal Hari Ini
$hariIni = date('Y-m-d');
$stmtHariIni = $pdo->prepare("SELECT k.*, p.nama as nama_mahasiswa FROM konsultasi k JOIN pengguna p ON k.mahasiswa_id = p.id WHERE k.dosen_id = ? AND k.tanggal = ? AND k.status = 'disetujui' ORDER BY k.waktu_mulai");
$stmtHariIni->execute([$user['id'], $hariIni]);
$jadwalHariIni = $stmtHariIni->fetchAll();

$blnSingkat = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - Jadwalin Kampus</title>
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
        .sidebar-nav a:hover{background:#EFF6FF;color:#1E40AF}
        .sidebar-nav a.active{background:#1E40AF;color:#fff}
        .sidebar-nav a i{font-size:20px}
        .sidebar-user{padding:16px 20px;border-top:1px solid #E2E8F0;display:flex;align-items:center;gap:12px}
        .sidebar-user .avatar{width:40px;height:40px;background:#DBEAFE;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#1E40AF;font-weight:700;font-size:16px}
        .sidebar-user .info{flex:1}.sidebar-user .info .name{font-size:15px;font-weight:600}.sidebar-user .info .role{font-size:13px;color:#64748B}
        .sidebar-user .logout{color:#94A3B8;font-size:24px;text-decoration:none}.sidebar-user .logout:hover{color:#EF4444}
        .main{margin-left:260px;flex:1;padding:32px;min-height:100vh}
        .page-title{font-size:28px;font-weight:700;margin-bottom:4px}
        .page-subtitle{color:#64748B;font-size:16px;margin-bottom:28px}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px}
        .stat-card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.06);display:flex;align-items:center;gap:16px}
        .stat-icon{width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:26px}
        .stat-icon.blue{background:#DBEAFE;color:#1E40AF}.stat-icon.green{background:#D1FAE5;color:#059669}.stat-icon.yellow{background:#FEF3C7;color:#D97706}
        .stat-value{font-size:32px;font-weight:800;line-height:1}.stat-label{font-size:14px;color:#64748B;margin-top:2px}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden}
        .card-header{padding:18px 20px;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;justify-content:space-between}
        .card-header h3{font-size:18px;font-weight:600;display:flex;align-items:center;gap:8px}
        .card-body{padding:20px}
        .item{display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:1px solid #F1F5F9}
        .item:last-child{border-bottom:none}
        .item-info{flex:1}.item-info h4{font-size:15px;font-weight:600;margin-bottom:2px}.item-info p{font-size:14px;color:#64748B}
        .btn{padding:10px 20px;border-radius:8px;font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all .2s;display:inline-flex;align-items:center;gap:4px}
        .btn-primary{background:#1E40AF;color:#fff}.btn-primary:hover{background:#1D4ED8}
        .btn-success{background:#10B981;color:#fff}.btn-danger{background:#EF4444;color:#fff}
        .btn-outline{background:#fff;color:#1E40AF;border:1px solid #DBEAFE}.btn-outline:hover{background:#EFF6FF}
        .badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
        .badge-warning{background:#FEF3C7;color:#D97706}
        .empty-state{text-align:center;padding:32px 16px;color:#94A3B8}.empty-state i{font-size:40px;margin-bottom:8px;display:block}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="icon"><i class='bx bxs-graduation'></i></div>
            <div><h2>JadwalinKampus</h2><small>Dashboard Dosen</small></div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="permintaan.php"><i class='bx bx-message-square-detail'></i> Permintaan</a>
            <a href="jadwal.php"><i class='bx bx-calendar'></i> Jadwal Konsultasi</a>
            <a href="mahasiswa.php"><i class='bx bxs-user-detail'></i> Mahasiswa Konsultasi</a>
            <a href="ketersediaan.php"><i class='bx bx-time-five'></i> Ketersediaan</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Saya</a>
        </nav>
        <div class="sidebar-user">
            <div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div>
            <div class="info"><div class="name"><?= htmlspecialchars($user['nama']) ?></div><div class="role">Dosen · <?= $user['nip'] ?></div></div>
            <a href="../logout.php" class="logout"><i class='bx bx-log-out'></i></a>
        </div>
    </aside>

    <div class="main">
        <h1 class="page-title">Selamat Datang, <?= htmlspecialchars($user['nama']) ?>!</h1>
        <p class="page-subtitle">Kelola jadwal konsultasi dan permintaan mahasiswa Anda hari ini.</p>

        <div class="stats">
            <div class="stat-card"><div class="stat-icon blue"><i class='bx bx-calendar'></i></div><div><div class="stat-value"><?= $totalBooking ?></div><div class="stat-label">Total Booking</div></div></div>
            <div class="stat-card"><div class="stat-icon green"><i class='bx bx-check-circle'></i></div><div><div class="stat-value"><?= $totalSetuju ?></div><div class="stat-label">Disetujui</div></div></div>
            <div class="stat-card"><div class="stat-icon yellow"><i class='bx bx-time-five'></i></div><div><div class="stat-value"><?= $totalTunggu ?></div><div class="stat-label">Menunggu</div></div></div>
        </div>

        <div class="grid-2">
            <!-- Permintaan Terbaru -->
            <div class="card">
                <div class="card-header"><h3><i class='bx bx-bell' style="color:#1E40AF"></i> Permintaan Baru</h3><a href="permintaan.php" class="btn btn-outline">Semua</a></div>
                <div class="card-body">
                    <?php if(empty($permintaanList)): ?>
                        <div class="empty-state"><i class='bx bx-check-double'></i><p>Tidak ada permintaan baru</p></div>
                    <?php else: foreach($permintaanList as $p): ?>
                        <div class="item">
                            <div class="item-info">
                                <h4><?= htmlspecialchars($p['nama_mahasiswa']) ?></h4>
                                <p>Topik: <?= htmlspecialchars($p['topik']) ?></p>
                                <p><i class='bx bx-calendar'></i> <?= formatTanggalSingkat($p['tanggal']) ?> · <?= formatWaktu($p['waktu_mulai']) ?></p>
                            </div>
                            <div style="display:flex;gap:4px">
                                <a href="permintaan.php?aksi=setuju&id=<?=$p['id']?>" class="btn btn-success"><i class='bx bx-check'></i></a>
                                <a href="permintaan.php?aksi=tolak&id=<?=$p['id']?>" class="btn btn-danger"><i class='bx bx-x'></i></a>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Jadwal Hari Ini -->
            <div class="card">
                <div class="card-header"><h3><i class='bx bx-calendar-event' style="color:#1E40AF"></i> Jadwal Hari Ini</h3></div>
                <div class="card-body">
                    <?php if(empty($jadwalHariIni)): ?>
                        <div class="empty-state"><i class='bx bx-coffee'></i><p>Tidak ada jadwal hari ini</p></div>
                    <?php else: foreach($jadwalHariIni as $j): ?>
                        <div class="item">
                            <div class="item-info">
                                <h4><?= formatWaktu($j['waktu_mulai']) ?> - <?= formatWaktu($j['waktu_selesai']) ?></h4>
                                <p><?= htmlspecialchars($j['nama_mahasiswa']) ?></p>
                                <p>Topik: <?= htmlspecialchars($j['topik']) ?></p>
                                <?php if($j['hasil_konsultasi']): ?>
                                    <div style="margin-top:8px;padding:8px;background:#F8FAFC;border-radius:6px;border:1px solid #E2E8F0;font-size:12px;color:#475569">
                                        <strong>Hasil:</strong> <?= htmlspecialchars($j['hasil_konsultasi']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
