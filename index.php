<?php
require_once 'config.php';

// Jika sudah login, arahkan ke dashboard sesuai peran
if (sudahLogin()) {
    $p = ambilPengguna();
    if ($p) {
        switch ($p['peran']) {
            case 'mahasiswa': alihkan('mahasiswa/dashboard.php'); break;
            case 'dosen': alihkan('dosen/dashboard.php'); break;
            case 'admin': alihkan('admin/dashboard.php'); break;
        }
    }
}

$pesan_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $kata_sandi = $_POST['kata_sandi'] ?? '';
    $peran = $_POST['peran'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE email = ? AND peran = ?");
    $stmt->execute([$email, $peran]);
    $pengguna = $stmt->fetch();

    if ($pengguna && password_verify($kata_sandi, $pengguna['kata_sandi'])) {
        $_SESSION['pengguna_id'] = $pengguna['id'];
        $_SESSION['peran'] = $pengguna['peran'];
        switch ($pengguna['peran']) {
            case 'mahasiswa': alihkan('mahasiswa/dashboard.php'); break;
            case 'dosen': alihkan('dosen/dashboard.php'); break;
            case 'admin': alihkan('admin/dashboard.php'); break;
        }
    } else {
        $pesan_error = 'Email, password, atau peran salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JadwalinKampus - Login</title>
    <meta name="description" content="Sistem manajemen jadwal dan booking konsultasi mahasiswa dengan dosen">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%;}
body{font-family:'Inter',sans-serif;background:#F0F4F8;min-height:100vh;display:flex;align-items:center;justify-content:center}
        .login-container{display:flex;width:960px;max-width:95vw;min-height:580px;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.12);animation:fadeIn .5s ease}
        .login-left{flex:1;background:linear-gradient(135deg,#1E40AF 0%,#3B82F6 50%,#60A5FA 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 36px;position:relative;overflow:hidden;color:#fff}
        .login-left::before{content:'';position:absolute;top:-60px;right:-60px;width:200px;height:200px;background:rgba(255,255,255,.08);border-radius:50%}
        .login-left::after{content:'';position:absolute;bottom:-80px;left:-40px;width:250px;height:250px;background:rgba(255,255,255,.06);border-radius:50%}
        .login-left img{width:80%;max-width:320px;border-radius:16px;margin-bottom:28px;position:relative;z-index:1;box-shadow:0 12px 40px rgba(0,0,0,.2)}
        .login-brand{display:flex;align-items:center;gap:12px;margin-bottom:10px;position:relative;z-index:1}
        .login-brand-icon{width:48px;height:48px;background:rgba(255,255,255,.2);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;backdrop-filter:blur(10px)}
        .login-brand h1{font-size:32px;font-weight:800;letter-spacing:-.5px}
        .login-tagline{text-align:center;font-size:16px;opacity:.85;position:relative;z-index:1;margin-bottom:24px}
        .login-features{display:flex;gap:20px;position:relative;z-index:1}
        .login-feature{display:flex;flex-direction:column;align-items:center;gap:6px;font-size:14px;opacity:.9}
        .login-feature i{font-size:24px;background:rgba(255,255,255,.15);width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(10px)}
        .login-right{flex:1;display:flex;align-items:center;justify-content:center;padding:48px 44px}
        .login-form{width:100%;max-width:360px}
        .login-form h2{font-size:32px;font-weight:700;color:#1E293B;margin-bottom:8px}
        .login-form>p{color:#64748B;font-size:16px;margin-bottom:32px}
        .form-group{margin-bottom:20px}
        .form-group label{display:flex;align-items:center;gap:6px;font-size:15px;font-weight:600;color:#334155;margin-bottom:8px}
        .form-group label i{font-size:18px;color:#3B82F6}
        .form-group input,.form-group select{width:100%;padding:14px 18px;border:1.5px solid #E2E8F0;border-radius:10px;font-family:inherit;font-size:16px;color:#1E293B;background:#F8FAFC;transition:all .2s;outline:none}
        .form-group input:focus,.form-group select:focus{border-color:#3B82F6;background:#fff;box-shadow:0 0 0 3px rgba(59,130,246,.12)}
        .form-group input::placeholder{color:#94A3B8}
        .password-wrapper{position:relative}
        .password-wrapper input{padding-right:44px}
        .toggle-password{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94A3B8;cursor:pointer;font-size:20px;display:flex}
        .toggle-password:hover{color:#3B82F6}
        .btn-login{width:100%;padding:16px;background:linear-gradient(135deg,#2563EB,#3B82F6);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:17px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;box-shadow:0 4px 12px rgba(37,99,235,.3)}
        .btn-login:hover{background:linear-gradient(135deg,#1D4ED8,#2563EB);transform:translateY(-1px);box-shadow:0 6px 20px rgba(37,99,235,.4)}
        .btn-login:active{transform:translateY(0)}
        .demo-info{margin-top:20px;padding:14px 18px;background:#EFF6FF;border-radius:10px;border:1px solid #DBEAFE;font-size:14px;color:#1E40AF;display:flex;align-items:flex-start;gap:8px}
        .demo-info i{font-size:18px;flex-shrink:0;margin-top:1px}
        .error-msg{background:#FEE2E2;color:#DC2626;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px;display:flex;align-items:center;gap:8px;border:1px solid #FECACA}
        @media(max-width:768px){body{padding:16px}.login-container{flex-direction:column;min-height:auto}.login-left{padding:32px 24px}.login-left img{max-width:200px}.login-right{padding:32px 24px}}
        @keyframes fadeIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <img src="assets/login-bg.png" alt="Ilustrasi Kampus">
            <div class="login-brand">
                <div class="login-brand-icon"><i class='bx bxs-graduation'></i></div>
                <h1>JadwalinKampus</h1>
            </div>
            <p class="login-tagline">Atur Konsultasi Akademikmu &amp;  Tanpa Ribet! </p>
            <div class="login-features">
                <div class="login-feature"><i class='bx bx-calendar-check'></i><span>Terjadwal</span></div>
                <div class="login-feature"><i class='bx bx-time-five'></i><span>Bebas Bentrok</span></div>
                <div class="login-feature"><i class='bx bx-chat'></i><span>Mudah</span></div>
            </div>
        </div>
        <div class="login-right">
            <form class="login-form" method="POST">
                <h2>Selamat Datang!</h2>
                <p>Silakan login untuk melanjutkan</p>
                <?php if ($pesan_error): ?>
                    <div class="error-msg"><i class='bx bx-error-circle'></i> <?= htmlspecialchars($pesan_error) ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label><i class='bx bx-envelope'></i> Email</label>
                    <input type="email" name="email" placeholder="nama@gmail.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><i class='bx bx-lock-alt'></i> Kata Sandi</label>
                    <div class="password-wrapper">
                        <input type="password" name="kata_sandi" id="pw" placeholder="Masukkan kata sandi" required>
                        <button type="button" class="toggle-password" onclick="togglePw()"><i class='bx bx-hide' id="pwIcon"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class='bx bx-user'></i> Masuk Sebagai</label>
                    <select name="peran" required>
                        <option value="mahasiswa" <?= ($_POST['peran'] ?? '') === 'mahasiswa' ? 'selected' : '' ?>>👨‍🎓 Mahasiswa</option>
                        <option value="dosen" <?= ($_POST['peran'] ?? '') === 'dosen' ? 'selected' : '' ?>>👨‍🏫 Dosen</option>
                        <option value="admin" <?= ($_POST['peran'] ?? '') === 'admin' ? 'selected' : '' ?>>🔧 Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn-login"><i class='bx bx-log-in'></i> Masuk</button>
            </form>
        </div>
    </div>
    <script>
    function togglePw(){const p=document.getElementById('pw'),i=document.getElementById('pwIcon');if(p.type==='password'){p.type='text';i.classList.replace('bx-hide','bx-show')}else{p.type='password';i.classList.replace('bx-show','bx-hide')}}
    </script>
</body>
</html>
