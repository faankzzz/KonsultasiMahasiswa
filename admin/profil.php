<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'admin') { alihkan('../index.php'); }
$user = ambilPengguna();
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE pengguna SET nama=?, email=?, kata_sandi=? WHERE id=?");
        $stmt->execute([$nama, $email, $hashed, $user['id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE pengguna SET nama=?, email=? WHERE id=?");
        $stmt->execute([$nama, $email, $user['id']]);
    }
    $pesan = 'Profil Admin berhasil diperbarui!';
    $user = ambilPengguna();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - JadwalinKampus</title>
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
        .sidebar-user .avatar{width:40px;height:40px;background:rgba(255,255,255,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:16px}
        .main{margin-left:260px;flex:1;padding:32px;min-height:100vh}
        .page-title{font-size:28px;font-weight:700;margin-bottom:4px}.page-subtitle{color:#64748B;font-size:16px;margin-bottom:28px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);max-width:600px}
        .card-header{padding:18px 20px;border-bottom:1px solid #F1F5F9;font-weight:600;font-size:18px;color:#1E293B}
        .card-body{padding:24px}
        .form-group{margin-bottom:20px}
        .form-group label{display:block;font-size:15px;font-weight:600;color:#334155;margin-bottom:8px}
        .form-control{width:100%;padding:12px 16px;border:1.5px solid #E2E8F0;border-radius:10px;font-family:inherit;font-size:16px;outline:none;background:#F8FAFC}
        .form-control:focus{border-color:#3B82F6;background:#fff}
        .btn{padding:12px 24px;border-radius:10px;font-family:inherit;font-size:16px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:8px;transition:all .2s;text-decoration:none}
        .btn-primary{background:#3B82F6;color:#fff}
        .alert{padding:14px 18px;border-radius:10px;font-size:15px;margin-bottom:20px;background:#D1FAE5;color:#059669;border:1px solid #A7F3D0}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Profil Admin</small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="pengguna.php"><i class='bx bxs-user-account'></i> Kelola Pengguna</a>
            <a href="monitoring.php"><i class='bx bx-calendar-event'></i> Monitoring Jadwal</a>
            <a href="profil.php" class="active"><i class='bx bxs-user-circle'></i> Profil Admin</a>
        </nav>
        <div class="sidebar-user"><div class="avatar">A</div><div class="info"><div style="font-size:15px;font-weight:600"><?= htmlspecialchars($user['nama']) ?></div><div style="font-size:13px;color:#94A3B8">Administrator</div></div><a href="../logout.php" style="color:#94A3B8;font-size:24px"><i class='bx bx-log-out'></i></a></div>
    </aside>
    <div class="main">
        <h1 class="page-title">Profil Administrator</h1>
        <p class="page-subtitle">Kelola informasi data diri dan kata sandi admin.</p>
        <?php if($pesan): ?><div class="alert"><i class='bx bx-check-circle'></i> <?= $pesan ?></div><?php endif; ?>
        <div class="card">
            <div class="card-header">Edit Profil Admin</div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required></div>
                    <div class="form-group"><label>Kata Sandi Baru (Kosongkan jika tidak ingin ganti)</label><input type="password" name="password" class="form-control" placeholder="••••••••"></div>
                    <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
