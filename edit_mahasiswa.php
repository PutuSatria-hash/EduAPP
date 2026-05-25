<?php
session_start();
require_once "koneksi.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID mahasiswa tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $mhs = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mhs) {
        $_SESSION['error'] = "Data mahasiswa tidak ditemukan.";
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal memuat data: " . $e->getMessage();
    header("Location: index.php");
    exit;
}

$minat_array = explode(", ", $mhs['minat']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduApp - Edit Mahasiswa</title>
    <meta name="description" content="Edit data mahasiswa pada sistem EduApp">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 0.92rem;
        }
        .navbar { border-bottom: 1px solid rgba(255,255,255,0.1); }
        .navbar-brand { font-size: 1.1rem; letter-spacing: 0.5px; }
        .main-wrapper { flex: 1; display: flex; }

        /* SIDEBAR */
        .sidebar {
            width: 240px;
            min-height: calc(100vh - 56px);
            background-color: #1e2330;
            flex-shrink: 0;
        }
        .sidebar-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6c757d;
            padding: 20px 16px 8px;
            font-weight: 600;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 9px 16px;
            border-radius: 6px;
            margin: 2px 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.88rem;
            transition: background 0.2s, color 0.2s;
        }
        .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.07); color: #fff; }
        .sidebar .nav-link.active { background-color: #0d6efd; color: #fff; }
        .sidebar .nav-link i { font-size: 1rem; width: 18px; }

        /* CONTENT */
        .content { flex: 1; padding: 24px 28px; overflow-x: hidden; }

        /* BREADCRUMB */
        .breadcrumb {
            background: none;
            padding: 0;
            margin-bottom: 4px;
            font-size: 0.8rem;
        }
        .breadcrumb-item a {
            color: #0d6efd;
            text-decoration: none;
        }
        .breadcrumb-item a:hover { text-decoration: underline; }
        .breadcrumb-item.active { color: #6c757d; }
        .breadcrumb-item + .breadcrumb-item::before { color: #adb5bd; }

        /* PAGE HEADER */
        .page-header {
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #dee2e6;
        }
        .page-header h4 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #212529;
            margin: 0;
        }

        /* SECTION CARD */
        .section-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .section-card .section-header {
            padding: 12px 18px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #212529;
        }
        .section-card .section-body { padding: 20px 18px; }

        /* FORM */
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 4px;
        }
        .form-control, .form-select {
            font-size: 0.88rem;
            border-color: #dee2e6;
            border-radius: 6px;
            padding: 8px 12px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
        }
        .form-check-label { font-size: 0.87rem; }

        /* FOTO PREVIEW */
        .foto-preview {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
            transition: border-color 0.2s;
        }
        .foto-preview:hover { border-color: #0d6efd; }
        .avatar-fallback-lg {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #6c757d;
            font-weight: 700;
            border: 2px solid #dee2e6;
        }

        /* ALERT */
        .alert {
            font-size: 0.88rem;
            border-radius: 7px;
            border: none;
            padding: 12px 16px;
        }

        /* BUTTON */
        .btn {
            font-size: 0.84rem;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .btn:hover { transform: translateY(-1px); }

        /* FOOTER */
        footer {
            background-color: #1e2330;
            color: #6c757d;
            text-align: center;
            padding: 14px;
            font-size: 0.8rem;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .main-wrapper { flex-direction: column; }
            .sidebar { width: 100%; min-height: auto; }
            .content { padding: 16px; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
                <img src="cat logo for app.jpg" alt="Logo" style="height: 30px; width: 30px; border-radius: 50%; object-fit: cover;">EduApp
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i>Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#tabel-data"><i class="bi bi-table me-1"></i>Data</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-person-circle me-1"></i>Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-box-arrow-right me-1"></i>Keluar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- WRAPPER SIDEBAR + CONTENT -->
    <div class="main-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar p-0">
            <div class="sidebar-title">Navigasi</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-speedometer2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php#tabel-data">
                        <i class="bi bi-people"></i>Data Mahasiswa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-journal-bookmark"></i>Data Jurusan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-gear"></i>Pengaturan
                    </a>
                </li>
            </ul>
        </aside>

        <!-- CONTENT AREA -->
        <main class="content">

            <!-- BREADCRUMB -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.php"><i class="bi bi-house me-1"></i>Beranda</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="index.php#tabel-data">Data Mahasiswa</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Edit &mdash; <?php echo htmlspecialchars($mhs['nama']); ?>
                    </li>
                </ol>
            </nav>

            <!-- PAGE HEADER -->
            <div class="page-header">
                <h4><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Data Mahasiswa</h4>
                <p class="text-muted mb-0 mt-1" style="font-size:0.85rem;">
                    Perbarui data untuk <strong><?php echo htmlspecialchars($mhs['nama']); ?></strong>
                </p>
            </div>

            <!-- ALERT -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- FORM EDIT -->
            <div class="section-card">
                <div class="section-header">
                    <i class="bi bi-pencil text-warning"></i>
                    Form Edit Mahasiswa
                </div>
                <div class="section-body">
                    <form class="needs-validation" method="POST" action="proses_edit.php" enctype="multipart/form-data" novalidate id="formEdit">

                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($mhs['id']); ?>">
                        <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($mhs['foto']); ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama"
                                    value="<?php echo htmlspecialchars($mhs['nama']); ?>" required>
                                <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($mhs['email']); ?>" required>
                                <div class="invalid-feedback">Email wajib diisi dengan format yang benar.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="jurusan" class="form-label">Jurusan <span class="text-danger">*</span></label>
                                <select id="jurusan" name="jurusan" class="form-select" required>
                                    <option value="">-- Pilih Jurusan --</option>
                                    <option value="Informatika" <?php echo $mhs['jurusan'] === 'Informatika' ? 'selected' : ''; ?>>Informatika</option>
                                    <option value="Sistem Informasi" <?php echo $mhs['jurusan'] === 'Sistem Informasi' ? 'selected' : ''; ?>>Sistem Informasi</option>
                                    <option value="Manajemen" <?php echo $mhs['jurusan'] === 'Manajemen' ? 'selected' : ''; ?>>Manajemen</option>
                                    <option value="Bisnis Digital" <?php echo $mhs['jurusan'] === 'Bisnis Digital' ? 'selected' : ''; ?>>Bisnis Digital</option>
                                </select>
                                <div class="invalid-feedback">Jurusan wajib dipilih.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status Keaktifan <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-select" required>
                                    <option value="Aktif" <?php echo $mhs['status'] === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Pending" <?php echo $mhs['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">Jenis Kelamin <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jenis_kelamin" id="laki" value="Laki-laki"
                                            <?php echo $mhs['jenis_kelamin'] === 'Laki-laki' ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="laki">Laki-laki</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan" value="Perempuan"
                                            <?php echo $mhs['jenis_kelamin'] === 'Perempuan' ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="perempuan">Perempuan</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">Minat Mahasiswa</label>
                                <div class="d-flex flex-wrap gap-3 mt-1" id="minatContainer">
                                    <small class="text-muted">Pilih jurusan terlebih dahulu untuk melihat minat yang tersedia.</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="foto" class="form-label">Ganti Foto (Opsional)</label>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <?php if (!empty($mhs['foto']) && file_exists("uploads/" . $mhs['foto'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($mhs['foto']); ?>"
                                            class="foto-preview" id="fotoPreview" alt="Foto saat ini">
                                    <?php else: ?>
                                        <span class="avatar-fallback-lg" id="fotoFallback">
                                            <?php echo strtoupper(substr($mhs['nama'], 0, 1)); ?>
                                        </span>
                                    <?php endif; ?>
                                    <div>
                                        <input type="file" id="foto" name="foto" class="form-control" accept="image/*" style="width:260px;">
                                        <small class="text-muted d-block mt-1">Format: JPG, JPEG, PNG. Maks. 2 MB. Biarkan kosong jika tidak ingin mengganti.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-warning text-dark fw-semibold" id="btnUpdate">
                                <i class="bi bi-check-circle me-1"></i>Update Data
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>

    <!-- FOOTER -->
    <footer>
        &copy; 2024 EduApp. Semua Hak Dilindungi. &mdash; MODUL PRAKTIKUM V
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ===== VALIDASI FORM BOOTSTRAP =====
        (() => {
            'use strict';
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

        // ===== PREVIEW FOTO SEBELUM UPLOAD =====
        document.getElementById('foto').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                let preview = document.getElementById('fotoPreview');
                let fallback = document.getElementById('fotoFallback');

                if (!preview) {
                    // Jika belum ada tag img, buat baru
                    preview = document.createElement('img');
                    preview.id = 'fotoPreview';
                    preview.className = 'foto-preview';
                    preview.alt = 'Preview foto';
                    if (fallback) {
                        fallback.replaceWith(preview);
                    }
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });

        // ===== FEEDBACK TOMBOL UPDATE =====
        document.getElementById('formEdit').addEventListener('submit', function (e) {
            if (this.checkValidity()) {
                const btn = document.getElementById('btnUpdate');
                btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Menyimpan...';
                btn.disabled = true;
            }
        });

        // ===== MINAT DINAMIS BERDASARKAN JURUSAN & PRE-FILL =====
        const minatData = {
            'Informatika': ['Web Programming', 'Mobile Programming', 'Data Science', 'Cybersecurity', 'Cloud Computing'],
            'Sistem Informasi': ['Web Programming', 'Database Management', 'Business Analysis', 'ERP System', 'IT Project Management'],
            'Manajemen': ['Manajemen Bisnis', 'Keuangan & Akuntansi', 'Pemasaran Digital', 'Manajemen SDM', 'Kewirausahaan'],
            'Bisnis Digital': ['Digital Marketing', 'E-Commerce', 'Social Media Strategy', 'Business Analytics', 'Content Creation']
        };

        // Minat yang terpilih dari database
        const savedMinat = <?php echo json_encode($minat_array); ?>;
        const jurusanSelect = document.getElementById('jurusan');
        const minatContainer = document.getElementById('minatContainer');

        function renderMinat(jurusan, preFilledValues = []) {
            minatContainer.innerHTML = '';
            if (jurusan && minatData[jurusan]) {
                minatData[jurusan].forEach((minat, i) => {
                    const id = 'minat_' + i;
                    const isChecked = preFilledValues.includes(minat) ? 'checked' : '';
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = `
                        <input class="form-check-input" type="checkbox" name="minat[]" id="${id}" value="${minat}" ${isChecked}>
                        <label class="form-check-label" for="${id}">${minat}</label>
                    `;
                    minatContainer.appendChild(div);
                });
            } else {
                minatContainer.innerHTML = '<small class="text-muted">Pilih jurusan terlebih dahulu untuk melihat minat yang tersedia.</small>';
            }
        }

        // Inisialisasi saat pertama kali dimuat
        renderMinat(jurusanSelect.value, savedMinat);

        // Event listener saat jurusan diubah oleh user
        jurusanSelect.addEventListener('change', function () {
            // Ketika diubah manual, biarkan kosong tanpa prefilled values kecuali jika kembali ke jurusan asal
            if (this.value === '<?php echo $mhs['jurusan']; ?>') {
                renderMinat(this.value, savedMinat);
            } else {
                renderMinat(this.value, []);
            }
        });
    </script>
</body>
</html>
