<?php
session_start();

// Proses simpan pengaturan (simulasi - tidak ada backend)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Di sini bisa ditambahkan logika simpan ke database/file config
    $_SESSION['success'] = "Pengaturan berhasil disimpan.";
    header("Location: pengaturan.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduApp - Pengaturan</title>
    <meta name="description" content="Halaman pengaturan aplikasi EduApp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 0.9rem;
            color: #1e293b;
        }

        /* NAVBAR */
        .app-navbar {
            background: #1d4ed8;
            padding: 0 20px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .app-navbar .brand {
            font-size: 1rem; font-weight: 700; color: #fff;
            text-decoration: none; display: flex; align-items: center; gap: 8px;
        }
        .brand-dot { width: 8px; height: 8px; background: #93c5fd; border-radius: 50%; display: inline-block; }
        .navbar-links { display: flex; align-items: center; gap: 2px; list-style: none; margin: 0; padding: 0; }
        .navbar-links a {
            color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.83rem;
            padding: 6px 12px; border-radius: 5px; transition: background 0.15s;
            display: flex; align-items: center; gap: 5px;
        }
        .navbar-links a:hover, .navbar-links a.active { background: rgba(255,255,255,0.15); color: #fff; }

        /* LAYOUT */
        .main-wrapper { flex: 1; display: flex; }

        /* SIDEBAR */
        .sidebar {
            width: 230px; background-color: #0f172a;
            min-height: calc(100vh - 56px);
            display: flex; flex-direction: column; flex-shrink: 0;
        }
        .sidebar-section { padding: 18px 12px 6px; }
        .sidebar-label {
            font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1.2px; color: #475569; padding: 0 6px; margin-bottom: 4px;
        }
        .sidebar-nav { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav li a {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 10px; color: #94a3b8; text-decoration: none;
            border-radius: 6px; font-size: 0.86rem;
            transition: background 0.15s, color 0.15s; margin-bottom: 2px;
        }
        .sidebar-nav li a i { font-size: 0.95rem; width: 16px; text-align: center; }
        .sidebar-nav li a:hover { background: rgba(255,255,255,0.06); color: #e2e8f0; }
        .sidebar-nav li a.active { background: #1d4ed8; color: #fff; font-weight: 500; }
        .sidebar-divider { border: none; border-top: 1px solid #1e293b; margin: 8px 12px; }
        .sidebar-bottom { margin-top: auto; padding: 12px; border-top: 1px solid #1e293b; }
        .sidebar-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 6px; background: rgba(255,255,255,0.04); }
        .sidebar-user .user-avatar { width: 30px; height: 30px; border-radius: 50%; background: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: #fff; flex-shrink: 0; }
        .sidebar-user .user-name { font-size: 0.82rem; color: #cbd5e1; font-weight: 500; }
        .sidebar-user .user-role { font-size: 0.7rem; color: #64748b; }

        /* CONTENT */
        .content { flex: 1; padding: 22px 26px; overflow-x: hidden; }

        /* BREADCRUMB */
        .breadcrumb { background: none; padding: 0; margin-bottom: 14px; font-size: 0.78rem; }
        .breadcrumb-item a { color: #3b82f6; text-decoration: none; }
        .breadcrumb-item a:hover { color: #1d4ed8; }
        .breadcrumb-item.active { color: #64748b; }
        .breadcrumb-item + .breadcrumb-item::before { color: #94a3b8; }

        /* PAGE HEADER */
        .page-title { font-size: 1.18rem; font-weight: 700; color: #0f172a; margin: 0 0 3px; }
        .page-subtitle { font-size: 0.82rem; color: #64748b; margin: 0 0 20px; }

        /* ALERT */
        .alert { font-size: 0.86rem; border-radius: 7px; border-left: 3px solid; padding: 11px 15px; margin-bottom: 16px; }
        .alert-success { background: #f0fdf4; border-left-color: #22c55e; color: #15803d; }

        /* SECTION CARD */
        .section-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 16px; overflow: hidden; }
        .section-header { padding: 12px 18px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.88rem; color: #0f172a; }
        .section-header i { color: #3b82f6; }
        .section-body { padding: 20px 18px; }

        /* FORM */
        .form-label { font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
        .form-text { font-size: 0.77rem; color: #9ca3af; }
        .form-control, .form-select {
            font-size: 0.87rem; border: 1px solid #d1d5db; border-radius: 6px;
            padding: 8px 11px; color: #1e293b; transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); outline: none;
        }
        .form-check-input:checked { background-color: #3b82f6; border-color: #3b82f6; }
        .form-check-label { font-size: 0.85rem; }

        /* SETTING ROW */
        .setting-row {
            display: flex; align-items: flex-start; justify-content: space-between;
            padding: 14px 0; border-bottom: 1px solid #f1f5f9;
        }
        .setting-row:last-child { border-bottom: none; padding-bottom: 0; }
        .setting-row:first-child { padding-top: 0; }
        .setting-info { flex: 1; margin-right: 24px; }
        .setting-label { font-size: 0.88rem; font-weight: 600; color: #1e293b; margin-bottom: 2px; }
        .setting-desc { font-size: 0.79rem; color: #94a3b8; }
        .setting-control { flex-shrink: 0; min-width: 160px; }

        /* BUTTON */
        .btn { font-size: 0.84rem; border-radius: 6px; font-weight: 500; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px; }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: #2563eb; border-color: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }
        .btn-outline-secondary { border-color: #d1d5db; color: #4b5563; background: #fff; }
        .btn-outline-secondary:hover { background: #f9fafb; }
        .btn-outline-danger { border-color: #fca5a5; color: #b91c1c; background: #fef2f2; }
        .btn-outline-danger:hover { background: #fee2e2; }

        /* FOOTER */
        footer { background: #0f172a; color: #475569; text-align: center; padding: 12px; font-size: 0.77rem; margin-top: auto; }

        @media (max-width: 768px) {
            .main-wrapper { flex-direction: column; }
            .sidebar { width: 100%; min-height: auto; }
            .content { padding: 14px; }
            .setting-row { flex-direction: column; gap: 10px; }
            .setting-control { min-width: auto; width: 100%; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="app-navbar">
        <a href="index.php" class="brand">
            <img src="cat logo for app.jpg" alt="Logo" style="height: 32px; width: 32px; border-radius: 50%; object-fit: cover;">EduApp
        </a>
        <ul class="navbar-links">
            <li><a href="index.php"><i class="bi bi-house-door"></i>Beranda</a></li>
            <li><a href="index.php#tabel-data"><i class="bi bi-table"></i>Data</a></li>
            <li><a href="pengaturan.php" class="active"><i class="bi bi-gear"></i>Pengaturan</a></li>
            <li><a href="#" style="background:rgba(239,68,68,0.15); color:#fca5a5; margin-left:4px;"><i class="bi bi-box-arrow-right"></i>Keluar</a></li>
        </ul>
    </nav>

    <div class="main-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Utama</div>
                <ul class="sidebar-nav">
                    <li><a href="index.php"><i class="bi bi-speedometer2"></i>Dashboard</a></li>
                    <li><a href="index.php#tabel-data"><i class="bi bi-people"></i>Data Mahasiswa</a></li>
                </ul>
            </div>
            <hr class="sidebar-divider">
            <div class="sidebar-section">
                <div class="sidebar-label">Akademik</div>
                <ul class="sidebar-nav">
                    <li><a href="#"><i class="bi bi-journal-bookmark"></i>Data Jurusan</a></li>
                </ul>
            </div>
            <hr class="sidebar-divider">
            <div class="sidebar-section">
                <div class="sidebar-label">Sistem</div>
                <ul class="sidebar-nav">
                    <li><a href="pengaturan.php" class="active"><i class="bi bi-gear"></i>Pengaturan</a></li>
                </ul>
            </div>
            <div class="sidebar-bottom">
                <div class="sidebar-user">
                    <div class="user-avatar">A</div>
                    <div>
                        <div class="user-name">Admin</div>
                        <div class="user-role">Administrator</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- CONTENT -->
        <main class="content">

            <!-- BREADCRUMB -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house me-1"></i>Beranda</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
                </ol>
            </nav>

            <h1 class="page-title">Pengaturan</h1>
            <p class="page-subtitle">Kelola konfigurasi aplikasi EduApp.</p>

            <!-- ALERT -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST" action="pengaturan.php">

                <!-- PENGATURAN APLIKASI -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="bi bi-app"></i>Pengaturan Aplikasi
                    </div>
                    <div class="section-body">
                        <div class="setting-row">
                            <div class="setting-info">
                                <div class="setting-label">Nama Aplikasi</div>
                                <div class="setting-desc">Nama yang ditampilkan di navbar dan judul browser.</div>
                            </div>
                            <div class="setting-control">
                                <input type="text" class="form-control" name="app_name" value="EduApp">
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <div class="setting-label">Tahun Copyright</div>
                                <div class="setting-desc">Tahun yang ditampilkan di footer halaman.</div>
                            </div>
                            <div class="setting-control">
                                <input type="number" class="form-control" name="year" value="2024" min="2000" max="2099">
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <div class="setting-label">Jumlah Data Per Halaman</div>
                                <div class="setting-desc">Batas maksimal data yang ditampilkan di tabel.</div>
                            </div>
                            <div class="setting-control">
                                <select class="form-select" name="per_page">
                                    <option value="10">10 data</option>
                                    <option value="25" selected>25 data</option>
                                    <option value="50">50 data</option>
                                    <option value="100">100 data</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PENGATURAN UPLOAD -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="bi bi-cloud-upload"></i>Pengaturan Upload Foto
                    </div>
                    <div class="section-body">
                        <div class="setting-row">
                            <div class="setting-info">
                                <div class="setting-label">Ukuran Maksimal File</div>
                                <div class="setting-desc">Batas ukuran file foto yang dapat diunggah.</div>
                            </div>
                            <div class="setting-control">
                                <select class="form-select" name="max_size">
                                    <option value="1">1 MB</option>
                                    <option value="2" selected>2 MB</option>
                                    <option value="5">5 MB</option>
                                </select>
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <div class="setting-label">Format File Diizinkan</div>
                                <div class="setting-desc">Tipe file gambar yang diperbolehkan untuk diunggah.</div>
                            </div>
                            <div class="setting-control">
                                <div class="d-flex flex-column gap-2 pt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="chk_jpg" name="format[]" value="jpg" checked>
                                        <label class="form-check-label" for="chk_jpg">JPG / JPEG</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="chk_png" name="format[]" value="png" checked>
                                        <label class="form-check-label" for="chk_png">PNG</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="chk_webp" name="format[]" value="webp">
                                        <label class="form-check-label" for="chk_webp">WebP</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PENGATURAN DATABASE -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="bi bi-database"></i>Informasi Database
                    </div>
                    <div class="section-body">
                        <div class="setting-row">
                            <div class="setting-info">
                                <div class="setting-label">Host Database</div>
                                <div class="setting-desc">Server database yang digunakan.</div>
                            </div>
                            <div class="setting-control">
                                <input type="text" class="form-control" value="localhost" readonly style="background:#f8fafc; color:#94a3b8;">
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <div class="setting-label">Nama Database</div>
                                <div class="setting-desc">Nama database yang aktif digunakan.</div>
                            </div>
                            <div class="setting-control">
                                <input type="text" class="form-control" value="db_eduapp" readonly style="background:#f8fafc; color:#94a3b8;">
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <div class="setting-label">Username Database</div>
                                <div class="setting-desc">Akun yang digunakan untuk koneksi database.</div>
                            </div>
                            <div class="setting-control">
                                <input type="text" class="form-control" value="root" readonly style="background:#f8fafc; color:#94a3b8;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOMBOL AKSI -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i>Simpan Pengaturan
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>Kembali
                    </a>
                </div>

            </form>

        </main>
    </div>

    <!-- FOOTER -->
    <footer>
        &copy; 2024 EduApp &mdash; Web Dinamis dengan PHP Native, PDO, dan Bootstrap
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        'use strict';
        // Feedback tombol saat simpan
        document.querySelector('form').addEventListener('submit', function () {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i>Menyimpan...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
