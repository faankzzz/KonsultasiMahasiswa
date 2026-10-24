<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'mahasiswa') { alihkan('../index.php'); }
$user = ambilPengguna();
$stmt = $pdo->prepare("SELECT k.*, p.nama as nama_dosen FROM konsultasi k JOIN pengguna p ON k.dosen_id=p.id WHERE k.mahasiswa_id=? ORDER BY k.dibuat_pada DESC");
$stmt->execute([$user['id']]); $riwayat = $stmt->fetchAll();
$blnSingkat = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - JadwalinKampus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}body{font-family:'Inter',sans-serif;background:#F0F4F8;min-height:100vh;display:flex;color:#1E293B}
        .sidebar{width:260px;background:#fff;border-right:1px solid #E2E8F0;height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;z-index:100;transition:transform .3s}.sidebar-brand{padding:24px 20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid #E2E8F0}.sidebar-brand .icon{width:40px;height:40px;background:linear-gradient(135deg,#2563EB,#3B82F6);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px}.sidebar-brand h2{font-size:17px;font-weight:700}.sidebar-brand small{font-size:11px;color:#64748B;display:block}.sidebar-nav{padding:16px 12px;flex:1}.sidebar-nav a{display:flex;align-items:center;gap:12px;padding:11px 16px;border-radius:10px;color:#475569;text-decoration:none;font-size:14px;font-weight:500;margin-bottom:4px;transition:all .2s}.sidebar-nav a:hover{background:#EFF6FF;color:#2563EB}.sidebar-nav a.active{background:#2563EB;color:#fff}.sidebar-nav a i{font-size:20px}.sidebar-user{padding:16px 20px;border-top:1px solid #E2E8F0;display:flex;align-items:center;gap:12px}.sidebar-user .avatar{width:36px;height:36px;background:#DBEAFE;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#2563EB;font-weight:700;font-size:14px}.sidebar-user .info{flex:1}.sidebar-user .info .name{font-size:13px;font-weight:600}.sidebar-user .info .role{font-size:11px;color:#64748B}.sidebar-user .logout{color:#94A3B8;font-size:20px;text-decoration:none}.sidebar-user .logout:hover{color:#EF4444}
        .main{margin-left:260px;flex:1;padding:32px;min-height:100vh}.page-title{font-size:28px;font-weight:700;margin-bottom:4px}.page-subtitle{color:#64748B;font-size:16px;margin-bottom:28px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden}.card-header{padding:18px 20px;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;gap:8px}.card-header h3{font-size:18px;font-weight:600}.card-body{padding:20px}
        .booking-item{display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:1px solid #F1F5F9}.booking-item:last-child{border-bottom:none}.booking-date-box{width:56px;min-width:56px;text-align:center;background:#EFF6FF;border-radius:10px;padding:8px 4px}.booking-date-box .day{font-size:22px;font-weight:700;color:#2563EB;line-height:1}.booking-date-box .month{font-size:12px;color:#3B82F6;text-transform:uppercase;margin-top:2px}.booking-info{flex:1}.booking-info h4{font-size:15px;font-weight:600;margin-bottom:2px}.booking-info p{font-size:14px;color:#64748B}
        .badge{padding:4px 12px;border-radius:20px;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:4px;white-space:nowrap}.badge-success{background:#D1FAE5;color:#059669}.badge-warning{background:#FEF3C7;color:#D97706}.badge-danger{background:#FEE2E2;color:#DC2626}
        .empty-state{text-align:center;padding:32px 16px;color:#94A3B8}.empty-state i{font-size:40px;margin-bottom:8px;display:block}
        .mobile-header{display:none;position:fixed;top:0;left:0;right:0;height:56px;background:#fff;border-bottom:1px solid #E2E8F0;align-items:center;padding:0 16px;gap:12px;z-index:99}.mobile-header .menu-btn{background:none;border:none;font-size:24px;color:#475569;cursor:pointer}.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:99}
        @media(max-width:768px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.overlay.show{display:block}.mobile-header{display:flex}.main{margin-left:0;padding:72px 16px 24px}}
        @keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}.main>*{animation:fadeIn .4s ease}
    </style>
</head>
<body>
    <div class="mobile-header"><button class="menu-btn" onclick="document.querySelector('.sidebar').classList.toggle('open');document.querySelector('.overlay').classList.toggle('show')"><i class='bx bx-menu'></i></button><span style="font-weight:600;font-size:15px"><i class='bx bxs-graduation' style="color:#2563EB"></i> JadwalinKampus</span></div>
    <div class="overlay" onclick="document.querySelector('.sidebar').classList.remove('open');this.classList.remove('show')"></div>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Riwayat Jadwal</small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="booking.php"><i class='bx bx-calendar-plus'></i> Booking Konsultasi</a>
            <a href="jadwal.php"><i class='bx bx-calendar'></i> Jadwal Saya</a>
            <a href="riwayat.php" class="active"><i class='bx bx-history'></i> Riwayat</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Saya</a>
        </nav>
        <div class="sidebar-user"><div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div><div class="info"><div class="name"><?= htmlspecialchars($user['nama']) ?></div><div class="role">Mahasiswa · <?= $user['nim'] ?></div></div><a href="../logout.php" class="logout"><i class='bx bx-log-out'></i></a></div>
    </aside>
    <div class="main">
        <h1 class="page-title">Riwayat Konsultasi</h1>
        <p class="page-subtitle">Semua riwayat booking konsultasi Anda</p>
        <div class="card">
            <div class="card-header"><i class='bx bx-history' style="color:#2563EB;font-size:20px"></i><h3>Semua Riwayat (<?= count($riwayat) ?> data)</h3></div>
            <div class="card-body">
                <?php if(empty($riwayat)): ?>
                    <div class="empty-state"><i class='bx bx-inbox'></i><p>Belum ada riwayat konsultasi</p></div>
                <?php else: foreach($riwayat as $b): ?>
                    <div class="booking-item">
                        <div class="booking-date-box"><div class="day"><?= date('j',strtotime($b['tanggal'])) ?></div><div class="month"><?= $blnSingkat[(int)date('n',strtotime($b['tanggal']))] ?></div></div>
                        <div class="booking-info">
                            <h4><?= htmlspecialchars($b['topik']) ?></h4>
                            <p><i class='bx bx-user'></i> <?= htmlspecialchars($b['nama_dosen']) ?></p>
                            <p><i class='bx bx-time-five'></i> <?= formatWaktu($b['waktu_mulai']) ?> - <?= formatWaktu($b['waktu_selesai']) ?> · <?= formatTanggal($b['tanggal']) ?></p>
                            <p><i class='bx bx-calendar'></i> Dibuat: <?= formatTanggalSingkat($b['dibuat_pada']) ?></p>
                            <?php if($b['hasil_konsultasi']): ?>
                                <div style="margin-top:10px;padding:12px;background:#F0F9FF;border-radius:10px;border:1px solid #BAE6FD">
                                    <div style="font-size:11px;color:#0369A1;font-weight:700;text-transform:uppercase;margin-bottom:4px"><i class='bx bx-message-detail'></i> Hasil Konsultasi:</div>
                                    <div style="font-size:14px;color:#1E293B"><?= nl2br(htmlspecialchars($b['hasil_konsultasi'])) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="badge <?= kelasStatus($b['status']) ?>"><i class='bx <?= ikonStatus($b['status']) ?>'></i> <?= ucfirst($b['status']) ?></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
