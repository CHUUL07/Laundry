<?php

require_once __DIR__ . '/BaseModel.php';

class LayananModel extends BaseModel
{
    protected string $table = 'jenis_layanan';

    // =============================================
    //  READ METHODS
    // =============================================

    /** Get all active (non-deleted) services */
    public function all(): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY created_at DESC"
        );
    }

    /** Get a single active service by ID */
    public function findById(int $id): array|false
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL LIMIT 1",
            [':id' => $id]
        );
    }

    /** Get the N most recent active services */
    public function recent(int $limit = 5): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT :limit",
            [':limit' => $limit]
        );
    }

    /** Get all soft-deleted (archived) services */
    public function archived(): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC"
        );
    }

    // =============================================
    //  COUNT METHODS (for dashboard summary cards)
    // =============================================

    public function countActive(): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL"
        );
        return (int)($row['total'] ?? 0);
    }

    public function countByKategori(string $kategori): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL AND kategori = :kategori",
            [':kategori' => $kategori]
        );
        return (int)($row['total'] ?? 0);
    }

    public function countArchived(): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NOT NULL"
        );
        return (int)($row['total'] ?? 0);
    }

    // =============================================
    //  WRITE METHODS
    // =============================================

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table}
                    (nama_layanan, kategori, harga, satuan_harga, estimasi_durasi, deskripsi)
                VALUES
                    (:nama_layanan, :kategori, :harga, :satuan_harga, :estimasi_durasi, :deskripsi)";

        $affected = $this->execute($sql, [
            ':nama_layanan'    => $data['nama_layanan'],
            ':kategori'        => $data['kategori'],
            ':harga'           => (int)$data['harga'],
            ':satuan_harga'    => $data['satuan_harga'],
            ':estimasi_durasi' => $data['estimasi_durasi'],
            ':deskripsi'       => $data['deskripsi'] ?: null,
        ]);

        return $affected > 0;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET
                    nama_layanan    = :nama_layanan,
                    kategori        = :kategori,
                    harga           = :harga,
                    satuan_harga    = :satuan_harga,
                    estimasi_durasi = :estimasi_durasi,
                    deskripsi       = :deskripsi
                WHERE id = :id AND deleted_at IS NULL";

        $affected = $this->execute($sql, [
            ':nama_layanan'    => $data['nama_layanan'],
            ':kategori'        => $data['kategori'],
            ':harga'           => (int)$data['harga'],
            ':satuan_harga'    => $data['satuan_harga'],
            ':estimasi_durasi' => $data['estimasi_durasi'],
            ':deskripsi'       => $data['deskripsi'] ?: null,
            ':id'              => $id,
        ]);

        return $affected > 0;
    }

    /**
     * SOFT DELETE: Sets deleted_at to NOW() instead of deleting the row.
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
     * RESTORE: Clears the deleted_at field, making the record active again.
     */
    public function restore(int $id): bool
    {
        $affected = $this->execute(
            "UPDATE {$this->table} SET deleted_at = NULL WHERE id = :id AND deleted_at IS NOT NULL",
            [':id' => $id]
        );
        return $affected > 0;
    }
}
