-- Database schema untuk Sistem POS pakaian
CREATE DATABASE IF NOT EXISTS pos_pakaian;
USE pos_pakaian;

-- Tabel Users (Admin, Kasir, Gudang)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'kasir', 'gudang') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Kategori pakaian
CREATE TABLE kategori_pakaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel pakaian
CREATE TABLE pakaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pakaian VARCHAR(20) UNIQUE NOT NULL,
    nama_pakaian VARCHAR(200) NOT NULL,
    kategori_id INT,
    ukuran ENUM('S', 'M', 'L', 'XL', 'XXL') DEFAULT 'M',
    warna VARCHAR(50),
    satuan VARCHAR(50) NOT NULL,
    harga_modal DECIMAL(10,2) NOT NULL,
    harga_jual DECIMAL(10,2) NOT NULL,
    stok INT DEFAULT 0,
    stok_minimal INT DEFAULT 0,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_pakaian(id) ON DELETE SET NULL
);

-- Tabel vendor
CREATE TABLE vendor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_vendor VARCHAR(200) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel customer
CREATE TABLE customer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_customer VARCHAR(200) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Pembelian (Barang masuk dari Vendor)
CREATE TABLE pembelian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT,
    user_id INT,
    total_harga DECIMAL(12,2) NOT NULL,
    tanggal_pembelian DATE NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendor(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel Detail Pembelian
CREATE TABLE detail_pembelian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pembelian_id INT,
    pakaian_id INT,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (pembelian_id) REFERENCES pembelian(id) ON DELETE CASCADE,
    FOREIGN KEY (pakaian_id) REFERENCES pakaian(id) ON DELETE CASCADE
);

-- Tabel Penjualan (Kasir)
CREATE TABLE penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(50) UNIQUE NOT NULL,
    user_id INT,
    customer_id INT,
    diskon DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_harga DECIMAL(12,2) NOT NULL,
    total_bayar DECIMAL(12,2) NOT NULL,
    kembalian DECIMAL(12,2) NOT NULL,
    note TEXT,
    tanggal_penjualan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE SET NULL
);

-- Tabel Detail Penjualan
CREATE TABLE detail_penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penjualan_id INT,
    pakaian_id INT,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (penjualan_id) REFERENCES penjualan(id) ON DELETE CASCADE,
    FOREIGN KEY (pakaian_id) REFERENCES pakaian(id) ON DELETE CASCADE
);

-- Insert data awal
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@tokopakaian.com', 'admin'),
('kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Utama', 'kasir@tokopakaian.com', 'kasir'),
('gudang1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Gudang', 'gudang@tokopakaian.com', 'gudang');

INSERT INTO kategori_pakaian (nama_kategori, deskripsi) VALUES
('kaos', 'berbagai jenis kaos untuk pria dan wanita'),
('kemeja', 'kemeja formal dan casual'),
('celana', 'celana jeans,chino,training ,dan lainnya'),
('jaket', 'jaket denim,hoodie,bomber,dan lainnya'),


INSERT INTO vendor (nama_vendor, alamat, telepon, email) VALUES
('PT. Fashion Indo', 'Jl. Raya Jakarta No. 123', '021-1234567', 'info@fashionindo.com'),
('CV. Medika Jaya', 'Jl. Sudirman No. 456', '021-7654321', 'contact@medikajaya.com');

