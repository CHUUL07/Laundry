<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Cek dulu apakah admin sudah ada — idempotent seeder
        $exists = $this->db->table('admins')
            ->where('username', 'admin')
            ->countAllResults();

        if ($exists > 0) {
            // Admin sudah ada, skip
            return;
        }

        $this->db->table('admins')->insert([
            'username'   => 'admin',
            'password'   => password_hash('admin123', PASSWORD_BCRYPT),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
