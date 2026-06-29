<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $count = $this->db->table('pelanggan')
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();

        if ($count > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'nama_pelanggan' => 'Budi Santoso',
                'no_telp'        => '081234567890',
                'alamat'         => 'Jl. Merdeka No. 123, Jakarta',
                'email'          => 'budi@email.com',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'nama_pelanggan' => 'Siti Rahmawati',
                'no_telp'        => '085678901234',
                'alamat'         => 'Jl. Sudirman No. 45, Bandung',
                'email'          => 'siti@email.com',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'nama_pelanggan' => 'Ahmad Hidayat',
                'no_telp'        => '087890123456',
                'alamat'         => 'Jl. Gatot Subroto No. 78, Surabaya',
                'email'          => 'ahmad@email.com',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'nama_pelanggan' => 'Dewi Pratiwi',
                'no_telp'        => '089012345678',
                'alamat'         => 'Jl. Diponegoro No. 22, Yogyakarta',
                'email'          => 'dewi@email.com',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'nama_pelanggan' => 'Eko Prasetyo',
                'no_telp'        => '082345678901',
                'alamat'         => 'Jl. Ahmad Yani No. 99, Semarang',
                'email'          => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ];

        $this->db->table('pelanggan')->insertBatch($data);
    }
}
