<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'mahasiswa') { alihkan('../index.php'); }
$user = ambilPengguna();
$daftarDosen = $pdo->query("SELECT * FROM pengguna WHERE peran='dosen' ORDER BY nama")->fetchAll();

$sukses = $gagal = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dosen_id = (int)$_POST['dosen_id'];
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu_mulai'];
    $topik = trim($_POST['topik']);
    $waktu_selesai = date('H:i:s', strtotime($waktu) + 1800); // Durasi 30 menit

    // 1. Validasi Waktu Lampau
    $waktu_booking = strtotime("$tanggal $waktu");
    if ($waktu_booking < time()) {
        $gagal = 'Waktu sudah lewat! Silakan pilih tanggal dan jam di masa mendatang.';
    } else {
        // 2. Cek Kesesuaian Hari & Jam dengan Ketersediaan Dosen
        $hariArr = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        $hariPilihan = $hariArr[date('w', strtotime($tanggal))];

        $cekTersedia = $pdo->prepare("SELECT * FROM ketersediaan WHERE dosen_id=? AND hari=? AND waktu_mulai<=? AND waktu_selesai>=?");
        $cekTersedia->execute([$dosen_id, $hariPilihan, $waktu, $waktu_selesai]);
        
        if (!$cekTersedia->fetch()) {
            $gagal = "Dosen tidak tersedia pada hari $hariPilihan di jam tersebut. Silakan cek jadwal ketersediaan dosen.";
        } else {
            // 3. Cek Bentrok dengan Booking Lain
            $cek = $pdo->prepare("SELECT COUNT(*) FROM konsultasi WHERE dosen_id=? AND tanggal=? AND status!='ditolak' AND ((waktu_mulai<=? AND waktu_selesai>?) OR (waktu_mulai<? AND waktu_selesai>=?))");
            $cek->execute([$dosen_id, $tanggal, $waktu, $waktu, $waktu_selesai, $waktu_selesai]);
            
            if ($cek->fetchColumn() > 0) {
                $gagal = 'Jadwal bentrok! Dosen sudah memiliki konsultasi di waktu tersebut.';
            } else {
                $pdo->prepare("INSERT INTO konsultasi (mahasiswa_id,dosen_id,tanggal,waktu_mulai,waktu_selesai,topik) VALUES (?,?,?,?,?,?)")->execute([$user['id'], $dosen_id, $tanggal, $waktu, $waktu_selesai, $topik]);
                $sukses = 'Booking berhasil dikirim! Menunggu persetujuan dosen.';
            }
        }
    }
}
$dataKetersediaan = [];
$dataKesibukan = [];
foreach ($daftarDosen as $d) {
    // Ambil Ketersediaan (Jam Kerja)
    $av = $pdo->prepare("SELECT * FROM ketersediaan WHERE dosen_id=? ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')");
    $av->execute([$d['id']]); 
    $dataKetersediaan[$d['id']] = $av->fetchAll();

    // Ambil Kesibukan (Yang sudah dibooking orang lain)
    $busy = $pdo->prepare("SELECT tanggal, waktu_mulai, waktu_selesai FROM konsultasi WHERE dosen_id=? AND status='disetujui' AND tanggal >= CURDATE() ORDER BY tanggal, waktu_mulai");
    $busy->execute([$d['id']]);
    $dataKesibukan[$d['id']] = $busy->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Konsultasi - JadwalinKampus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#F0F4F8;min-height:100vh;display:flex;color:#1E293B}
        .sidebar{width:260px;background:#fff;border-right:1px solid #E2E8F0;height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;z-index:100;transition:transform .3s}
        .sidebar-brand{padding:24px 20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid #E2E8F0}
        .sidebar-brand .icon{width:40px;height:40px;background:linear-gradient(135deg,#2563EB,#3B82F6);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px}
        .sidebar-brand h2{font-size:20px;font-weight:700}.sidebar-brand small{font-size:13px;color:#64748B;display:block}
        .sidebar-nav{padding:16px 12px;flex:1}
        .sidebar-nav a{display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;color:#475569;text-decoration:none;font-size:16px;font-weight:500;margin-bottom:4px;transition:all .2s}
        .sidebar-nav a:hover{background:#EFF6FF;color:#2563EB}.sidebar-nav a.active{background:#2563EB;color:#fff}.sidebar-nav a i{font-size:20px}
        .sidebar-user{padding:16px 20px;border-top:1px solid #E2E8F0;display:flex;align-items:center;gap:12px}
        .sidebar-user .avatar{width:40px;height:40px;background:#DBEAFE;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#2563EB;font-weight:700;font-size:16px}
        .sidebar-user .info{flex:1}.sidebar-user .info .name{font-size:15px;font-weight:600}.sidebar-user .info .role{font-size:13px;color:#64748B}
        .sidebar-user .logout{color:#94A3B8;font-size:24px;text-decoration:none}.sidebar-user .logout:hover{color:#EF4444}
        .main{margin-left:260px;flex:1;padding:32px;min-height:100vh}
        .page-title{font-size:28px;font-weight:700;margin-bottom:4px}.page-subtitle{color:#64748B;font-size:16px;margin-bottom:28px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden}
        .card-header{padding:18px 20px;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;gap:8px}
        .card-header h3{font-size:18px;font-weight:600}.card-body{padding:24px}
        .form-group{margin-bottom:20px}
        .form-group label{display:block;font-size:15px;font-weight:600;color:#334155;margin-bottom:8px}.form-group label i{color:#3B82F6}
        .form-group input,.form-group select,.form-group textarea{width:100%;padding:14px 18px;border:1.5px solid #E2E8F0;border-radius:10px;font-family:inherit;font-size:16px;color:#1E293B;background:#F8FAFC;outline:none;transition:all .2s}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#3B82F6;background:#fff;box-shadow:0 0 0 3px rgba(59,130,246,.12)}
        .form-group textarea{resize:vertical;min-height:100px}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .btn{padding:14px 28px;border-radius:10px;font-family:inherit;font-size:16px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:8px;transition:all .2s;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,#2563EB,#3B82F6);color:#fff;box-shadow:0 2px 8px rgba(37,99,235,.25)}.btn-primary:hover{transform:translateY(-1px)}
        .alert{padding:16px 22px;border-radius:10px;font-size:15px;margin-bottom:20px;display:flex;align-items:center;gap:8px}
        .alert-success{background:#D1FAE5;color:#059669;border:1px solid #A7F3D0}.alert-error{background:#FEE2E2;color:#DC2626;border:1px solid #FECACA}
        .avail-info{background:#EFF6FF;border:1px solid #DBEAFE;border-radius:10px;padding:16px 22px;margin-top:12px;font-size:15px;color:#1E40AF}
        .avail-item{display:flex;align-items:center;gap:8px;padding:6px 0}.avail-item i{font-size:18px}
        .busy-info{background:#FFF7ED;border:1px solid #FFEDD5;border-radius:10px;padding:16px 22px;margin-top:12px;font-size:14px;color:#9A3412}
        .busy-title{font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:6px;color:#C2410C}
        .busy-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:8px}
        .busy-tag{background:#FFEDD5;padding:4px 10px;border-radius:6px;border:1px solid #FED7AA;display:flex;align-items:center;gap:5px}
        .mobile-header{display:none;position:fixed;top:0;left:0;right:0;height:56px;background:#fff;border-bottom:1px solid #E2E8F0;align-items:center;padding:0 16px;gap:12px;z-index:99}
        .mobile-header .menu-btn{background:none;border:none;font-size:24px;color:#475569;cursor:pointer}
        .overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:99}
        @media(max-width:768px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.overlay.show{display:block}.mobile-header{display:flex}.main{margin-left:0;padding:72px 16px 24px}.form-row{grid-template-columns:1fr}}
        @keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}.main>*{animation:fadeIn .4s ease}
    </style>
</head>
<body>
    <div class="mobile-header"><button class="menu-btn" onclick="document.querySelector('.sidebar').classList.toggle('open');document.querySelector('.overlay').classList.toggle('show')"><i class='bx bx-menu'></i></button><span style="font-weight:600;font-size:15px"><i class='bx bxs-graduation' style="color:#2563EB"></i> JadwalinKampus</span></div>
    <div class="overlay" onclick="document.querySelector('.sidebar').classList.remove('open');this.classList.remove('show')"></div>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Booking Konsultasi</small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="booking.php" class="active"><i class='bx bx-calendar-plus'></i> Booking Konsultasi</a>
            <a href="jadwal.php"><i class='bx bx-calendar'></i> Jadwal Saya</a>
            <a href="riwayat.php"><i class='bx bx-history'></i> Riwayat</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Saya</a>
        </nav>
        <div class="sidebar-user"><div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div><div class="info"><div class="name"><?= htmlspecialchars($user['nama']) ?></div><div class="role">Mahasiswa · <?= $user['nim'] ?></div></div><a href="../logout.php" class="logout"><i class='bx bx-log-out'></i></a></div>
    </aside>
    <div class="main">
        <h1 class="page-title">Booking Konsultasi</h1>
        <p class="page-subtitle">Ajukan jadwal konsultasi dengan dosen pilihan Anda</p>
        <?php if($sukses): ?><div class="alert alert-success"><i class='bx bx-check-circle'></i> <?= $sukses ?></div><?php endif; ?>
        <?php if($gagal): ?><div class="alert alert-error"><i class='bx bx-error-circle'></i> <?= $gagal ?></div><?php endif; ?>
        <div class="card">
            <div class="card-header"><i class='bx bx-calendar-plus' style="color:#2563EB;font-size:20px"></i><h3>Formulir Booking</h3></div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label><i class='bx bx-user'></i> Pilih Dosen</label>
                        <select name="dosen_id" id="dosenSelect" required onchange="tampilKetersediaan(this.value)">
                            <option value="">-- Pilih Dosen --</option>
                            <?php foreach($daftarDosen as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nama']) ?> - <?= htmlspecialchars($d['jurusan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="infoKetersediaan"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label><i class='bx bx-calendar'></i> Tanggal</label><input type="date" name="tanggal" required min="<?= date('Y-m-d') ?>"></div>
                        <div class="form-group"><label><i class='bx bx-time-five'></i> Waktu Mulai</label><input type="time" name="waktu_mulai" required></div>
                    </div>
                    <div class="form-group"><label><i class='bx bx-notepad'></i> Topik / Deskripsi Konsultasi</label><textarea name="topik" placeholder="Jelaskan topik yang ingin dikonsultasikan..." required></textarea></div>
                    <button type="submit" class="btn btn-primary"><i class='bx bx-send'></i> Kirim Booking</button>
                </form>
            </div>
        </div>
    </div>
    <script>
    const dataKetersediaan=<?= json_encode($dataKetersediaan) ?>;
    const dataKesibukan=<?= json_encode($dataKesibukan) ?>;

    function formatTanggalIndo(tgl) {
        const d = new Date(tgl);
        const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return d.getDate() + ' ' + bulan[d.getMonth()];
    }

    function tampilKetersediaan(id){
        const el=document.getElementById('infoKetersediaan');
        if(!id) { el.innerHTML=''; return; }
        
        let html = '';

        // Tampilkan Jam Kerja
        if(dataKetersediaan[id] && dataKetersediaan[id].length){
            html += '<div class="avail-info"><strong><i class="bx bx-info-circle"></i> Jam Kerja Dosen:</strong>';
            dataKetersediaan[id].forEach(a=>{
                html += `<div class="avail-item"><i class="bx bx-check"></i> ${a.hari}: ${a.waktu_mulai.substring(0,5)} - ${a.waktu_selesai.substring(0,5)}</div>`
            });
            html += '</div>';
        }

        // Tampilkan Jam yang sudah terisi (Kesibukan)
        if(dataKesibukan[id] && dataKesibukan[id].length){
            html += '<div class="busy-info">';
            html += '<div class="busy-title"><i class="bx bx-calendar-x"></i> Jam Terisi (Sudah Dibooking):</div>';
            html += '<div class="busy-grid">';
            dataKesibukan[id].forEach(b => {
                html += `<div class="busy-tag"><i class='bx bx-time' style="font-size:12px"></i> ${formatTanggalIndo(b.tanggal)} (${b.waktu_mulai.substring(0,5)})</div>`;
            });
            html += '</div>';
            html += '<div style="margin-top:10px; font-size:12px; color:#9A3412; font-style:italic"><i class="bx bx-info-circle"></i> Catatan: Setiap sesi konsultasi berlangsung selama 30 menit. Silakan pilih waktu minimal 30 menit setelah jadwal yang sudah terisi.</div>';
            html += '</div>';
        } else if(id) {
            html += '<div class="busy-info" style="background:#F0FDF4; border-color:#DCFCE7; color:#166534">';
            html += '<i class="bx bx-smile"></i> Belum ada jadwal booking untuk dosen ini. Semua jam kerja masih tersedia!</div>';
        }
        el.innerHTML = html;
    }
    </script>
</body>
</html>
