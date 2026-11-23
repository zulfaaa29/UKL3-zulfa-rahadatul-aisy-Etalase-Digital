-- Etalase Digital Stella (gabungan)
CREATE DATABASE IF NOT EXISTS db_etalase_stella
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_etalase_stella;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'siswa') DEFAULT 'siswa'
);

CREATE TABLE IF NOT EXISTS student (
    id_siswa INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL UNIQUE,
    nis VARCHAR(30) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    kelas_kejuruan VARCHAR(50) NOT NULL,
    foto_profile VARCHAR(255) NOT NULL,
    CONSTRAINT fk_student_user FOREIGN KEY (id_user)
        REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS karya (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_karya VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    gambar VARCHAR(255) NOT NULL,
    link_karya VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Akun default (password semuanya: password123) -- segera ganti setelah login pertama
INSERT IGNORE INTO users (nama, email, password, role) VALUES
('Super Admin Stella', 'superadmin@stella.sch.id', '$2y$10$7C8r/agxCXnosP8mnSlZbOsWoUaW06UUJtuIFkUHuShVNBJ2pz0S.', 'super_admin'),
('Admin Stella',       'admin@stella.sch.id',      '$2y$10$7C8r/agxCXnosP8mnSlZbOsWoUaW06UUJtuIFkUHuShVNBJ2pz0S.', 'admin'),
('Siswa Contoh',       'siswa@stella.sch.id',      '$2y$10$7C8r/agxCXnosP8mnSlZbOsWoUaW06UUJtuIFkUHuShVNBJ2pz0S.', 'siswa');
 
 INSERT IGNORE INTO student (id_user, nis, nama_lengkap, kelas_kejuruan, foto_profile) 
 SELECT id_user, '0000001', 'Zulfa Rahadatul Aisy', 'RPL 1', NULL
    FROM users
    WHERE username = 'Zulfa Rahadatul AisY' AND role = 'siswa';