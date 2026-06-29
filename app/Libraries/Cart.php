<?php

/**
 * Cart Library — Laundry-IN v2.0
 *
 * Shopping cart berbasis PHP Session.
 * Menyimpan data di $_SESSION['shopping_cart'].
 *
 * Format item di session:
 * [
 *   'id_layanan'   => int,
 *   'nama_layanan' => string,
 *   'harga'        => int,
 *   'satuan_harga' => string,
 *   'quantity'     => int,
 *   'subtotal'     => int,   // harga * quantity
 * ]
 *
 * Method wajib sesuai Rules.md §9.1:
 * - insert($id, $data)   — Tambah item
 * - update($id, $qty)    — Ubah quantity
 * - total()              — Hitung total harga
 * - remove($id)         — Hapus item spesifik
 * - destroy()            — Kosongkan semua
 */
class Cart
{
    private const SESSION_KEY = 'shopping_cart';

    public function __construct()
    {
        // Pastikan session sudah dimulai
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Inisialisasi cart jika belum ada
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    // ----------------------------------------------------------------
    // INSERT — Tambah item ke cart (Rules.md §9.1)
    // Jika item sudah ada, tambah quantity-nya
    // ----------------------------------------------------------------

    /**
     * Tambah item ke keranjang belanja.
     *
     * @param int   $id   ID layanan (dari tabel jenis_layanan)
     * @param array $data Array berisi: nama_layanan, harga, satuan_harga, quantity (opsional, default 1)
     * @return bool true jika berhasil
     */
    public function insert(int $id, array $data): bool
    {
        if ($id <= 0) {
            return false;
        }

        $qty = max(1, (int)($data['quantity'] ?? 1));

        if (isset($_SESSION[self::SESSION_KEY][$id])) {
            // Item sudah ada — tambah quantity
            $_SESSION[self::SESSION_KEY][$id]['quantity'] += $qty;
            $_SESSION[self::SESSION_KEY][$id]['subtotal']  =
                $_SESSION[self::SESSION_KEY][$id]['harga'] *
                $_SESSION[self::SESSION_KEY][$id]['quantity'];
        } else {
            // Item baru — tambah ke cart
            $harga = max(0, (int)($data['harga'] ?? 0));
            $_SESSION[self::SESSION_KEY][$id] = [
                'id_layanan'   => $id,
                'nama_layanan' => $data['nama_layanan'] ?? 'Unknown',
                'harga'        => $harga,
                'satuan_harga' => $data['satuan_harga'] ?? 'item',
                'quantity'     => $qty,
                'subtotal'     => $harga * $qty,
            ];
        }

        return true;
    }

    // ----------------------------------------------------------------
    // UPDATE — Ubah quantity item di cart (Rules.md §9.1)
    // Jika qty = 0, item dihapus dari cart
    // ----------------------------------------------------------------

    /**
     * Perbarui quantity item di keranjang.
     *
     * @param int $id  ID layanan
     * @param int $qty Quantity baru (0 = hapus dari cart)
     * @return bool true jika berhasil, false jika item tidak ditemukan
     */
    public function update(int $id, int $qty): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY][$id])) {
            return false;
        }

        if ($qty <= 0) {
            // Jika qty 0 atau negatif, hapus item
            return $this->remove($id);
        }

        $_SESSION[self::SESSION_KEY][$id]['quantity'] = $qty;
        $_SESSION[self::SESSION_KEY][$id]['subtotal']  =
            $_SESSION[self::SESSION_KEY][$id]['harga'] * $qty;

        return true;
    }

    // ----------------------------------------------------------------
    // TOTAL — Hitung total harga semua item di cart (Rules.md §9.1)
    // ----------------------------------------------------------------

    /**
     * Hitung total harga semua item di keranjang.
     *
     * @return int Total harga dalam rupiah (integer)
     */
    public function total(): int
    {
        $total = 0;
        foreach ($_SESSION[self::SESSION_KEY] as $item) {
            $total += (int)$item['subtotal'];
        }
        return $total;
    }

    // ----------------------------------------------------------------
    // REMOVE — Hapus satu item dari cart berdasarkan ID (Rules.md §9.1)
    // ----------------------------------------------------------------

    /**
     * Hapus satu item dari keranjang.
     *
     * @param int $id ID layanan yang ingin dihapus
     * @return bool true jika berhasil, false jika tidak ditemukan
     */
    public function remove(int $id): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY][$id])) {
            return false;
        }

        unset($_SESSION[self::SESSION_KEY][$id]);
        return true;
    }

    // ----------------------------------------------------------------
    // DESTROY — Kosongkan seluruh isi cart (Rules.md §9.1)
    // ----------------------------------------------------------------

    /**
     * Kosongkan seluruh keranjang belanja.
     *
     * @return void
     */
    public function destroy(): void
    {
        $_SESSION[self::SESSION_KEY] = [];
    }

    // ----------------------------------------------------------------
    // Helper Methods — untuk view
    // ----------------------------------------------------------------

    /**
     * Ambil semua item di cart sebagai array (re-indexed).
     *
     * @return array
     */
    public function getItems(): array
    {
        return array_values($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Hitung total jumlah item (sum of all quantities).
     *
     * @return int
     */
    public function count(): int
    {
        $count = 0;
        foreach ($_SESSION[self::SESSION_KEY] as $item) {
            $count += (int)$item['quantity'];
        }
        return $count;
    }

    /**
     * Cek apakah cart kosong.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Cek apakah item dengan ID tertentu sudah ada di cart.
     *
     * @param int $id
     * @return bool
     */
    public function has(int $id): bool
    {
        return isset($_SESSION[self::SESSION_KEY][$id]);
    }
}
