-- ============================================
-- Laundry-IN — Seed Data
-- Database: kampusin_db
-- ============================================

USE kampusin_db;

-- Admin seed (password: admin123)
INSERT INTO `admins` (`username`, `password`) VALUES (
    'admin',
    '$2y$10$RTus.vW5U9z.MzHtlwWV6.ZWeF7UUI2joUciozQKGh0BPI8swqb.2'
);

-- Jenis Layanan seed data
INSERT INTO `jenis_layanan` (`nama_layanan`, `kategori`, `harga`, `satuan_harga`, `estimasi_durasi`, `deskripsi`) VALUES
('Cuci Express', 'express', 8000, 'kg', '2-3 Jam', 'Cuci cepat selesai hari itu juga, cocok untuk kebutuhan mendesak.'),
('Cuci Reguler', 'reguler', 5000, 'kg', '1-2 Hari', 'Layanan cuci standar dengan kualitas terjaga.'),
('Setrika Saja', 'reguler', 4000, 'kg', '6 Jam', 'Hanya setrika tanpa cuci, untuk pakaian bersih yang kusut.'),
('Cuci + Setrika', 'express', 12000, 'kg', '3-4 Jam', 'Paket lengkap cuci dan setrika, hasil rapi langsung bisa dipakai.'),
('Cuci Sepatu', 'reguler', 25000, 'item', '1 Hari', 'Cuci bersih sepatu dengan metode khusus, aman untuk berbagai bahan.'),
('Laundry Paket', 'reguler', 35000, 'paket', '2 Hari', 'Paket hemat untuk 5kg cucian termasuk cuci dan setrika.');
