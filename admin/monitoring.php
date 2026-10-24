<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'admin') { alihkan('../index.php'); }
$user = ambilPengguna();

// Filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where = "";
if ($status_filter) {
    $where = "WHERE k.status = " . $pdo->quote($status_filter);
}

$stmt = $pdo->query("SELECT k.*, m.nama as m_nama, m.nim, d.nama as d_nama FROM konsultasi k JOIN pengguna m ON k.mahasiswa_id=m.id JOIN pengguna d ON k.dosen_id=d.id $where ORDER BY k.tanggal DESC, k.waktu_mulai DESC");
$konsultasi = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Jadwal - JadwalinKampus</title>
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
        .sidebar-nav a:hover{background:rgba(255,255,255,0.05);color:#fff}.sidebar-nav a.active{background:#3B82F6;color:#fff}
        .sidebar-user{padding:16px 20px;border-top:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;gap:12px}
        .main{margin-left:260px;flex:1;padding:32px;min-height:100vh}
        .page-title{font-size:28px;font-weight:700;margin-bottom:4px}.page-subtitle{color:#64748B;font-size:16px;margin-bottom:28px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden}
        .card-header{padding:20px;border-bottom:1px solid #F1F5F9;display:flex;justify-content:space-between;align-items:center}
        .card-body{padding:0}
        table{width:100%;border-collapse:collapse}
        th{background:#F8FAFC;padding:16px 20px;text-align:left;font-size:15px;font-weight:600;color:#64748B;border-bottom:1px solid #E2E8F0}
        td{padding:18px 20px;font-size:16px;border-bottom:1px solid #F1F5F9}
        .badge{padding:6px 12px;border-radius:20px;font-size:13px;font-weight:600}
        .badge-warning{background:#FEF3C7;color:#D97706}.badge-success{background:#D1FAE5;color:#059669}.badge-danger{background:#FEE2E2;color:#DC2626}
        .filter-btn{padding:8px 16px;border-radius:8px;font-size:14px;text-decoration:none;background:#F1F5F9;color:#475569;border:1px solid #E2E8F0}
        .filter-btn.active{background:#3B82F6;color:#fff;border-color:#3B82F6}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Monitoring Jadwal Admin</small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="pengguna.php"><i class='bx bxs-user-account'></i> Kelola Pengguna</a>
            <a href="monitoring.php" class="active"><i class='bx bx-calendar-event'></i> Monitoring Jadwal</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Admin</a>
        </nav>
        <div class="sidebar-user"><div class="avatar">A</div><div class="info"><div style="font-size:13px;font-weight:600"><?= $user['nama'] ?></div></div></div>
    </aside>

    <div class="main">
        <h1 class="page-title">Monitoring Jadwal</h1>
        <p class="page-subtitle">Pantau seluruh aktivitas konsultasi yang terdaftar di sistem.</p>

        <div class="card">
            <div class="card-header">
                <h3 style="font-size:15px">Semua Konsultasi</h3>
                <div style="display:flex;gap:8px">
                    <a href="monitoring.php" class="filter-btn <?= $status_filter==''?'active':'' ?>">Semua</a>
                    <a href="?status=menunggu" class="filter-btn <?= $status_filter=='menunggu'?'active':'' ?>">Menunggu</a>
                    <a href="?status=disetujui" class="filter-btn <?= $status_filter=='disetujui'?'active':'' ?>">Disetujui</a>
                    <a href="?status=ditolak" class="filter-btn <?= $status_filter=='ditolak'?'active':'' ?>">Ditolak</a>
                </div>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Dosen</th>
                            <th>Jadwal</th>
                            <th>Topik</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($konsultasi)): ?>
                            <tr><td colspan="5" style="text-align:center;padding:40px;color:#94A3B8">Tidak ada data konsultasi ditemukan</td></tr>
                        <?php else: foreach($konsultasi as $k): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600"><?= htmlspecialchars($k['m_nama']) ?></div>
                                    <div style="font-size:12px;color:#64748B"><?= $k['nim'] ?></div>
                                </td>
                                <td><?= htmlspecialchars($k['d_nama']) ?></td>
                                <td>
                                    <div><?= formatTanggalSingkat($k['tanggal']) ?></div>
                                    <div style="font-size:12px;color:#64748B"><?= formatWaktu($k['waktu_mulai']) ?> - <?= formatWaktu($k['waktu_selesai']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($k['topik']) ?></td>
                                <td><span class="badge <?= kelasStatus($k['status']) ?>"><?= ucfirst($k['status']) ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
