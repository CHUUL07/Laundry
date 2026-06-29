<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        // Cek apakah sudah ada data layanan
        $count = $this->db->table('jenis_layanan')
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();

        // Jika sudah ada data, skip seeding
        if ($count > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'nama_layanan'    => 'Cuci Express',
                'kategori'        => 'express',
                'harga'           => 8000,
                'satuan_harga'    => 'kg',
                'estimasi_durasi' => '2-3 Jam',
                'deskripsi'       => 'Cuci cepat selesai hari itu juga, cocok untuk kebutuhan mendesak.',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'nama_layanan'    => 'Cuci Reguler',
                'kategori'        => 'reguler',
                'harga'           => 5000,
                'satuan_harga'    => 'kg',
                'estimasi_durasi' => '1-2 Hari',
                'deskripsi'       => 'Layanan cuci standar dengan kualitas terjaga.',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'nama_layanan'    => 'Setrika Saja',
                'kategori'        => 'reguler',
                'harga'           => 4000,
                'satuan_harga'    => 'kg',
                'estimasi_durasi' => '6 Jam',
                'deskripsi'       => 'Hanya setrika tanpa cuci, untuk pakaian bersih yang kusut.',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'nama_layanan'    => 'Cuci + Setrika',
                'kategori'        => 'express',
                'harga'           => 12000,
                'satuan_harga'    => 'kg',
                'estimasi_durasi' => '3-4 Jam',
                'deskripsi'       => 'Paket lengkap cuci dan setrika, hasil rapi langsung bisa dipakai.',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'nama_layanan'    => 'Cuci Sepatu',
                'kategori'        => 'reguler',
                'harga'           => 25000,
                'satuan_harga'    => 'item',
                'estimasi_durasi' => '1 Hari',
                'deskripsi'       => 'Cuci bersih sepatu dengan metode khusus, aman untuk berbagai bahan.',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'nama_layanan'    => 'Laundry Paket',
                'kategori'        => 'reguler',
                'harga'           => 35000,
                'satuan_harga'    => 'paket',
                'estimasi_durasi' => '2 Hari',
                'deskripsi'       => 'Paket hemat untuk 5kg cucian termasuk cuci dan setrika.',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];

        $this->db->table('jenis_layanan')->insertBatch($data);
    }
}
