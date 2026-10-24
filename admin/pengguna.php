<?php
require_once __DIR__ . '/../config.php';
if (!sudahLogin() || $_SESSION['peran'] !== 'admin') { alihkan('../index.php'); }
$user = ambilPengguna();

// Tambah Pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $peran = $_POST['peran'];
    $nim_nip = $_POST['nim_nip'];
    $jurusan = $_POST['jurusan'];
    
    $nim = ($peran === 'mahasiswa') ? $nim_nip : null;
    $nip = ($peran === 'dosen') ? $nim_nip : null;
    
    $stmt = $pdo->prepare("INSERT INTO pengguna (nama, email, kata_sandi, peran, nim, nip, jurusan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nama, $email, $password, $peran, $nim, $nip, $jurusan]);
    alihkan('pengguna.php?pesan=tambah');
}

// Edit Pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $peran = $_POST['peran'];
    $nim_nip = $_POST['nim_nip'];
    $jurusan = $_POST['jurusan'];
    $password = $_POST['password'];
    
    $nim = ($peran === 'mahasiswa') ? $nim_nip : null;
    $nip = ($peran === 'dosen') ? $nim_nip : null;
    
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE pengguna SET nama=?, email=?, peran=?, nim=?, nip=?, jurusan=?, kata_sandi=? WHERE id=?");
        $stmt->execute([$nama, $email, $peran, $nim, $nip, $jurusan, $hashed, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE pengguna SET nama=?, email=?, peran=?, nim=?, nip=?, jurusan=? WHERE id=?");
        $stmt->execute([$nama, $email, $peran, $nim, $nip, $jurusan, $id]);
    }
    alihkan('pengguna.php?pesan=edit');
}

// Hapus Pengguna
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id != $user['id']) {
        $stmt = $pdo->prepare("DELETE FROM pengguna WHERE id = ?");
        $stmt->execute([$id]);
    }
    alihkan('pengguna.php?pesan=hapus');
}

// Ambil Data Edit
$dataEdit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id = ?");
    $stmt->execute([$id]);
    $dataEdit = $stmt->fetch();
}

// Daftar Pengguna
$stmt = $pdo->query("SELECT * FROM pengguna ORDER BY peran, nama");
$daftarPengguna = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - JadwalinKampus</title>
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
        .grid{display:grid;grid-template-columns:1fr 350px;gap:24px}
        .card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden}
        .card-header{padding:18px 20px;border-bottom:1px solid #F1F5F9;font-weight:600}
        .card-body{padding:0}
        table{width:100%;border-collapse:collapse}
        th{background:#F8FAFC;padding:16px 20px;text-align:left;font-size:15px;font-weight:600;color:#64748B;border-bottom:1px solid #E2E8F0}
        td{padding:18px 20px;font-size:16px;border-bottom:1px solid #F1F5F9}
        .badge-role{padding:4px 10px;border-radius:4px;font-size:13px;font-weight:700;text-transform:uppercase}
        .role-mahasiswa{background:#DBEAFE;color:#1E40AF}.role-dosen{background:#F3E8FF;color:#7E22CE}.role-admin{background:#F1F5F9;color:#475569}
        .form-group{margin-bottom:14px;padding:0 20px}
        .form-group label{display:block;font-size:15px;font-weight:600;color:#64748B;margin-bottom:8px}
        .form-control{width:100%;padding:12px 16px;border:1.5px solid #E2E8F0;border-radius:8px;font-size:16px;outline:none}
        .btn{padding:14px 20px;border-radius:10px;font-weight:600;cursor:pointer;border:none;font-size:16px;transition:all .2s}
        .btn-primary{background:#3B82F6;color:#fff;width:calc(100% - 40px);margin:10px 20px 20px}
        .btn-danger-sm{color:#EF4444;font-size:20px;text-decoration:none}
        .btn-edit-sm{color:#3B82F6;font-size:20px;text-decoration:none;margin-right:12px}
        .btn-secondary{background:#94A3B8;color:#fff;width:calc(100% - 40px);margin:0 20px 20px}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="icon"><i class='bx bxs-graduation'></i></div><div><h2>JadwalinKampus</h2><small>Kelola Pengguna Admin</small></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class='bx bxs-dashboard'></i> Beranda</a>
            <a href="pengguna.php" class="active"><i class='bx bxs-user-account'></i> Kelola Pengguna</a>
            <a href="monitoring.php"><i class='bx bx-calendar-event'></i> Monitoring Jadwal</a>
            <a href="profil.php"><i class='bx bxs-user-circle'></i> Profil Admin</a>
        </nav>
        <div class="sidebar-user"><div class="avatar">A</div><div class="info"><div style="font-size:13px;font-weight:600"><?= $user['nama'] ?></div></div></div>
    </aside>

    <div class="main">
        <h1 class="page-title">Kelola Pengguna</h1>
        <p class="page-subtitle">Tambah atau hapus data mahasiswa, dosen, dan admin.</p>

        <div class="grid">
            <div class="card">
                <div class="card-header">Daftar Pengguna</div>
                <div class="card-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Peran</th>
                                <th>NIM/NIP</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($daftarPengguna as $p): ?>
                                <tr>
                                    <td><div style="font-weight:600"><?= htmlspecialchars($p['nama']) ?></div><div style="font-size:11px;color:#64748B"><?= $p['jurusan'] ?></div></td>
                                    <td><?= htmlspecialchars($p['email']) ?></td>
                                    <td><span class="badge-role role-<?= $p['peran'] ?>"><?= $p['peran'] ?></span></td>
                                    <td><?= $p['nim'] ?: ($p['nip'] ?: '-') ?></td>
                                    <td>
                                        <div style="display:flex;align-items:center">
                                            <a href="?edit=<?=$p['id']?>" class="btn-edit-sm" title="Edit"><i class='bx bx-edit-alt'></i></a>
                                            <?php if($p['id'] != $user['id']): ?>
                                                <a href="?hapus=<?=$p['id']?>" class="btn-danger-sm" title="Hapus" onclick="return confirm('Hapus pengguna ini?')"><i class='bx bx-trash'></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <?php if ($dataEdit): ?>
                    <div class="card-header">Edit Pengguna: <?= htmlspecialchars($dataEdit['nama']) ?></div>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $dataEdit['id'] ?>">
                        <div style="padding-top:20px"></div>
                        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($dataEdit['nama']) ?>" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($dataEdit['email']) ?>" required></div>
                        <div class="form-group"><label>Password (Kosongkan jika tidak ganti)</label><input type="password" name="password" class="form-control" placeholder="••••••••"></div>
                        <div class="form-group">
                            <label>Peran</label>
                            <select name="peran" class="form-control" required>
                                <option value="mahasiswa" <?= $dataEdit['peran']=='mahasiswa'?'selected':'' ?>>Mahasiswa</option>
                                <option value="dosen" <?= $dataEdit['peran']=='dosen'?'selected':'' ?>>Dosen</option>
                                <option value="admin" <?= $dataEdit['peran']=='admin'?'selected':'' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="form-group"><label>NIM / NIP (Optional)</label><input type="text" name="nim_nip" class="form-control" value="<?= $dataEdit['nim'] ?: $dataEdit['nip'] ?>"></div>
                        <div class="form-group"><label>Jurusan / Departemen</label><input type="text" name="jurusan" class="form-control" value="<?= htmlspecialchars($dataEdit['jurusan']) ?>"></div>
                        <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="pengguna.php" class="btn btn-secondary" style="display:flex;justify-content:center">Batal</a>
                    </form>
                <?php else: ?>
                    <div class="card-header">Tambah Pengguna Baru</div>
                    <form method="POST">
                        <div style="padding-top:20px"></div>
                        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" class="form-control" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                        <div class="form-group">
                            <label>Peran</label>
                            <select name="peran" class="form-control" required>
                                <option value="mahasiswa">Mahasiswa</option><option value="dosen">Dosen</option><option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group"><label>NIM / NIP (Optional)</label><input type="text" name="nim_nip" class="form-control"></div>
                        <div class="form-group"><label>Jurusan / Departemen</label><input type="text" name="jurusan" class="form-control"></div>
                        <button type="submit" name="tambah" class="btn btn-primary">Tambah Pengguna</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
