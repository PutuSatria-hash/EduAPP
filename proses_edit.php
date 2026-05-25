<?php
session_start();
require_once "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Akses tidak valid.";
    header("Location: index.php");
    exit;
}

$id            = isset($_POST['id']) ? trim($_POST['id']) : '';
$foto_lama     = isset($_POST['foto_lama']) ? trim($_POST['foto_lama']) : '';
$nama          = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$email         = isset($_POST['email']) ? trim($_POST['email']) : '';
$jurusan       = isset($_POST['jurusan']) ? trim($_POST['jurusan']) : '';
$jenis_kelamin = isset($_POST['jenis_kelamin']) ? trim($_POST['jenis_kelamin']) : '';
$minat         = isset($_POST['minat']) ? $_POST['minat'] : [];
$status        = isset($_POST['status']) ? trim($_POST['status']) : 'Aktif';

if (empty($id) || empty($nama) || empty($email) || empty($jurusan) || empty($jenis_kelamin)) {
    $_SESSION['error'] = "Semua data wajib diisi.";
    header("Location: edit_mahasiswa.php?id=" . $id);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Format email tidak valid.";
    header("Location: edit_mahasiswa.php?id=" . $id);
    exit;
}

$nama_foto_simpan = $foto_lama;

// Cek apakah ada foto baru yang diunggah
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $folder_upload = "uploads/";
    $nama_file     = $_FILES['foto']['name'];
    $tmp_file      = $_FILES['foto']['tmp_name'];
    $ukuran_file   = $_FILES['foto']['size'];

    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png'];
    $ekstensi_file      = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

    if (!in_array($ekstensi_file, $ekstensi_diizinkan)) {
        $_SESSION['error'] = "Format foto baru harus JPG, JPEG, atau PNG.";
        header("Location: edit_mahasiswa.php?id=" . $id);
        exit;
    }

    if ($ukuran_file > 2 * 1024 * 1024) {
        $_SESSION['error'] = "Ukuran foto baru maksimal 2 MB.";
        header("Location: edit_mahasiswa.php?id=" . $id);
        exit;
    }

    $nama_file_baru = time() . "_" . uniqid() . "." . $ekstensi_file;
    $lokasi_upload  = $folder_upload . $nama_file_baru;

    if (move_uploaded_file($tmp_file, $lokasi_upload)) {
        // Hapus foto lama dari server
        $path_foto_lama = $folder_upload . $foto_lama;
        if (!empty($foto_lama) && file_exists($path_foto_lama)) {
            unlink($path_foto_lama);
        }
        $nama_foto_simpan = $nama_file_baru;
    } else {
        $_SESSION['error'] = "Gagal mengunggah foto baru.";
        header("Location: edit_mahasiswa.php?id=" . $id);
        exit;
    }
}

$minat_text = !empty($minat) ? implode(", ", $minat) : '-';

try {
    $sql = "UPDATE mahasiswa
            SET nama = :nama, email = :email, jurusan = :jurusan, jenis_kelamin = :jenis_kelamin,
                minat = :minat, foto = :foto, status = :status
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nama'          => $nama,
        ':email'         => $email,
        ':jurusan'       => $jurusan,
        ':jenis_kelamin' => $jenis_kelamin,
        ':minat'         => $minat_text,
        ':foto'          => $nama_foto_simpan,
        ':status'        => $status,
        ':id'            => $id
    ]);

    $_SESSION['success'] = "Data mahasiswa berhasil diperbarui.";
    header("Location: index.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal memperbarui data: " . $e->getMessage();
    header("Location: edit_mahasiswa.php?id=" . $id);
    exit;
}
?>
