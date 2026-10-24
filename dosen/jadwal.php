<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'dosen') { alihkan('../index.php'); }
$user = ambilPengguna();

// Kalender
$bln = isset($_GET['b']) ? (int)$_GET['b'] : (int)date('n');
$thn = isset($_GET['t']) ? (int)$_GET['t'] : (int)date('Y');
$stmt = $pdo->prepare("SELECT k.*, p.nama as nama_mahasiswa FROM konsultasi k JOIN pengguna p ON k.mahasiswa_id = p.id WHERE k.dosen_id = ? AND MONTH(k.tanggal) = ? AND YEAR(k.tanggal) = ? AND k.status = 'disetujui'");
$stmt->execute([$user['id'], $bln, $thn]);
$dataKal = $stmt->fetchAll();

$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

// Simpan Hasil Konsultasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_hasil'])) {
    $id = (int)$_POST['konsultasi_id'];
    $hasil = $_POST['hasil_konsultasi'];
    $stmt = $pdo->prepare("UPDATE konsultasi SET hasil_konsultasi = ? WHERE id = ? AND dosen_id = ?");
    $stmt->execute([$hasil, $id, $user['id']]);
    alihkan('jadwal.php?pesan=berhasil');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Konsultasi - JadwalinKampus</title>
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
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);padding:24px}
        .calendar-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
        .cal-nav{display:flex;align-items:center;gap:8px}
        .btn-nav{width:36px;height:36px;border-radius:10px;border:1px solid #E2E8F0;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#475569;transition:all .2s;text-decoration:none}
        .btn-nav:hover{background:#EFF6FF;border-color:#BFDBFE;color:#1E40AF}
        .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:8px}
        .cal-day-name{text-align:center;font-size:14px;font-weight:600;color:#94A3B8;padding:10px 0;text-transform:uppercase}
        .cal-day{min-height:120px;background:#F8FAFC;border:1px solid #F1F5F9;border-radius:12px;padding:10px;position:relative}
        .cal-day.today{background:#EFF6FF;border-color:#BFDBFE}
        .cal-day-num{font-size:16px;font-weight:600;color:#64748B;margin-bottom:8px}
        .today .cal-day-num{color:#1E40AF}
        .event{background:#DBEAFE;border-left:3px solid #1E40AF;padding:6px 10px;border-radius:4px;font-size:12px;margin-bottom:6px;color:#1E40AF;line-height:1.2}
        .event-time{font-weight:700;display:block}
        .event{cursor:pointer;transition:transform .1s}.event:hover{transform:scale(1.02)}
        
        /* Modal Styles */
        .modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px}
        .modal.show{display:flex}
        .modal-content{background:#fff;border-radius:16px;width:100%;max-width:450px;overflow:hidden;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);animation:slideUp .3s ease-out;color:#1E293B}
        @keyframes slideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
        .modal-header{padding:20px;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;justify-content:space-between}
        .modal-header h3{font-size:18px;font-weight:700;color:#1E293B}
        .modal-close{background:none;border:none;font-size:24px;color:#94A3B8;cursor:pointer}
        .modal-body{padding:20px}
        .detail-item{margin-bottom:16px;display:flex;gap:12px}
        .detail-item i{font-size:20px;color:#3B82F6;margin-top:2px}
        .detail-label{font-size:12px;color:#64748B;font-weight:600;text-transform:uppercase;margin-bottom:2px}
        .detail-value{font-size:15px;color:#1E293B;font-weight:500}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Jadwal Konsultasi Dosen</small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="permintaan.php"><i class='bx bx-message-square-detail'></i> Permintaan</a>
            <a href="jadwal.php" class="active"><i class='bx bx-calendar'></i> Jadwal Konsultasi</a>
            <a href="mahasiswa.php"><i class='bx bxs-user-detail'></i> Mahasiswa Konsultasi</a>
            <a href="ketersediaan.php"><i class='bx bx-time-five'></i> Ketersediaan</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Saya</a>
        </nav>
        <div class="sidebar-user"><div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div><div class="info"><div class="name"><?= htmlspecialchars($user['nama']) ?></div></div><a href="../logout.php" style="color:#94A3B8;font-size:20px"><i class='bx bx-log-out'></i></a></div>
    </aside>

    <div class="main">
        <h1 class="page-title">Jadwal Konsultasi</h1>
        <p class="page-subtitle">Kalender jadwal konsultasi yang sudah Anda setujui.</p>

        <?php if(isset($_GET['pesan'])): ?>
            <div style="background:#D1FAE5;color:#059669;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:8px;border:1px solid #A7F3D0">
                <i class='bx bx-check-circle'></i> Berhasil menyimpan hasil konsultasi!
            </div>
        <?php endif; ?>

        <div class="card">
            <?php
            $bP = $bln-1; $tP = $thn; if($bP<1){$bP=12;$tP--;}
            $bN = $bln+1; $tN = $thn; if($bN>12){$bN=1;$tN++;}
            $firstDay = mktime(0,0,0,$bln,1,$thn); $daysInMonth = date('t',$firstDay); $startDay = date('w',$firstDay); $today = date('Y-m-d');
            ?>
            <div class="calendar-header">
                <h3><?= $namaBulan[$bln] ?> <?= $thn ?></h3>
                <div class="cal-nav">
                    <a href="?b=<?=$bP?>&t=<?=$tP?>" class="btn-nav"><i class='bx bx-chevron-left'></i></a>
                    <a href="?b=<?=date('n')?>&t=<?=date('Y')?>" class="btn-nav" style="width:auto;padding:0 12px;font-size:12px;font-weight:600">Hari Ini</a>
                    <a href="?b=<?=$bN?>&t=<?=$tN?>" class="btn-nav"><i class='bx bx-chevron-right'></i></a>
                </div>
            </div>

            <div class="cal-grid">
                <?php foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $h): ?><div class="cal-day-name"><?=$h?></div><?php endforeach; ?>
                <?php for($i=0;$i<$startDay;$i++): ?><div class="cal-day" style="opacity:0.3"></div><?php endfor; ?>
                <?php for($d=1;$d<=$daysInMonth;$d++):
                    $dateStr = sprintf('%04d-%02d-%02d',$thn,$bln,$d);
                    $isToday = ($dateStr === $today);
                ?>
                    <div class="cal-day <?= $isToday ? 'today' : '' ?>">
                        <div class="cal-day-num"><?= $d ?></div>
                        <?php foreach($dataKal as $e): if($e['tanggal'] === $dateStr): ?>
                            <div class="event" onclick='showDetail(<?= json_encode($e) ?>)'>
                                <span class="event-time"><?= formatWaktu($e['waktu_mulai']) ?></span>
                                <?= htmlspecialchars($e['nama_mahasiswa']) ?>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                <?php endfor; ?>
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
    function showDetail(item) {
        const modal = document.getElementById('detailModal');
        const body = document.getElementById('modalBody');
        const today = new Date().toISOString().split('T')[0];
        
        let formContent = '';
        if (today >= item.tanggal) {
            formContent = `
                <form method="POST">
                    <input type="hidden" name="konsultasi_id" value="${item.id}">
                    <div class="detail-label" style="margin-bottom:8px">Hasil / Respon Konsultasi</div>
                    <textarea name="hasil_konsultasi" style="width:100%;height:100px;padding:12px;border:1px solid #E2E8F0;border-radius:10px;font-family:inherit;font-size:14px;resize:vertical;outline:none" placeholder="Tuliskan hasil diskusi atau tugas untuk mahasiswa...">${item.hasil_konsultasi || ''}</textarea>
                    <button type="submit" name="simpan_hasil" style="width:100%;margin-top:12px;padding:12px;background:#1E40AF;color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer">Simpan Hasil</button>
                </form>
            `;
        } else {
            formContent = `
                <div style="padding:15px;background:#FEF3C7;border-radius:10px;color:#D97706;font-size:14px;display:flex;align-items:center;gap:8px;margin-top:10px">
                    <i class='bx bx-info-circle' style="font-size:18px"></i>
                    Hasil konsultasi baru dapat diisi pada hari-H konsultasi.
                </div>
            `;
        }

        body.innerHTML = `
            <div class="detail-item">
                <i class='bx bx-notepad'></i>
                <div><div class="detail-label">Topik</div><div class="detail-value">${item.topik}</div></div>
            </div>
            <div class="detail-item">
                <i class='bx bx-user'></i>
                <div><div class="detail-label">Mahasiswa</div><div class="detail-value">${item.nama_mahasiswa}</div></div>
            </div>
            <div class="detail-item">
                <i class='bx bx-time-five'></i>
                <div><div class="detail-label">Waktu</div><div class="detail-value">${item.waktu_mulai.substring(0,5)} - ${item.waktu_selesai.substring(0,5)}</div></div>
            </div>
            
            <hr style="margin:16px 0;border:0;border-top:1px solid #F1F5F9;">
            ${formContent}
        `;
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
