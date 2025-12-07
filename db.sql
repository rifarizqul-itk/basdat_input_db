CREATE DATABASE db_lifting;
USE db_lifting;

-- ==========================================
-- BAGIAN 1: PEMBUATAN STRUKTUR TABEL (DDL)
-- ==========================================

-- 1. Tabel Karyawan
CREATE TABLE karyawan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Di real app ini harus hash
    peran VARCHAR(50) NOT NULL -- 'Admin', 'Kasir', 'Gudang'
);

-- 2. Tabel Pelanggan
CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(20),
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel Barang
CREATE TABLE barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    satuan VARCHAR(20),
    harga DECIMAL(15, 2) NOT NULL,
    stok_tersedia INT DEFAULT 0
);

-- 4. Tabel Metode Pembayaran
CREATE TABLE metode_pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_metode VARCHAR(50) NOT NULL,
    nomor_rekening VARCHAR(50)
);

-- 5. Tabel Transaksi (Header)
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelanggan_id INT,
    karyawan_id INT,
    tanggal DATE NOT NULL,
    total_tagihan DECIMAL(15, 2) DEFAULT 0,
    total_dibayar DECIMAL(15, 2) DEFAULT 0,
    sisa_tagihan DECIMAL(15, 2) DEFAULT 0,
    status_pembayaran VARCHAR(20), -- 'Lunas', 'Belum Lunas'
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
    FOREIGN KEY (karyawan_id) REFERENCES karyawan(id)
);

-- 6. Tabel Detail Transaksi (Rincian Barang)
CREATE TABLE detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT,
    barang_id INT,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(15, 2) NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id),
    FOREIGN KEY (barang_id) REFERENCES barang(id)
);

-- 7. Tabel Riwayat Pembayaran (Log Keuangan)
CREATE TABLE riwayat_pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT,
    karyawan_id INT,
    metode_pembayaran_id INT,
    tanggal_bayar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    jumlah_bayar DECIMAL(15, 2) NOT NULL,
    keterangan VARCHAR(255),
    bukti_transfer VARCHAR(255),
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id),
    FOREIGN KEY (karyawan_id) REFERENCES karyawan(id),
    FOREIGN KEY (metode_pembayaran_id) REFERENCES metode_pembayaran(id)
);

-- JALANKAN INI DI KOMPUTER TEMAN (SEBELUM INPUT)
-- Tujuannya agar ID start dari angka 1000, jadi aman dari konflik.

ALTER TABLE karyawan AUTO_INCREMENT = 1000;
ALTER TABLE pelanggan AUTO_INCREMENT = 1000;
ALTER TABLE barang AUTO_INCREMENT = 1000;
ALTER TABLE transaksi AUTO_INCREMENT = 1000;
ALTER TABLE detail_transaksi AUTO_INCREMENT = 1000;
ALTER TABLE riwayat_pembayaran AUTO_INCREMENT = 1000;