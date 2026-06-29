<?php

require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel
{
    protected string $table = 'users';

    /**
     * Find user by email (untuk login).
     */
    public function findByEmail(string $email): array|false
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE email = :email AND deleted_at IS NULL",
            [':email' => $email]
        );
    }

    /**
     * Find user by ID.
     */
    public function findById(int $id): array|false
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL",
            [':id' => $id]
        );
    }

    /**
     * Create new user.
     */
    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            "INSERT INTO {$this->table} (nama, email, password, no_telp, alamat, created_at, updated_at) 
             VALUES (:nama, :email, :password, :no_telp, :alamat, :created_at, :updated_at)",
            [
                ':nama'       => $data['nama'],
                ':email'      => $data['email'],
                ':password'   => $data['password'],
                ':no_telp'    => $data['no_telp'] ?? null,
                ':alamat'     => $data['alamat'] ?? null,
                ':created_at' => $now,
                ':updated_at' => $now,
            ]
        );
        return (int) $this->lastInsertId();
    }

    /**
     * Count active users.
     */
    public function countActive(): int
    {
        $result = $this->queryOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL"
        );
        return (int)($result['total'] ?? 0);
    }
}
