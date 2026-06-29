<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $count = $this->db->table('users')->countAll();
        if ($count > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insert([
            'nama'       => 'Budi Santoso',
            'email'      => 'budi@email.com',
            'password'   => password_hash('user123', PASSWORD_BCRYPT),
            'no_telp'    => '081234567890',
            'alamat'     => 'Jl. Merdeka No. 123, Jakarta',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
