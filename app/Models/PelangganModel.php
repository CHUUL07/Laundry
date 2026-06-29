<?php

require_once __DIR__ . '/BaseModel.php';

class PelangganModel extends BaseModel
{
    protected string $table = 'pelanggan';

    // =============================================
    //  READ METHODS
    // =============================================

    /**
     * Ambil semua pelanggan aktif (deleted_at IS NULL)
     */
    public function all(): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY created_at DESC"
        );
    }

    /**
     * Cari pelanggan berdasarkan ID (hanya yang belum dihapus)
     */
    public function findById(int $id): array|false
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL LIMIT 1",
            [':id' => $id]
        );
    }

    /**
     * Ambil semua pelanggan yang sudah di-soft-delete
     */
    public function archived(): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC"
        );
    }

    // =============================================
    //  COUNT METHODS
    // =============================================

    /**
     * Hitung total pelanggan aktif — untuk dashboard
     */
    public function countActive(): int
    {
        $result = $this->queryOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL"
        );
        return (int)($result['total'] ?? 0);
    }

    /**
     * Hitung total pelanggan yang sudah di-soft-delete
     */
    public function countArchived(): int
    {
        $result = $this->queryOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NOT NULL"
        );
        return (int)($result['total'] ?? 0);
    }

    // =============================================
    //  WRITE METHODS
    // =============================================

    /**
     * Tambah pelanggan baru
     */
    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO {$this->table} (nama_pelanggan, no_telp, email, alamat)
             VALUES (:nama_pelanggan, :no_telp, :email, :alamat)",
            [
                ':nama_pelanggan' => $data['nama_pelanggan'],
                ':no_telp'        => $data['no_telp'],
                ':email'          => $data['email'] ?: null,
                ':alamat'         => $data['alamat'] ?: null,
            ]
        );
        return (int) $this->lastInsertId();
    }

    /**
     * Update data pelanggan
     */
    public function update(int $id, array $data): bool
    {
        $affected = $this->execute(
            "UPDATE {$this->table} SET
                nama_pelanggan = :nama_pelanggan,
                no_telp        = :no_telp,
                email          = :email,
                alamat         = :alamat
             WHERE id = :id AND deleted_at IS NULL",
            [
                ':nama_pelanggan' => $data['nama_pelanggan'],
                ':no_telp'        => $data['no_telp'],
                ':email'          => $data['email'] ?: null,
                ':alamat'         => $data['alamat'] ?: null,
                ':id'             => $id,
            ]
        );
        return $affected > 0;
    }

    /**
     * Soft delete pelanggan (set deleted_at = NOW())
     */
    public function softDelete(int $id): bool
    {
        $affected = $this->execute(
            "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL",
            [':id' => $id]
        );
        return $affected > 0;
    }

    /**
     * Restore pelanggan yang sudah di-soft-delete
     */
    public function restore(int $id): bool
    {
        $affected = $this->execute(
            "UPDATE {$this->table} SET deleted_at = NULL WHERE id = :id AND deleted_at IS NOT NULL",
            [':id' => $id]
        );
        return $affected > 0;
    }

    // =============================================
    //  VALIDATION
    // =============================================

    /**
     * Validasi data input untuk create/update
     */
    public function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        // --- nama_pelanggan ---
        if (empty($data['nama_pelanggan'])) {
            $errors['nama_pelanggan'] = 'Nama pelanggan wajib diisi.';
        } elseif (strlen($data['nama_pelanggan']) > 100) {
            $errors['nama_pelanggan'] = 'Nama pelanggan maksimal 100 karakter.';
        }

        // --- no_telp ---
        if (empty($data['no_telp'])) {
            $errors['no_telp'] = 'Nomor telepon wajib diisi.';
        } elseif (!preg_match('/^[0-9]{10,15}$/', $data['no_telp'])) {
            $errors['no_telp'] = 'Nomor telepon harus 10-15 digit angka.';
        }

        // --- email (opsional) ---
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }

        return $errors;
    }
}
