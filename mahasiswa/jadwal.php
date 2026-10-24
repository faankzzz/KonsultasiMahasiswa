<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'mahasiswa') { alihkan('../index.php'); }
$user = ambilPengguna();

$stmt = $pdo->prepare("SELECT k.*, p.nama as nama_dosen, p.jurusan FROM konsultasi k JOIN pengguna p ON k.dosen_id=p.id WHERE k.mahasiswa_id=? AND k.tanggal>=CURDATE() AND k.status='disetujui' ORDER BY k.tanggal, k.waktu_mulai");
$stmt->execute([$user['id']]);
$jadwal = $stmt->fetchAll();

$blnSkrg = isset($_GET['b']) ? (int)$_GET['b'] : (int)date('n');
$thnSkrg = isset($_GET['t']) ? (int)$_GET['t'] : (int)date('Y');
$stmtKal = $pdo->prepare("SELECT k.*, p.nama as nama_dosen, p.jurusan FROM konsultasi k JOIN pengguna p ON k.dosen_id=p.id WHERE k.mahasiswa_id=? AND MONTH(k.tanggal)=? AND YEAR(k.tanggal)=? AND k.status='disetujui'");
$stmtKal->execute([$user['id'], $blnSkrg, $thnSkrg]);
$dataKal = $stmtKal->fetchAll();
$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$blnSingkat = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Saya</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}body{font-family:'Inter',sans-serif;background:#F0F4F8;min-height:100vh;display:flex;color:#1E293B}
        .sidebar{width:260px;background:#fff;border-right:1px solid #E2E8F0;height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;z-index:100;transition:transform .3s}
        .sidebar-brand{padding:24px 20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid #E2E8F0}.sidebar-brand .icon{width:40px;height:40px;background:linear-gradient(135deg,#2563EB,#3B82F6);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px}.sidebar-brand h2{font-size:20px;font-weight:700}.sidebar-brand small{font-size:13px;color:#64748B;display:block}
        .sidebar-nav{padding:16px 12px;flex:1}.sidebar-nav a{display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;color:#475569;text-decoration:none;font-size:16px;font-weight:500;margin-bottom:4px;transition:all .2s}.sidebar-nav a:hover{background:#EFF6FF;color:#2563EB}.sidebar-nav a.active{background:#2563EB;color:#fff}.sidebar-nav a i{font-size:20px}
        .sidebar-user{padding:16px 20px;border-top:1px solid #E2E8F0;display:flex;align-items:center;gap:12px}.sidebar-user .avatar{width:40px;height:40px;background:#DBEAFE;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#2563EB;font-weight:700;font-size:16px}.sidebar-user .info{flex:1}.sidebar-user .info .name{font-size:15px;font-weight:600}.sidebar-user .info .role{font-size:13px;color:#64748B}.sidebar-user .logout{color:#94A3B8;font-size:24px;text-decoration:none}.sidebar-user .logout:hover{color:#EF4444}
        .main{margin-left:260px;flex:1;padding:32px;min-height:100vh}.page-title{font-size:28px;font-weight:700;margin-bottom:4px}.page-subtitle{color:#64748B;font-size:16px;margin-bottom:28px}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden;margin-bottom:24px}.card-header{padding:18px 20px;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;gap:8px}.card-header h3{font-size:18px;font-weight:600}.card-body{padding:20px}
        .booking-item{display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:1px solid #F1F5F9}.booking-item:last-child{border-bottom:none}
        .booking-date-box{width:56px;min-width:56px;text-align:center;background:#EFF6FF;border-radius:10px;padding:8px 4px}.booking-date-box .day{font-size:22px;font-weight:700;color:#2563EB;line-height:1}.booking-date-box .month{font-size:12px;color:#3B82F6;text-transform:uppercase;margin-top:2px}
        .booking-info{flex:1}.booking-info h4{font-size:15px;font-weight:600;margin-bottom:2px}.booking-info p{font-size:14px;color:#64748B}
        .badge{padding:4px 12px;border-radius:20px;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:4px;white-space:nowrap}.badge-success{background:#D1FAE5;color:#059669}.badge-warning{background:#FEF3C7;color:#D97706}.badge-danger{background:#FEE2E2;color:#DC2626}
        .calendar-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}.cal-nav{background:none;border:1px solid #E2E8F0;width:36px;height:36px;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#475569;transition:all .2s;text-decoration:none}.cal-nav:hover{background:#EFF6FF;color:#2563EB}
        .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center}.cal-day-name{font-size:13px;font-weight:600;color:#94A3B8;padding:6px 0}.cal-day{font-size:15px;padding:10px 4px;border-radius:8px;position:relative;cursor:pointer;transition:background .2s}.cal-day:hover{background:#F1F5F9}.cal-day.today{background:#2563EB;color:#fff;font-weight:700}.cal-day.has-booking::after{content:'';position:absolute;bottom:3px;left:50%;transform:translateX(-50%);width:6px;height:6px;border-radius:50%;background:#3B82F6}.cal-day.today.has-booking::after{background:#fff}
        .empty-state{text-align:center;padding:32px 16px;color:#94A3B8}.empty-state i{font-size:40px;margin-bottom:8px;display:block}
        .mobile-header{display:none;position:fixed;top:0;left:0;right:0;height:56px;background:#fff;border-bottom:1px solid #E2E8F0;align-items:center;padding:0 16px;gap:12px;z-index:99}.mobile-header .menu-btn{background:none;border:none;font-size:24px;color:#475569;cursor:pointer}.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:99}
        @media(max-width:768px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.overlay.show{display:block}.mobile-header{display:flex}.main{margin-left:0;padding:72px 16px 24px}.grid-2{grid-template-columns:1fr}}
        @keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}.main>*{animation:fadeIn .4s ease}
        
        /* Modal Styles */
        .modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px}
        .modal.show{display:flex}
        .modal-content{background:#fff;border-radius:16px;width:100%;max-width:450px;overflow:hidden;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);animation:slideUp .3s ease-out}
        @keyframes slideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
        .modal-header{padding:20px;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;justify-content:space-between}
        .modal-header h3{font-size:18px;font-weight:700;color:#1E293B}
        .modal-close{background:none;border:none;font-size:24px;color:#94A3B8;cursor:pointer}
        .modal-body{padding:20px}
        .detail-item{margin-bottom:16px;display:flex;gap:12px}
        .detail-item i{font-size:20px;color:#3B82F6;margin-top:2px}
        .detail-label{font-size:12px;color:#64748B;font-weight:600;text-transform:uppercase;margin-bottom:2px}
        .detail-value{font-size:15px;color:#1E293B;font-weight:500}
        .cal-day.has-booking{cursor:pointer}
    </style>
</head>
<body>
    <div class="mobile-header"><button class="menu-btn" onclick="document.querySelector('.sidebar').classList.toggle('open');document.querySelector('.overlay').classList.toggle('show')"><i class='bx bx-menu'></i></button><span style="font-weight:600;font-size:15px"><i class='bx bxs-graduation' style="color:#2563EB"></i> JadwalinKampus</span></div>
    <div class="overlay" onclick="document.querySelector('.sidebar').classList.remove('open');this.classList.remove('show')"></div>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Jadwal Konsultasi</small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="booking.php"><i class='bx bx-calendar-plus'></i> Booking Konsultasi</a>
            <a href="jadwal.php" class="active"><i class='bx bx-calendar'></i> Jadwal Saya</a>
            <a href="riwayat.php"><i class='bx bx-history'></i> Riwayat</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Saya</a>
        </nav>
        <div class="sidebar-user"><div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div><div class="info"><div class="name"><?= htmlspecialchars($user['nama']) ?></div><div class="role">Mahasiswa · <?= $user['nim'] ?></div></div><a href="../logout.php" class="logout"><i class='bx bx-log-out'></i></a></div>
    </aside>
    <div class="main">
        <h1 class="page-title">Jadwal Konsultasi Saya</h1>
        <p class="page-subtitle">Daftar jadwal konsultasi yang sudah disetujui dan kalender</p>
        <div class="grid-2">
            <div class="card">
                <div class="card-header"><i class='bx bx-calendar-check' style="color:#2563EB;font-size:20px"></i><h3>Jadwal Mendatang (Disetujui)</h3></div>
                <div class="card-body">
                    <?php if(empty($jadwal)): ?>
                        <div class="empty-state"><i class='bx bx-calendar-x'></i><p>Belum ada jadwal mendatang</p></div>
                    <?php else: foreach($jadwal as $b): $d=new DateTime($b['tanggal']); ?>
                        <div class="booking-item">
                            <div class="booking-date-box"><div class="day"><?= $d->format('j') ?></div><div class="month"><?= $blnSingkat[(int)$d->format('n')] ?></div></div>
                            <div class="booking-info">
                                <h4><?= htmlspecialchars($b['topik']) ?></h4>
                                <p><i class='bx bx-user'></i> <?= htmlspecialchars($b['nama_dosen']) ?> · <?= htmlspecialchars($b['jurusan']) ?></p>
                                <p><i class='bx bx-time-five'></i> <?= formatWaktu($b['waktu_mulai']) ?> - <?= formatWaktu($b['waktu_selesai']) ?></p>
                                <p><i class='bx bx-calendar'></i> <?= formatTanggal($b['tanggal']) ?></p>
                                <?php if($b['hasil_konsultasi']): ?>
                                    <div style="margin-top:8px;padding:10px;background:#F0F9FF;border-radius:8px;border:1px solid #BAE6FD;font-size:13px">
                                        <div style="color:#0369A1;font-weight:700;margin-bottom:2px">Hasil:</div>
                                        <?= nl2br(htmlspecialchars($b['hasil_konsultasi'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="badge badge-success"><i class='bx bx-check-circle'></i> Disetujui</span>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><i class='bx bx-calendar' style="color:#2563EB;font-size:20px"></i><h3>Kalender</h3></div>
                <div class="card-body">
                    <?php
                    $blnPrev=$blnSkrg-1;$thnPrev=$thnSkrg;if($blnPrev<1){$blnPrev=12;$thnPrev--;}
                    $blnNext=$blnSkrg+1;$thnNext=$thnSkrg;if($blnNext>12){$blnNext=1;$thnNext++;}
                    $hariPertama=mktime(0,0,0,$blnSkrg,1,$thnSkrg);$jmlHari=date('t',$hariPertama);$hariMulai=date('w',$hariPertama);$hariIni=date('Y-m-d');
                    $tglBooking=array_column($dataKal,'tanggal');
                    ?>
                    <div class="calendar-header">
                        <a href="?b=<?=$blnPrev?>&t=<?=$thnPrev?>" class="cal-nav"><i class='bx bx-chevron-left'></i></a>
                        <h3><?= $namaBulan[$blnSkrg] ?> <?= $thnSkrg ?></h3>
                        <a href="?b=<?=$blnNext?>&t=<?=$thnNext?>" class="cal-nav"><i class='bx bx-chevron-right'></i></a>
                    </div>
                    <div class="cal-grid">
                        <?php foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $h): ?><div class="cal-day-name"><?=$h?></div><?php endforeach; ?>
                        <?php for($i=0;$i<$hariMulai;$i++): ?><div class="cal-day"></div><?php endfor; ?>
                        <?php for($d=1;$d<=$jmlHari;$d++):
                            $tglStr=sprintf('%04d-%02d-%02d',$thnSkrg,$blnSkrg,$d);
                            $cls='cal-day'; $attr='';
                            if($tglStr===$hariIni) $cls.=' today';
                            $bookingsOnThisDay = array_filter($dataKal, function($k) use ($tglStr) { return $k['tanggal'] == $tglStr; });
                            if(!empty($bookingsOnThisDay)) {
                                $cls.=' has-booking';
                                $attr='onclick=\'showDetail('.json_encode(array_values($bookingsOnThisDay)).')\'';
                            }
                        ?><div class="<?=$cls?>" <?=$attr?>><?=$d?></div><?php endfor; ?>
                    </div>
                    <div style="margin-top:16px;font-size:12px;color:#64748B">
                        <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:8px;height:8px;border-radius:50%;background:#3B82F6;display:inline-block"></span> Klik tanggal untuk detail</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Konsultasi</h3>
                <button class="modal-close" onclick="closeModal()"><i class='bx bx-x'></i></button>
            </div>
            <div id="modalBody" class="modal-body">
                <!-- Dinamis via JS -->
            </div>
        </div>
    </div>

    <script>
    function showDetail(data) {
        const modal = document.getElementById('detailModal');
        const body = document.getElementById('modalBody');
        let html = '';
        
        data.forEach((item, index) => {
            if (index > 0) html += '<hr style="margin:16px 0;border:0;border-top:1px dashed #E2E8F0;">';
            html += `
                <div class="detail-item">
                    <i class='bx bx-notepad'></i>
                    <div><div class="detail-label">Topik</div><div class="detail-value">${item.topik}</div></div>
                </div>
                <div class="detail-item">
                    <i class='bx bx-user'></i>
                    <div><div class="detail-label">Dosen</div><div class="detail-value">${item.nama_dosen}</div></div>
                </div>
                <div class="detail-item">
                    <i class='bx bx-time-five'></i>
                    <div><div class="detail-label">Waktu</div><div class="detail-value">${item.waktu_mulai.substring(0,5)} - ${item.waktu_selesai.substring(0,5)}</div></div>
                </div>
                ${item.hasil_konsultasi ? `
                <div style="margin-top:16px;padding:12px;background:#F0F9FF;border-radius:10px;border:1px solid #BAE6FD">
                    <div class="detail-label" style="color:#0369A1"><i class='bx bx-message-detail'></i> Hasil Konsultasi</div>
                    <div class="detail-value" style="margin-top:4px">${item.hasil_konsultasi}</div>
                </div>
                ` : ''}
            `;
        });
        
        body.innerHTML = html;
        modal.classList.add('show');
    }
    
    function closeModal() {
        document.getElementById('detailModal').classList.remove('show');
    }
    
    window.onclick = function(e) {
        if (e.target.classList.contains('modal')) closeModal();
    }
    </script>
</body>
</html>
