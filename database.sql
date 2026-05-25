-- Pembuatan database db_eduapp
CREATE DATABASE IF NOT EXISTS db_eduapp;
USE db_eduapp;

-- Pembuatan tabel mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    jenis_kelamin VARCHAR(20) NOT NULL,
    minat TEXT DEFAULT NULL,
    foto VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
