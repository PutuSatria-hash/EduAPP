<?php
session_start();
require_once "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Akses tidak valid.";
    header("Location: index.php");
    exit;
}

$nama         = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$email        = isset($_POST['email']) ? trim($_POST['email']) : '';
$jurusan      = isset($_POST['jurusan']) ? trim($_POST['jurusan']) : '';
$jenis_kelamin = isset($_POST['jenis_kelamin']) ? trim($_POST['jenis_kelamin']) : '';
$minat        = isset($_POST['minat']) ? $_POST['minat'] : [];

// Validasi data wajib diisi
if (empty($nama) || empty($email) || empty($jurusan) || empty($jenis_kelamin)) {
    $_SESSION['error'] = "Semua data wajib diisi.";
    header("Location: index.php");
    exit;
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Format email tidak valid.";
    header("Location: index.php");
    exit;
}

// Validasi upload foto
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== 0) {
    $_SESSION['error'] = "Foto wajib diupload.";
    header("Location: index.php");
    exit;
}

$folder_upload = "uploads/";
if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0777, true);
}

$nama_file   = $_FILES['foto']['name'];
$tmp_file    = $_FILES['foto']['tmp_name'];
$ukuran_file = $_FILES['foto']['size'];

$ekstensi_diizinkan = ['jpg', 'jpeg', 'png'];
$ekstensi_file      = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

// Periksa ekstensi file
if (!in_array($ekstensi_file, $ekstensi_diizinkan)) {
    $_SESSION['error'] = "Format foto harus JPG, JPEG, atau PNG.";
    header("Location: index.php");
    exit;
}

// Periksa ukuran file (maksimal 2 MB)
if ($ukuran_file > 2 * 1024 * 1024) {
    $_SESSION['error'] = "Ukuran foto maksimal 2 MB.";
    header("Location: index.php");
    exit;
}

// Buat nama file baru agar unik
$nama_file_baru = time() . "_" . uniqid() . "." . $ekstensi_file;
$lokasi_upload  = $folder_upload . $nama_file_baru;

if (!move_uploaded_file($tmp_file, $lokasi_upload)) {
    $_SESSION['error'] = "Foto gagal diupload.";
    header("Location: index.php");
    exit;
}

// Konversi minat dari array ke string
$minat_text = !empty($minat) ? implode(", ", $minat) : '-';

// Simpan data ke database menggunakan PDO prepared statement
try {
    $sql = "INSERT INTO mahasiswa (nama, email, jurusan, jenis_kelamin, minat, foto, status)
            VALUES (:nama, :email, :jurusan, :jenis_kelamin, :minat, :foto, 'Aktif')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nama'          => $nama,
        ':email'         => $email,
        ':jurusan'       => $jurusan,
        ':jenis_kelamin' => $jenis_kelamin,
        ':minat'         => $minat_text,
        ':foto'          => $nama_file_baru
    ]);

    $_SESSION['success'] = "Data mahasiswa " . htmlspecialchars($nama) . " berhasil disimpan.";
    header("Location: index.php");
    exit;
} catch (PDOException $e) {
    // Hapus foto yang sudah diupload jika query gagal
    if (file_exists($lokasi_upload)) {
        unlink($lokasi_upload);
    }
    $_SESSION['error'] = "Gagal menyimpan data: " . $e->getMessage();
    header("Location: index.php");
    exit;
}
?>
