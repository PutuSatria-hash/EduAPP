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
    // Ambil info foto terlebih dahulu untuk dihapus dari server
    $stmt_select = $pdo->prepare("SELECT foto FROM mahasiswa WHERE id = :id");
    $stmt_select->execute([':id' => $id]);
    $mhs = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if ($mhs) {
        // Hapus file foto dari folder uploads/
        $path_foto = "uploads/" . $mhs['foto'];
        if (!empty($mhs['foto']) && file_exists($path_foto)) {
            unlink($path_foto);
        }

        // Hapus data mahasiswa dari database
        $stmt_delete = $pdo->prepare("DELETE FROM mahasiswa WHERE id = :id");
        $stmt_delete->execute([':id' => $id]);

        $_SESSION['success'] = "Data mahasiswa berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Data mahasiswa tidak ditemukan.";
    }

    header("Location: index.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal menghapus data: " . $e->getMessage();
    header("Location: index.php");
    exit;
}
?>
