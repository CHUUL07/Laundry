<?php

require_once __DIR__ . '/BaseModel.php';

class PesananModel extends BaseModel
{
    protected string $table = 'pesanan';

    /**
     * Ambil semua pesanan dengan JOIN users, ORDER BY created_at DESC.
     */
    public function all(): array
    {
        return $this->query(
            "SELECT p.*, u.nama as nama_user, u.email as email_user, u.no_telp, u.alamat 
             FROM {$this->table} p 
             LEFT JOIN users u ON p.user_id = u.id 
             WHERE p.deleted_at IS NULL 
             ORDER BY p.created_at DESC"
        );
    }

    /**
     * Ambil pesanan by status.
     */
    public function findByStatus(string $status): array
    {
        return $this->query(
            "SELECT p.*, u.nama as nama_user, u.email as email_user 
             FROM {$this->table} p 
             LEFT JOIN users u ON p.user_id = u.id 
             WHERE p.deleted_at IS NULL AND p.status = :status 
             ORDER BY p.created_at DESC",
            [':status' => $status]
        );
    }

    /**
     * Ambil satu pesanan by ID dengan data user.
     */
    public function findById(int $id): array|false
    {
        return $this->queryOne(
            "SELECT p.*, u.nama, u.email as email_user, u.no_telp, u.alamat 
             FROM {$this->table} p 
             LEFT JOIN users u ON p.user_id = u.id 
             WHERE p.id = :id AND p.deleted_at IS NULL",
            [':id' => $id]
        );
    }

    /**
     * Ambil detail items dari suatu pesanan.
     */
    public function getDetail(int $pesananId): array
    {
        return $this->query(
            "SELECT * FROM detail_pesanan WHERE pesanan_id = :pesanan_id ORDER BY id ASC",
            [':pesanan_id' => $pesananId]
        );
    }

    /**
     * Buat pesanan baru.
     */
    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO {$this->table} (user_id, kode_pesanan, status, metode_pengiriman, total_harga, catatan, tanggal_pesan, created_at) 
             VALUES (:user_id, :kode_pesanan, :status, :metode_pengiriman, :total_harga, :catatan, :tanggal_pesan, :created_at)",
            [
                ':user_id'           => $data['user_id'],
                ':kode_pesanan'      => $data['kode_pesanan'],
                ':status'            => $data['status'],
                ':metode_pengiriman' => $data['metode_pengiriman'],
                ':total_harga'       => $data['total_harga'],
                ':catatan'           => $data['catatan'] ?? null,
                ':tanggal_pesan'     => $data['tanggal_pesan'],
                ':created_at'        => $data['created_at'],
            ]
        );
        return (int) $this->lastInsertId();
    }

    /**
     * Tambah detail item ke pesanan.
     */
    public function addDetail(int $pesananId, array $item): void
    {
        $this->execute(
            "INSERT INTO detail_pesanan (pesanan_id, nama_layanan, harga_satuan, satuan_harga, quantity, subtotal) 
             VALUES (:pesanan_id, :nama_layanan, :harga_satuan, :satuan_harga, :quantity, :subtotal)",
            [
                ':pesanan_id'   => $pesananId,
                ':nama_layanan' => $item['nama_layanan'],
                ':harga_satuan' => $item['harga_satuan'],
                ':satuan_harga' => $item['satuan_harga'] ?? 'kg',
                ':quantity'     => $item['quantity'],
                ':subtotal'     => $item['subtotal'],
            ]
        );
    }

    /**
     * Update status pesanan.
     */
    public function updateStatus(int $id, string $status): bool
    {
        $affected = $this->execute(
            "UPDATE {$this->table} SET status = :status, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL",
            [':status' => $status, ':id' => $id]
        );
        return $affected > 0;
    }

    /**
     * Hitung jumlah pesanan berdasarkan status.
     */
    public function countByStatus(string $status): int
    {
        $result = $this->queryOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL AND status = :status",
            [':status' => $status]
        );
        return (int)($result['total'] ?? 0);
    }

    /**
     * Hitung jumlah pesanan baru (status 'diterima') — untuk badge sidebar.
     */
    public function countNew(): int
    {
        return $this->countByStatus('diterima');
    }

    /**
     * Hitung total semua pesanan aktif.
     */
    public function countActive(): int
    {
        $result = $this->queryOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL"
        );
        return (int)($result['total'] ?? 0);
    }
}
