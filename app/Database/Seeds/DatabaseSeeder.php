<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // URUTAN PENTING: AdminSeeder dulu, baru Layanan, baru Pelanggan, baru Users
        $this->call('AdminSeeder');
        $this->call('LayananSeeder');
        $this->call('PelangganSeeder');
        $this->call('UsersSeeder');
    }
}
