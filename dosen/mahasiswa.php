<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'dosen') { alihkan('../index.php'); }
$user = ambilPengguna();

// Ambil daftar mahasiswa yang pernah konsultasi dengan dosen ini
$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.nama, p.nim, p.jurusan FROM pengguna p JOIN konsultasi k ON p.id = k.mahasiswa_id WHERE k.dosen_id = ? ORDER BY p.nama");
$stmt->execute([$user['id']]);
$daftarMahasiswa = $stmt->fetchAll();

// Jika ada mahasiswa yang dipilih, ambil riwayatnya
$mId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$riwayat = [];
$detailM = null;
if ($mId) {
    $stmtR = $pdo->prepare("SELECT * FROM konsultasi WHERE dosen_id = ? AND mahasiswa_id = ? ORDER BY tanggal DESC, waktu_mulai DESC");
    $stmtR->execute([$user['id'], $mId]);
    $riwayat = $stmtR->fetchAll();
    
    $stmtM = $pdo->prepare("SELECT * FROM pengguna WHERE id = ?");
    $stmtM->execute([$mId]);
    $detailM = $stmtM->fetch();
}

// Simpan Hasil Konsultasi (untuk edit di sini juga bisa)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_hasil'])) {
    $kId = (int)$_POST['konsultasi_id'];
    $hasil = $_POST['hasil_konsultasi'];
    $stmt = $pdo->prepare("UPDATE konsultasi SET hasil_konsultasi = ? WHERE id = ? AND dosen_id = ?");
    $stmt->execute([$hasil, $kId, $user['id']]);
    alihkan("mahasiswa.php?id=$mId&pesan=berhasil");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahasiswa Konsultasi - JadwalinKampus</title>
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
        .page-header{margin-bottom:28px}
        .page-title{font-size:28px;font-weight:700;margin-bottom:4px}.page-subtitle{color:#64748B;font-size:16px}
        
        .grid{display:grid;grid-template-columns:300px 1fr;gap:24px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden;display:flex;flex-direction:column}
        .card-header{padding:18px 20px;border-bottom:1px solid #F1F5F9;font-weight:700;font-size:16px;background:#fff;position:sticky;top:0;z-index:10}
        
        .m-list{flex:1;overflow-y:auto;max-height:calc(100vh - 200px)}
        .m-item{padding:16px 20px;border-bottom:1px solid #F1F5F9;cursor:pointer;transition:all .2s;text-decoration:none;display:block;color:inherit}
        .m-item:hover{background:#F8FAFC}.m-item.active{background:#EFF6FF;border-left:4px solid #1E40AF}
        .m-name{font-weight:600;font-size:15px;margin-bottom:2px}.m-info{font-size:12px;color:#64748B}
        
        .history-container{padding:24px}
        .history-item{background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:20px;margin-bottom:20px}
        .history-header{display:flex;justify-content:space-between;margin-bottom:12px}
        .h-date{font-weight:700;font-size:16px;color:#1E40AF}
        .h-status{font-size:12px;font-weight:700;text-transform:uppercase;padding:4px 10px;border-radius:20px}
        .status-disetujui{background:#D1FAE5;color:#059669}.status-menunggu{background:#FEF3C7;color:#D97706}.status-ditolak{background:#FEE2E2;color:#DC2626}
        .h-topic{font-weight:600;font-size:15px;margin-bottom:8px}
        
        .result-box{margin-top:16px;padding-top:16px;border-top:1px dashed #CBD5E1}
        .btn-save{background:#1E40AF;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;margin-top:8px}
        textarea{width:100%;min-height:80px;padding:12px;border:1px solid #E2E8F0;border-radius:10px;font-family:inherit;font-size:14px;outline:none;resize:vertical}
        
        .empty-history{text-align:center;padding:60px 20px;color:#94A3B8}
        .empty-history i{font-size:64px;margin-bottom:16px;display:block}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Daftar Mahasiswa Konsultasi </small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="permintaan.php"><i class='bx bx-message-square-detail'></i> Permintaan</a>
            <a href="jadwal.php"><i class='bx bx-calendar'></i> Jadwal Konsultasi</a>
            <a href="mahasiswa.php" class="active"><i class='bx bxs-user-detail'></i> Mahasiswa Konsultasi</a>
            <a href="ketersediaan.php"><i class='bx bx-time-five'></i> Ketersediaan</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Saya</a>
        </nav>
        <div class="sidebar-user"><div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div><div class="info"><div class="name"><?= htmlspecialchars($user['nama']) ?></div></div><a href="../logout.php" style="color:#94A3B8;font-size:20px"><i class='bx bx-log-out'></i></a></div>
    </aside>

    <div class="main">
        <div class="page-header">
            <h1 class="page-title">Mahasiswa Konsultasi</h1>
            <p class="page-subtitle">Kelola riwayat dan hasil konsultasi per mahasiswa.</p>
        </div>

        <div class="grid">
            <!-- Sidebar Mahasiswa -->
            <div class="card">
                <div class="card-header">Daftar Mahasiswa</div>
                <div class="m-list">
                    <?php if(empty($daftarMahasiswa)): ?>
                        <div style="padding:20px;color:#94A3B8;font-size:14px">Belum ada data mahasiswa.</div>
                    <?php else: foreach($daftarMahasiswa as $m): ?>
                        <a href="?id=<?=$m['id']?>" class="m-item <?=$mId==$m['id']?'active':''?>">
                            <div class="m-name"><?= htmlspecialchars($m['nama']) ?></div>
                            <div class="m-info"><?= $m['nim'] ?> · <?= htmlspecialchars($m['jurusan']) ?></div>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Detail Riwayat -->
            <div class="card">
                <?php if(!$mId): ?>
                    <div class="empty-history">
                        <i class='bx bx-user-circle'></i>
                        <p>Pilih mahasiswa di sebelah kiri untuk melihat riwayat konsultasi.</p>
                    </div>
                <?php else: ?>
                    <div class="card-header">Riwayat Konsultasi: <?= htmlspecialchars($detailM['nama']) ?></div>
                    <div class="history-container">
                        <?php if(empty($riwayat)): ?>
                            <div class="empty-history"><p>Tidak ada riwayat konsultasi.</p></div>
                        <?php else: foreach($riwayat as $r): ?>
                            <div class="history-item">
                                <div class="history-header">
                                    <div class="h-date"><?= formatTanggal($r['tanggal']) ?> <small style="font-weight:400;color:#64748B;font-size:13px"><?= formatWaktu($r['waktu_mulai']) ?></small></div>
                                    <div class="h-status status-<?=$r['status']?>"><?= ucfirst($r['status']) ?></div>
                                </div>
                                <div class="h-topic">Topik: <?= htmlspecialchars($r['topik']) ?></div>
                                
                                <?php if($r['status'] === 'disetujui'): ?>
                                <div class="result-box">
                                    <?php if (date('Y-m-d') >= $r['tanggal']): ?>
                                        <form method="POST">
                                            <input type="hidden" name="konsultasi_id" value="<?=$r['id']?>">
                                            <div style="font-size:12px;font-weight:700;color:#64748B;margin-bottom:8px">HASIL KONSULTASI:</div>
                                            <textarea name="hasil_konsultasi" placeholder="Berikan feedback atau hasil konsultasi..."><?= htmlspecialchars($r['hasil_konsultasi']) ?></textarea>
                                            <button type="submit" name="simpan_hasil" class="btn-save">Simpan Perubahan</button>
                                        </form>
                                    <?php else: ?>
                                        <div style="padding:15px;background:#FEF3C7;border-radius:10px;color:#D97706;font-size:14px;display:flex;align-items:center;gap:8px">
                                            <i class='bx bx-info-circle' style="font-size:18px"></i>
                                            Hasil konsultasi baru dapat diisi pada hari-H konsultasi (<?= formatTanggalSingkat($r['tanggal']) ?>).
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
