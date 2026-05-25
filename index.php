<?php
session_start();
require_once "koneksi.php";

$data_mahasiswa = [];
try {
    $stmt = $pdo->query("SELECT * FROM mahasiswa ORDER BY id DESC");
    $data_mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal mengambil data: " . $e->getMessage();
}

$total_mahasiswa = count($data_mahasiswa);
$unique_jurusan  = [];
$total_aktif     = 0;
foreach ($data_mahasiswa as $mhs) {
    if ($mhs['jurusan'] && !in_array($mhs['jurusan'], $unique_jurusan)) {
        $unique_jurusan[] = $mhs['jurusan'];
    }
    if ($mhs['status'] === 'Aktif') $total_aktif++;
}
$total_jurusan = count($unique_jurusan) ?: 0;

// FIX BUG: Fungsi untuk warna avatar berdasarkan huruf pertama
function getAvatarColor(string $name): string {
    $colors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4','#ec4899','#84cc16'];
    return $colors[ord(strtolower($name[0])) % count($colors)];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduApp - Dashboard</title>
    <meta name="description" content="Sistem pengelolaan data mahasiswa EduApp berbasis Bootstrap 5 dan PHP PDO">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ===== GLOBAL ===== */
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

        /* ===== NAVBAR ===== */
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
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.3px;
        }
        .app-navbar .brand .brand-dot {
            width: 8px; height: 8px;
            background: #93c5fd;
            border-radius: 50%;
            display: inline-block;
        }
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 2px;
            list-style: none;
            margin: 0; padding: 0;
        }
        .navbar-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.83rem;
            padding: 6px 12px;
            border-radius: 5px;
            transition: background 0.15s, color 0.15s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .navbar-links a:hover, .navbar-links a.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .navbar-toggler-custom {
            display: none;
            background: none;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 5px;
            padding: 5px 8px;
            color: #fff;
            cursor: pointer;
        }

        /* ===== LAYOUT ===== */
        .main-wrapper { flex: 1; display: flex; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 230px;
            background-color: #0f172a;
            min-height: calc(100vh - 56px);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .sidebar-section {
            padding: 18px 12px 6px;
        }
        .sidebar-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #475569;
            padding: 0 6px;
            margin-bottom: 4px;
        }
        .sidebar-nav {
            list-style: none;
            padding: 0; margin: 0;
        }
        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.86rem;
            transition: background 0.15s, color 0.15s;
            margin-bottom: 2px;
        }
        .sidebar-nav li a i {
            font-size: 0.95rem;
            width: 16px;
            text-align: center;
        }
        .sidebar-nav li a:hover {
            background: rgba(255,255,255,0.06);
            color: #e2e8f0;
        }
        .sidebar-nav li a.active {
            background: #1d4ed8;
            color: #fff;
            font-weight: 500;
        }
        .sidebar-nav li a .nav-badge {
            margin-left: auto;
            background: #1d4ed8;
            color: #fff;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 20px;
            font-weight: 600;
            line-height: 1.4;
        }
        .sidebar-nav li a.active .nav-badge {
            background: rgba(255,255,255,0.25);
        }
        .sidebar-divider {
            border: none;
            border-top: 1px solid #1e293b;
            margin: 8px 12px;
        }
        .sidebar-bottom {
            margin-top: auto;
            padding: 12px;
            border-top: 1px solid #1e293b;
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 6px;
            background: rgba(255,255,255,0.04);
        }
        .sidebar-user .user-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: #1d4ed8;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .sidebar-user .user-name {
            font-size: 0.82rem;
            color: #cbd5e1;
            font-weight: 500;
            line-height: 1.2;
        }
        .sidebar-user .user-role {
            font-size: 0.7rem;
            color: #64748b;
        }

        /* ===== CONTENT ===== */
        .content { flex: 1; padding: 22px 26px; overflow-x: hidden; }

        /* ===== BREADCRUMB ===== */
        .breadcrumb {
            background: none; padding: 0;
            margin-bottom: 14px;
            font-size: 0.78rem;
        }
        .breadcrumb-item a {
            color: #3b82f6; text-decoration: none;
            transition: color 0.15s;
        }
        .breadcrumb-item a:hover { color: #1d4ed8; }
        .breadcrumb-item.active { color: #64748b; }
        .breadcrumb-item + .breadcrumb-item::before { color: #94a3b8; }

        /* ===== PAGE HEADER ===== */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .page-title {
            font-size: 1.18rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 3px;
        }
        .page-subtitle {
            font-size: 0.82rem;
            color: #64748b;
            margin: 0;
        }

        /* ===== ALERT ===== */
        .alert {
            font-size: 0.86rem;
            border-radius: 7px;
            border-left: 3px solid;
            padding: 11px 15px;
            margin-bottom: 16px;
        }
        .alert-success {
            background: #f0fdf4;
            border-left-color: #22c55e;
            color: #15803d;
        }
        .alert-danger {
            background: #fef2f2;
            border-left-color: #ef4444;
            color: #b91c1c;
        }
        .alert .btn-close { font-size: 0.75rem; }

        /* ===== STAT CARDS ===== */
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px; }
        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: border-color 0.2s, transform 0.2s;
            cursor: default;
        }
        .stat-card:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
        }
        .stat-icon-wrap {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .stat-icon-blue  { background: #eff6ff; color: #3b82f6; }
        .stat-icon-violet { background: #f5f3ff; color: #8b5cf6; }
        .stat-icon-green { background: #f0fdf4; color: #22c55e; }
        .stat-label {
            font-size: 0.73rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #94a3b8;
            margin-bottom: 3px;
        }
        .stat-value {
            font-size: 1.65rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1;
        }

        /* ===== SECTION CARD ===== */
        .section-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 18px;
            overflow: hidden;
        }
        .section-header {
            padding: 12px 18px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
        }
        .section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.88rem;
            color: #0f172a;
        }
        .section-title i { color: #3b82f6; font-size: 0.95rem; }
        .section-body { padding: 20px 18px; }
        .section-body.p-0 { padding: 0; }

        /* ===== FORM ===== */
        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }
        .form-control, .form-select {
            font-size: 0.87rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 11px;
            color: #1e293b;
            transition: border-color 0.15s, box-shadow 0.15s;
            background-color: #fff;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        .form-control::placeholder { color: #9ca3af; font-size: 0.85rem; }
        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        .form-check-label { font-size: 0.85rem; color: #374151; }
        .invalid-feedback { font-size: 0.78rem; }

        /* ===== TOGGLE BUTTON ===== */
        .toggle-btn {
            background: none;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            color: #64748b;
            font-size: 0.8rem;
            padding: 5px 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.15s, color 0.15s;
        }
        .toggle-btn:hover { background: #f8fafc; color: #374151; }

        /* ===== TABLE ===== */
        .table { font-size: 0.86rem; margin-bottom: 0; }
        .table thead th {
            font-size: 0.73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 14px;
            white-space: nowrap;
        }
        .table tbody td {
            padding: 11px 14px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr { transition: background 0.12s; }
        .table tbody tr:hover { background: #f8fafc; }

        /* ===== AVATAR ===== */
        .av {
            width: 32px; height: 32px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }
        .av-initial {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        /* ===== BADGE ===== */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.73rem;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 20px;
        }
        .badge-aktif  { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

        /* ===== ACTIONS ===== */
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 0.78rem;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid;
            transition: all 0.15s;
            cursor: pointer;
        }
        .action-btn:hover { transform: translateY(-1px); }
        .btn-edit {
            color: #b45309;
            border-color: #fcd34d;
            background: #fffbeb;
        }
        .btn-edit:hover { background: #fef3c7; color: #92400e; border-color: #f59e0b; }
        .btn-del {
            color: #b91c1c;
            border-color: #fca5a5;
            background: #fef2f2;
        }
        .btn-del:hover { background: #fee2e2; color: #991b1b; border-color: #f87171; }

        /* ===== SEARCH BAR ===== */
        .search-wrap {
            position: relative;
            width: 200px;
        }
        .search-wrap i {
            position: absolute;
            left: 9px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.85rem;
        }
        .search-wrap input {
            padding-left: 30px;
            font-size: 0.82rem;
            height: 32px;
        }

        /* ===== BUTTONS ===== */
        .btn {
            font-size: 0.84rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: #2563eb; border-color: #2563eb; }
        .btn-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }
        .btn-outline-secondary { border-color: #d1d5db; color: #4b5563; }
        .btn-outline-secondary:hover { background: #f9fafb; }

        /* ===== EMPTY STATE ===== */
        .empty-state { padding: 48px 20px; text-align: center; }
        .empty-icon { font-size: 2.5rem; color: #d1d5db; margin-bottom: 10px; }
        .empty-title { font-weight: 600; color: #374151; margin-bottom: 4px; font-size: 0.92rem; }
        .empty-desc { font-size: 0.82rem; color: #9ca3af; }

        /* ===== FOOTER ===== */
        footer {
            background: #0f172a;
            color: #475569;
            text-align: center;
            padding: 12px;
            font-size: 0.77rem;
            margin-top: auto;
        }

        /* ===== TOAST ===== */
        .toast-container { z-index: 1080; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .main-wrapper { flex-direction: column; }
            .sidebar { width: 100%; min-height: auto; }
            .content { padding: 14px; }
            .stat-grid { grid-template-columns: 1fr; }
            .navbar-links { display: none; }
            .navbar-toggler-custom { display: block; }
        }
    </style>
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <nav class="app-navbar">
        <a href="index.php" class="brand">
            <img src="cat logo for app.jpg" alt="Logo" style="height: 32px; width: 32px; border-radius: 50%; object-fit: cover;">EduApp
        </a>
        <button class="navbar-toggler-custom" id="navToggler">
            <i class="bi bi-list" style="font-size:1.1rem;"></i>
        </button>
        <ul class="navbar-links" id="navLinks">
            <li><a href="index.php" class="active"><i class="bi bi-house-door"></i>Beranda</a></li>
            <li><a href="#tabel-data"><i class="bi bi-table"></i>Data</a></li>
            <li><a href="pengaturan.php"><i class="bi bi-gear"></i>Pengaturan</a></li>
            <li>
                <a href="#" style="background:rgba(239,68,68,0.15); color:#fca5a5; margin-left:4px;">
                    <i class="bi bi-box-arrow-right"></i>Keluar
                </a>
            </li>
        </ul>
    </nav>

    <!-- ===== WRAPPER ===== -->
    <div class="main-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Utama</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="index.php" class="active">
                            <i class="bi bi-speedometer2"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="#tabel-data">
                            <i class="bi bi-people"></i>Data Mahasiswa
                            <?php if ($total_mahasiswa > 0): ?>
                                <span class="nav-badge"><?php echo $total_mahasiswa; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <hr class="sidebar-divider">

            <div class="sidebar-section">
                <div class="sidebar-label">Akademik</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="#">
                            <i class="bi bi-journal-bookmark"></i>Data Jurusan
                        </a>
                    </li>
                </ul>
            </div>

            <hr class="sidebar-divider">

            <div class="sidebar-section">
                <div class="sidebar-label">Sistem</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="pengaturan.php">
                            <i class="bi bi-gear"></i>Pengaturan
                        </a>
                    </li>
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
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>

            <!-- PAGE HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Dashboard EduApp</h1>
                    <p class="page-subtitle">Pengelolaan data mahasiswa &mdash; MODUL PRAKTIKUM V</p>
                </div>
            </div>

            <!-- ALERT -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- STATISTIK -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon-wrap stat-icon-blue">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Mahasiswa</div>
                        <div class="stat-value"><?php echo $total_mahasiswa; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrap stat-icon-violet">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Jurusan</div>
                        <div class="stat-value"><?php echo $total_jurusan; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrap stat-icon-green">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Data Aktif</div>
                        <div class="stat-value"><?php echo $total_aktif; ?></div>
                    </div>
                </div>
            </div>

            <!-- FORM INPUT -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">
                        <i class="bi bi-person-plus-fill"></i>
                        Form Input Data Mahasiswa
                    </div>
                    <button class="toggle-btn" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#formInputCollapse"
                        aria-expanded="true"
                        id="toggleFormBtn">
                        <i class="bi bi-chevron-up" id="toggleIcon"></i>
                        <span id="toggleText">Sembunyikan</span>
                    </button>
                </div>

                <div class="collapse show" id="formInputCollapse">
                    <div class="section-body">
                        <form class="needs-validation" method="POST" action="proses_mahasiswa.php"
                            enctype="multipart/form-data" novalidate id="formMahasiswa">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama" name="nama"
                                        placeholder="Masukkan nama lengkap" required>
                                    <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="contoh@email.com" required>
                                    <div class="invalid-feedback">Format email tidak valid.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="jurusan" class="form-label">Jurusan <span class="text-danger">*</span></label>
                                    <select id="jurusan" name="jurusan" class="form-select" required>
                                        <option value="">-- Pilih Jurusan --</option>
                                        <option value="Informatika">Informatika</option>
                                        <option value="Sistem Informasi">Sistem Informasi</option>
                                        <option value="Manajemen">Manajemen</option>
                                        <option value="Bisnis Digital">Bisnis Digital</option>
                                    </select>
                                    <div class="invalid-feedback">Jurusan wajib dipilih.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3 pt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="laki" value="Laki-laki" required>
                                            <label class="form-check-label" for="laki">Laki-laki</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan" value="Perempuan" required>
                                            <label class="form-check-label" for="perempuan">Perempuan</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block">Minat Mahasiswa</label>
                                    <div class="d-flex flex-wrap gap-3 pt-1" id="minatContainer">
                                        <small class="text-muted">Pilih jurusan terlebih dahulu untuk melihat minat yang tersedia.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="foto" class="form-label">Upload Foto <span class="text-danger">*</span></label>
                                    <input type="file" id="foto" name="foto" class="form-control" accept="image/*" required>
                                    <div class="invalid-feedback">Foto wajib diupload.</div>
                                    <small class="text-muted" style="font-size:0.77rem;">JPG, JPEG, PNG &mdash; maks. 2 MB</small>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary" id="btnSimpan">
                                    <i class="bi bi-save"></i>Simpan Data
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TABEL DATA -->
            <div id="tabel-data" class="section-card">
                <div class="section-header">
                    <div class="section-title">
                        <i class="bi bi-table"></i>
                        Data Mahasiswa
                        <?php if ($total_mahasiswa > 0): ?>
                            <span class="badge bg-primary" style="font-size:0.7rem;"><?php echo $total_mahasiswa; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari...">
                    </div>
                </div>
                <div class="section-body p-0">
                    <div class="table-responsive">
                        <table class="table" id="tableMahasiswa">
                            <thead>
                                <tr>
                                    <th style="width:44px;">No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Jurusan</th>
                                    <th style="width:90px;">Status</th>
                                    <th style="width:110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php if (!empty($data_mahasiswa)) : ?>
                                    <?php $no = 1; foreach ($data_mahasiswa as $mhs): ?>
                                        <?php
                                            $avatarColor = getAvatarColor($mhs['nama']);
                                            // FIX BUG: escape nama untuk JS (apostrophe dll)
                                            $namaJs = addslashes(htmlspecialchars($mhs['nama']));
                                        ?>
                                        <tr>
                                            <td class="text-muted" style="font-size:0.8rem;"><?php echo $no++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if (!empty($mhs['foto']) && file_exists("uploads/" . $mhs['foto'])): ?>
                                                        <img src="uploads/<?php echo htmlspecialchars($mhs['foto']); ?>"
                                                            class="av" alt="Foto">
                                                    <?php else: ?>
                                                        <span class="av-initial"
                                                            style="background:<?php echo $avatarColor; ?>;">
                                                            <?php echo strtoupper(substr($mhs['nama'], 0, 1)); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <!-- FIX SEARCH: data-nama dan data-jurusan untuk filter akurat -->
                                                    <span class="fw-semibold row-nama"><?php echo htmlspecialchars($mhs['nama']); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-muted row-email"><?php echo htmlspecialchars($mhs['email']); ?></td>
                                            <td class="row-jurusan"><?php echo htmlspecialchars($mhs['jurusan']); ?></td>
                                            <td>
                                                <?php if ($mhs['status'] === 'Aktif'): ?>
                                                    <span class="status-badge badge-aktif">
                                                        <span class="badge-dot"></span>Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="status-badge badge-pending">
                                                        <span class="badge-dot"></span><?php echo htmlspecialchars($mhs['status']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="edit_mahasiswa.php?id=<?php echo $mhs['id']; ?>"
                                                        class="action-btn btn-edit">
                                                        <i class="bi bi-pencil"></i>Edit
                                                    </a>
                                                    <a href="hapus_mahasiswa.php?id=<?php echo $mhs['id']; ?>"
                                                        class="action-btn btn-del"
                                                        onclick="return confirmHapus('<?php echo $namaJs; ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div class="empty-icon"><i class="bi bi-people"></i></div>
                                                <div class="empty-title">Belum ada data mahasiswa</div>
                                                <div class="empty-desc">Gunakan form di atas untuk menambahkan data mahasiswa.</div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- FOOTER -->
    <footer>
        &copy; 2024 EduApp &mdash; Web Dinamis dengan PHP Native, PDO, dan Bootstrap
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        'use strict';

        // ===== VALIDASI FORM BOOTSTRAP =====
        (() => {
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // ===== TOGGLE FORM =====
        const collapseEl  = document.getElementById('formInputCollapse');
        const toggleIcon  = document.getElementById('toggleIcon');
        const toggleText  = document.getElementById('toggleText');

        collapseEl.addEventListener('show.bs.collapse', () => {
            toggleIcon.className = 'bi bi-chevron-up';
            toggleText.textContent = 'Sembunyikan';
        });
        collapseEl.addEventListener('hide.bs.collapse', () => {
            toggleIcon.className = 'bi bi-chevron-down';
            toggleText.textContent = 'Tampilkan';
        });

        // ===== FIX SEARCH: hanya cari kolom nama, email, jurusan =====
        document.getElementById('searchInput').addEventListener('input', function () {
            const kw = this.value.toLowerCase().trim();
            document.querySelectorAll('#tableBody tr').forEach(row => {
                const nama    = (row.querySelector('.row-nama')?.textContent || '').toLowerCase();
                const email   = (row.querySelector('.row-email')?.textContent || '').toLowerCase();
                const jurusan = (row.querySelector('.row-jurusan')?.textContent || '').toLowerCase();
                row.style.display = (!kw || nama.includes(kw) || email.includes(kw) || jurusan.includes(kw)) ? '' : 'none';
            });
        });

        // ===== FIX KONFIRMASI HAPUS (safe dari apostrophe) =====
        function confirmHapus(nama) {
            return confirm('Hapus data mahasiswa "' + nama + '"?\nTindakan ini tidak dapat dibatalkan.');
        }

        // ===== FEEDBACK TOMBOL SIMPAN =====
        document.getElementById('formMahasiswa').addEventListener('submit', function () {
            if (this.checkValidity()) {
                const btn = document.getElementById('btnSimpan');
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i>Menyimpan...';
                btn.disabled = true;
            }
        });

        // ===== MINAT DINAMIS BERDASARKAN JURUSAN =====
        const minatData = {
            'Informatika': ['Web Programming', 'Mobile Programming', 'Data Science', 'Cybersecurity', 'Cloud Computing'],
            'Sistem Informasi': ['Web Programming', 'Database Management', 'Business Analysis', 'ERP System', 'IT Project Management'],
            'Manajemen': ['Manajemen Bisnis', 'Keuangan & Akuntansi', 'Pemasaran Digital', 'Manajemen SDM', 'Kewirausahaan'],
            'Bisnis Digital': ['Digital Marketing', 'E-Commerce', 'Social Media Strategy', 'Business Analytics', 'Content Creation']
        };

        const jurusanSelect = document.getElementById('jurusan');
        const minatContainer = document.getElementById('minatContainer');

        function renderMinat(jurusan) {
            minatContainer.innerHTML = '';
            if (jurusan && minatData[jurusan]) {
                minatData[jurusan].forEach((minat, i) => {
                    const id = 'minat_' + i;
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = `
                        <input class="form-check-input" type="checkbox" name="minat[]" id="${id}" value="${minat}">
                        <label class="form-check-label" for="${id}">${minat}</label>
                    `;
                    minatContainer.appendChild(div);
                });
            } else {
                minatContainer.innerHTML = '<small class="text-muted">Pilih jurusan terlebih dahulu untuk melihat minat yang tersedia.</small>';
            }
        }

        jurusanSelect.addEventListener('change', function () {
            renderMinat(this.value);
        });

        // Reset form handling untuk minat dinamis
        document.getElementById('formMahasiswa').addEventListener('reset', function() {
            setTimeout(() => {
                renderMinat('');
            }, 10);
        });
    </script>
</body>
</html>
