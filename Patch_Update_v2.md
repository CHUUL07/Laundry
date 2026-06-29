# Patch_Update_v3.md — Laundry-IN

**Versi:** 3.0.0
**Status v2.0:** COMPLETE (Phase A-H sudah selesai, lihat State.md)
**Dibuat:** 29 Juni 2026
**Phase I (v3.0):** Sidebar Theme, User Auth, Cart → User, Pesanan Admin, Struk PDF
**Format:** Sequential Phase Guide — dibaca dan dieksekusi oleh AI Coding Assistant / GitHub Copilot
**Prerequisite:** Baca `State.md` terlebih dahulu untuk memahami kondisi v1.0 yang sudah ada.

---

> **INSTRUKSI UNTUK COPILOT/AI ASSISTANT:**
> Eksekusi setiap Phase secara BERURUTAN. Jangan skip. Sebelum mengerjakan Phase berikutnya, pastikan Phase saat ini sudah verified. Jangan mengubah file yang tidak disebutkan di tiap step — itu melanggar Rules.md §1.1. Setiap kode yang diberikan adalah output final yang diharapkan.

---

## Daftar Isi

- [Audit v1.0 — Masalah yang Tersisa](#audit-v10--masalah-yang-tersisa)
- [Saran Perbaikan Teknis (Wajib Dibaca)](#saran-perbaikan-teknis-wajib-dibaca)
- [Mapping Soal Ujian vs Kondisi v1.0](#mapping-soal-ujian-vs-kondisi-v10)
- [Phase A — Prerequisite: Routing Unification (WAJIB PERTAMA)](#phase-a--prerequisite-routing-unification-wajib-pertama)
- [Phase B — Migration & Seeder CI4 (SOAL 02)](#phase-b--migration--seeder-ci4-soal-02)
- [Phase C — CRUD Pelanggan (SOAL 01 — bagian yang kurang)](#phase-c--crud-pelanggan-soal-01--bagian-yang-kurang)
- [Phase D — Login dari Database: Verifikasi & Hardening (SOAL 03)](#phase-d--login-dari-database-verifikasi--hardening-soal-03)
- [Phase E — Export PDF dengan Dompdf (SOAL 04)](#phase-e--export-pdf-dengan-dompdf-soal-04)
- [Phase F — Shopping Cart Library (SOAL 05)](#phase-f--shopping-cart-library-soal-05)
- [Phase G — Dashboard Update & Integrasi Final](#phase-g--dashboard-update--integrasi-final)
- [Phase H — Final Testing & Submission Checklist](#phase-h--final-testing--submission-checklist)
- [Phase I — Sidebar Theme, User Auth, Pesanan Workflow & Struk PDF](#phase-i--sidebar-theme-user-auth-pesanan-workflow--struk-pdf)
- [File Tree Final v2.0](#file-tree-final-v20)
- [File Tree Final v3.0](#file-tree-final-v30)
- [Ringkasan Eksekutif v3.0](#ringkasan-eksekutif-v30)

---

## Audit v1.0 — Masalah yang Tersisa

Berdasarkan `State.md`, bugs berikut sudah difix di v1.0. Tapi ada masalah yang BELUM ditangani:

### MASALAH KRITIS yang Masih Ada

**MASALAH #1 — Dual Routing System (Belum Dikonsolidasi)**

State.md mencatat bahwa `index.php` di root menggunakan custom router berbasis `$_GET['url']`, SEMENTARA CI4 juga punya `app/Config/Routes.php`. Keduanya ada dan beroperasi secara bersamaan. Ini menyebabkan:

- Saat akses via Apache (`http://localhost/laundry-in/`): Custom `index.php` yang jalan
- Saat `php spark serve`: CI4 Routes yang jalan
- Behavior berbeda = bug yang unpredictable

State.md menyebut redirect sudah difix ke tanpa prefix, tapi akar masalahnya (dual system) belum diselesaikan.

**Solusi di Phase A:** Konsolidasi ke SATU sistem. Karena CI4 sudah terinstall dan ada `app/Config/Routes.php`, kita gunakan CI4 Routes sebagai satu-satunya router. Custom `index.php` diubah menjadi thin bootstrap yang hanya boot CI4.

**MASALAH #2 — Asset Path Bergantung pada Lokasi Deploy**

State.md mencatat assets sudah dipindahkan ke `assets/` di root. Tapi path yang digunakan di layout masih hardcoded `/laundry-in/assets/...` atau `/assets/...`. Ini akan broken jika project dipindah folder atau deploy di server lain.

**Solusi:** Gunakan CI4 `base_url()` helper yang membaca konfigurasi dari `.env`. Ini sekali setting, berlaku di mana saja.

**MASALAH #3 — Controllers Masih Menggunakan Hybrid Approach**

`State.md` menunjukkan ada `app/Libraries/Database.php` (custom PDO singleton) DAN `app/Config/Database.php` (CI4 config). Controllers memanggil keduanya secara berbeda. Ini tidak konsisten dan menyulitkan maintenance.

**Solusi:** Semua controllers menggunakan CI4 native database (`$db = \Config\Database::connect()`). Tapi karena ini perubahan besar yang bisa merusak yang sudah jalan, kita HANYA lakukan ini untuk file BARU (PelangganController, CartController). File yang sudah ada JANGAN diubah (Rules.md §1.1).

### MASALAH MEDIUM

**MASALAH #4 — `app/Config/Routes.php` Belum Terdefinisi Lengkap**

Routes untuk pelanggan, cart, dan PDF export belum ada. Harus ditambahkan.

**MASALAH #5 — Migration CI4 Belum Ada**

`State.md` §Phase 2 menunjukkan database dibuat via SQL manual di phpMyAdmin. Untuk memenuhi SOAL 02, file migration CI4 harus dibuat.

---

## Saran Perbaikan Teknis (Wajib Dibaca)

Ini saran yang akan membuat kode lebih robust, bebas bug, dan future-proof. Beberapa sudah diimplementasi di patch ini, beberapa adalah catatan untuk diperhatikan.

### Saran 1 — Jangan Campur PDO Custom dengan CI4 DB (DITERAPKAN)

Draft Patch_Update_v2 user menggunakan `BaseModel.php` custom (PDO) untuk `PelangganModel`. Ini masalah karena:

- Jika CI4 migration dijalankan, koneksi ada 2 (CI4 DB config + custom PDO)
- CI4 Session Library default pakai database driver — perlu koneksi CI4
- Dompdf butuh data dari query — lebih mudah jika pakai model yang konsisten

**Keputusan di patch ini:** File BARU (PelangganModel, CartLibrary) menggunakan CI4 native database. File LAMA (LayananModel, AdminModel yang pakai BaseModel) TIDAK diubah — cukup ditambahkan method baru saja.

### Saran 2 — Cart Berbasis Session, Bukan Database (DITERAPKAN SESUAI SOAL)

Draft user membuat tabel `cart` di database. Tapi Rules.md §9.2 secara eksplisit menyatakan:

> "Cart disimpan di `$_SESSION['shopping_cart']` — JANGAN simpan cart di database untuk assignment ini"

Selain itu, tabel `cart` dengan FK ke `pelanggan` tidak masuk akal untuk shopping cart sementara. Soal 05 meminta library Cart dengan method tertentu, bukan database-backed cart.

**Keputusan:** Cart menggunakan session (`$_SESSION['shopping_cart']`). TIDAK perlu migration tabel cart.

### Saran 3 — Validasi Input Server-Side Konsisten (DITERAPKAN)

Draft user di PelangganController tidak memvalidasi `no_telp` hanya berupa angka. Ini bisa menyebabkan data kotor. Patch ini menambahkan validasi: no_telp hanya angka, panjang 10-15 karakter.

### Saran 4 — PDF Menggunakan Data Real dari Database (DITERAPKAN)

Draft user hanya menyebut "tambah tombol Export PDF" tanpa detail implementasi Dompdf yang lengkap. Patch ini memberikan implementasi lengkap: install Composer, autoload, template HTML untuk PDF, dan controller method yang benar.

### Saran 5 — Namespace Seeder Harus Konsisten (BUG DI DRAFT USER)

Draft user memanggil seeder dengan:

```php
$this->call('App\Database\Seeds\AdminSeeder');
```

Tapi CI4 `Seeder::call()` menerima nama class pendek, bukan FQCN:

```php
$this->call('AdminSeeder'); // BENAR untuk CI4
```

Patch ini menggunakan cara yang benar.

### Saran 6 — Idempotent Seeder (DITERAPKAN)

Jika seeder dijalankan 2x, akan menyebabkan duplicate data atau error. Patch ini menambahkan pengecekan `truncate()` sebelum insert, atau cek `EXISTS` untuk admin.

### Saran 7 — Migration `up()` dan `down()` Harus Ada Return Type (PHP 8.1)

Draft user menggunakan `public function up()` tanpa return type. Untuk PHP 8.1 strict mode dan CI4 4.x, gunakan `public function up(): void`.

### Saran 8 — Soft Delete di Pelanggan Harus Filter dari Cart View (DITERAPKAN)

Jika pelanggan soft-deleted tapi masih ada di shopping cart (session), cart view bisa crash saat mencoba lookup nama pelanggan. Patch ini menambahkan null-check di cart view.

---

## Mapping Soal Ujian vs Kondisi v1.0

| Soal      | Bobot    | Requirement                                          | Status v1.0           | Gap                                       | Dikerjakan di |
| --------- | -------- | ---------------------------------------------------- | --------------------- | ----------------------------------------- | ------------- |
| SOAL 01   | 20%      | CRUD Layanan + **CRUD Pelanggan** (keduanya!)        | 50% — hanya Layanan   | Pelanggan belum ada                       | Phase C       |
| SOAL 02   | 20%      | Migration CI4 + Seeder CI4 + Model                   | 0% — pakai SQL manual | Semua file migration & seeder belum ada   | Phase B       |
| SOAL 03   | 20%      | Login validasi dari **database** (bukan statis)      | 100% — sudah ada      | Verifikasi + hardening saja               | Phase D       |
| SOAL 04   | 20%      | Dompdf export PDF halaman layanan                    | 0% — belum ada        | Install dompdf + implementasi             | Phase E       |
| SOAL 05   | 20%      | Library Cart: insert, update, total, remove, destroy | 0% — belum ada        | Buat Cart library + CartController + view | Phase F       |
| **Total** | **100%** |                                                      | **~20%**              |                                           | **Phase A-H** |

---

## Phase A — Prerequisite: Routing Unification (WAJIB PERTAMA)

**Tujuan:** Menghapus dual routing system. Setelah phase ini, SEMUA request dihandle oleh CI4 Routes. Custom `index.php` hanya menjadi thin bootstrap CI4.

**Estimasi waktu:** 15 menit
**File yang diubah:** `index.php` (root), `app/Config/Routes.php`
**File yang TIDAK diubah:** Semua file lain

### Step A.1 — Verifikasi CI4 Bootstrap

Buka file `public/index.php` (bukan root `index.php`). Pastikan ini adalah CI4 standard entry point:

```php
<?php
// CI4 standard public/index.php sudah ada
// Tidak perlu diubah
```

File ini adalah entry point CI4. `.htaccess` di root harus forward request ke `public/index.php`.

### Step A.2 — Update `.htaccess` di Root

Edit file `.htaccess` di root project:

```apache
Options -Indexes
RewriteEngine On

# Redirect semua request ke public/index.php (CI4 entry point)
# Bukan ke index.php di root lagi
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php/$1 [L]

# Block direct access ke app/ directory
RewriteRule ^app/ - [F,L]
RewriteRule ^writable/ - [F,L]
```

**PENTING:** Jika sudah ada `public/.htaccess` dari CI4, jangan hapus. Yang diubah hanya `.htaccess` di ROOT.

### Step A.3 — Ubah Root `index.php` menjadi Thin Redirect

Root `index.php` (yang dulunya custom router) diubah isinya menjadi sederhana. Seluruh routing logic di dalam `index.php` lama DIHAPUS dan diganti dengan ini:

```php
<?php
/**
 * Laundry-IN — Root Bootstrap
 * Redirects ke CI4 entry point di public/index.php
 * Semua routing ditangani oleh app/Config/Routes.php
 */

// Jika diakses langsung via http://localhost/laundry-in/index.php
// redirect ke public/ (CI4 front controller)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path     = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

header('Location: ' . $protocol . '://' . $host . $path . '/public/');
exit;
```

### Step A.4 — Perbarui `app/Config/App.php`

Buka `app/Config/App.php`. Set `$baseURL` agar `base_url()` helper bekerja dengan benar:

```php
// app/Config/App.php
public string $baseURL = 'http://localhost/laundry-in/public/';
```

Atau lebih baik, biarkan dibaca dari `.env`:

Di file `.env`:

```env
app.baseURL = 'http://localhost/laundry-in/public/'
```

### Step A.5 — Update Asset Paths di Semua Layout

Sekarang `base_url()` berfungsi, update semua path CSS/JS di layouts agar dinamis:

**File:** `app/Views/layouts/main.php` — Cari semua `href` dan `src` untuk assets, ubah dari:

```html
<link rel="stylesheet" href="/laundry-in/assets/css/variables.css" />
```

Menjadi:

```html
<link rel="stylesheet" href="<?= base_url('assets/css/variables.css') ?>" />
```

Lakukan hal yang sama untuk SEMUA file di `assets/css/` dan `assets/js/` yang di-load di layout.

**File:** `app/Views/layouts/auth.php` — Sama seperti di atas.

**File:** `app/Views/layouts/landing.php` — Sama seperti di atas.

### Step A.6 — Verifikasi Checkpoint

Jalankan:

```bash
php spark serve
```

Buka `http://localhost:8080/login` di browser.

Checklist:

- [ ] Halaman login tampil dengan CSS lengkap (tidak broken/unstyled)
- [ ] Theme toggle (dark/light) berfungsi
- [ ] Login dengan `admin / admin123` berhasil masuk dashboard
- [ ] Sidebar tampil dengan styling lengkap
- [ ] Navigasi antar halaman tidak ada 404

**STOP. Jangan lanjut ke Phase B jika checklist di atas belum semua centang.**

---

## Phase B — Migration & Seeder CI4 (SOAL 02)

**Tujuan:** Membuat file migration dan seeder CI4 yang proper. Ini bukti bahwa schema database dibuat dengan cara profesional, bukan manual SQL.

**Estimasi waktu:** 20 menit
**File yang dibuat:** 4 migration files, 4 seeder files
**File yang diubah:** `app/Config/Database.php` (verifikasi saja)
**File yang TIDAK diubah:** Kode controller/model yang sudah ada

### Step B.1 — Verifikasi Konfigurasi Database CI4

Buka `app/Config/Database.php`. Pastikan isinya seperti ini (sesuaikan dengan `.env`):

```php
// app/Config/Database.php
public array $default = [
    'DSN'      => '',
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'kampusin_db',
    'DBDriver' => 'MySQLi',
    'DBPrefix' => '',
    'pConnect' => false,
    'DBDebug'  => true,
    'charset'  => 'utf8mb4',
    'DBCollat' => 'utf8mb4_unicode_ci',
    'swapPre'  => '',
    'encrypt'  => false,
    'compress' => false,
    'strictOn' => false,
    'failover' => [],
    'port'     => 3306,
    'numberNative' => false,
];
```

Atau biarkan dibaca dari `.env` (lebih aman):

```env
database.default.hostname = localhost
database.default.database = kampusin_db
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### Step B.2 — Migration 1: Tabel `admins`

Buat file: `app/Database/Migrations/2026-06-29-000001_CreateAdminsTable.php`

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('admins', true); // true = IF NOT EXISTS
    }

    public function down(): void
    {
        $this->forge->dropTable('admins', true);
    }
}
```

### Step B.3 — Migration 2: Tabel `jenis_layanan`

Buat file: `app/Database/Migrations/2026-06-29-000002_CreateJenisLayananTable.php`

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJenisLayananTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_layanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'kategori' => [
                'type'       => 'ENUM',
                'constraint' => ['express', 'reguler'],
                'null'       => false,
            ],
            'harga' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 0,
            ],
            'satuan_harga' => [
                'type'       => 'ENUM',
                'constraint' => ['kg', 'item', 'paket'],
                'null'       => false,
                'default'    => 'kg',
            ],
            'estimasi_durasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('jenis_layanan', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('jenis_layanan', true);
    }
}
```

### Step B.4 — Migration 3: Tabel `pelanggan`

Buat file: `app/Database/Migrations/2026-06-29-000003_CreatePelangganTable.php`

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePelangganTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_pelanggan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'no_telp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('pelanggan', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('pelanggan', true);
    }
}
```

> **CATATAN PENTING:** Tabel `cart` TIDAK dibuat via migration karena cart menggunakan session (sesuai Rules.md §9.2 dan SOAL 05 yang meminta library Cart berbasis session). Membuat tabel cart bertentangan dengan requirement.

### Step B.5 — Seeder 1: AdminSeeder

Buat file: `app/Database/Seeds/AdminSeeder.php`

```php
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
```

### Step B.6 — Seeder 2: LayananSeeder

Buat file: `app/Database/Seeds/LayananSeeder.php`

```php
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan tabel sebelum insert agar idempotent
        // Hanya hapus yang bukan soft-deleted dan bukan data real
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
```

### Step B.7 — Seeder 3: PelangganSeeder

Buat file: `app/Database/Seeds/PelangganSeeder.php`

```php
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
```

### Step B.8 — Seeder Utama: DatabaseSeeder

Buat file: `app/Database/Seeds/DatabaseSeeder.php`

```php
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // URUTAN PENTING: AdminSeeder dulu, baru Layanan, baru Pelanggan
        // Karena Layanan dan Pelanggan tidak punya dependency,
        // tapi tetap urut agar konsisten
        $this->call('AdminSeeder');
        $this->call('LayananSeeder');
        $this->call('PelangganSeeder');
    }
}
```

> **CATATAN:** CI4 `Seeder::call()` menerima nama class pendek (bukan FQCN). CI4 akan mencari di namespace `App\Database\Seeds` secara otomatis.

### Step B.9 — Jalankan Migration dan Seeder

```bash
# Dari root project (bukan dari public/)
# Jika tabel sudah ada dari manual SQL, migration akan skip karena IF NOT EXISTS

# 1. Jalankan migration
php spark migrate

# Output yang diharapkan:
# Running (Database): 2026-06-29-000001_CreateAdminsTable
# Running (Database): 2026-06-29-000002_CreateJenisLayananTable
# Running (Database): 2026-06-29-000003_CreatePelangganTable

# 2. Jalankan seeder
php spark db:seed DatabaseSeeder

# Output yang diharapkan:
# Seeded: AdminSeeder
# Seeded: LayananSeeder
# Seeded: PelangganSeeder
```

### Step B.10 — Verifikasi Checkpoint Phase B

Di phpMyAdmin atau MySQL CLI:

```sql
-- Cek tabel migrations CI4 terbuat
SELECT * FROM migrations ORDER BY id DESC LIMIT 5;

-- Cek data admin
SELECT id, username FROM admins;

-- Cek data layanan (seharusnya 6 rows)
SELECT COUNT(*) FROM jenis_layanan WHERE deleted_at IS NULL;

-- Cek data pelanggan (seharusnya 5 rows)
SELECT COUNT(*) FROM pelanggan WHERE deleted_at IS NULL;
```

**STOP. Jangan lanjut ke Phase C jika tabel belum ada atau data tidak ter-insert.**

---

## Phase C — CRUD Pelanggan (SOAL 01 — bagian yang kurang)

**Tujuan:** Membuat fitur CRUD lengkap untuk data Pelanggan, melengkapi SOAL 01 yang sebelumnya hanya punya CRUD Layanan.

**Estimasi waktu:** 45 menit
**File yang dibuat:** `PelangganModel.php`, `PelangganController.php`, 4 view files
**File yang diubah:** `app/Config/Routes.php`, `app/Views/layouts/main.php` (sidebar nav)
**File yang TIDAK diubah:** Semua file layanan, auth, dashboard yang sudah ada

### Step C.1 — Buat PelangganModel

Buat file: `app/Models/PelangganModel.php`

Model ini menggunakan `BaseModel.php` yang sudah ada (PDO custom) agar konsisten dengan model yang sudah ada. JANGAN membuat dua sistem koneksi database.

```php
<?php

require_once __DIR__ . '/BaseModel.php';

class PelangganModel extends BaseModel
{
    protected string $table = 'pelanggan';

    /**
     * Ambil semua pelanggan aktif (deleted_at IS NULL)
     * Diurutkan dari yang terbaru
     */
    public function all(): array
    {
        return $this->query(
            "SELECT * FROM {$this->table}
             WHERE deleted_at IS NULL
             ORDER BY created_at DESC"
        );
    }

    /**
     * Cari pelanggan berdasarkan ID (hanya yang belum dihapus)
     */
    public function findById(int $id): ?array
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table}
             WHERE id = :id AND deleted_at IS NULL
             LIMIT 1",
            [':id' => $id]
        );
    }

    /**
     * Tambah pelanggan baru
     * Mengembalikan ID yang baru dibuat
     */
    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO {$this->table}
                (nama_pelanggan, no_telp, alamat, email, created_at, updated_at)
             VALUES
                (:nama_pelanggan, :no_telp, :alamat, :email, NOW(), NOW())",
            [
                ':nama_pelanggan' => $data['nama_pelanggan'],
                ':no_telp'        => $data['no_telp'],
                ':alamat'         => $data['alamat'] ?? null,
                ':email'          => $data['email'] ?? null,
            ]
        );
        return $this->lastInsertId();
    }

    /**
     * Update data pelanggan
     */
    public function update(int $id, array $data): void
    {
        $this->execute(
            "UPDATE {$this->table}
             SET nama_pelanggan = :nama_pelanggan,
                 no_telp        = :no_telp,
                 alamat         = :alamat,
                 email          = :email,
                 updated_at     = NOW()
             WHERE id = :id AND deleted_at IS NULL",
            [
                ':nama_pelanggan' => $data['nama_pelanggan'],
                ':no_telp'        => $data['no_telp'],
                ':alamat'         => $data['alamat'] ?? null,
                ':email'          => $data['email'] ?? null,
                ':id'             => $id,
            ]
        );
    }

    /**
     * Soft delete pelanggan (set deleted_at = NOW())
     */
    public function softDelete(int $id): void
    {
        $this->execute(
            "UPDATE {$this->table}
             SET deleted_at = NOW()
             WHERE id = :id AND deleted_at IS NULL",
            [':id' => $id]
        );
    }

    /**
     * Restore pelanggan yang sudah di-soft-delete
     */
    public function restore(int $id): void
    {
        $this->execute(
            "UPDATE {$this->table}
             SET deleted_at = NULL, updated_at = NOW()
             WHERE id = :id AND deleted_at IS NOT NULL",
            [':id' => $id]
        );
    }

    /**
     * Ambil semua pelanggan yang sudah di-soft-delete
     */
    public function archived(): array
    {
        return $this->query(
            "SELECT * FROM {$this->table}
             WHERE deleted_at IS NOT NULL
             ORDER BY deleted_at DESC"
        );
    }

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
     * Validasi data input
     * Mengembalikan array error (kosong = valid)
     */
    public function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        // Nama pelanggan — wajib, max 100 char
        if (empty(trim($data['nama_pelanggan'] ?? ''))) {
            $errors['nama_pelanggan'] = 'Nama pelanggan wajib diisi.';
        } elseif (mb_strlen(trim($data['nama_pelanggan'])) > 100) {
            $errors['nama_pelanggan'] = 'Nama pelanggan maksimal 100 karakter.';
        }

        // No telp — wajib, hanya angka, 10-15 digit
        $noTelp = trim($data['no_telp'] ?? '');
        if (empty($noTelp)) {
            $errors['no_telp'] = 'Nomor telepon wajib diisi.';
        } elseif (!ctype_digit($noTelp)) {
            $errors['no_telp'] = 'Nomor telepon hanya boleh berisi angka.';
        } elseif (strlen($noTelp) < 10 || strlen($noTelp) > 15) {
            $errors['no_telp'] = 'Nomor telepon harus 10-15 digit.';
        }

        // Email — opsional, tapi jika diisi harus valid
        $email = trim($data['email'] ?? '');
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }

        // Alamat — opsional, max 255 char jika diisi
        $alamat = trim($data['alamat'] ?? '');
        if (!empty($alamat) && mb_strlen($alamat) > 255) {
            $errors['alamat'] = 'Alamat maksimal 255 karakter.';
        }

        return $errors;
    }
}
```

### Step C.2 — Buat PelangganController

Buat file: `app/Controllers/PelangganController.php`

```php
<?php

require_once __DIR__ . '/../Models/PelangganModel.php';
require_once __DIR__ . '/../Helpers/auth.php';

class PelangganController
{
    private PelangganModel $model;

    public function __construct()
    {
        $this->model = new PelangganModel();
    }

    // ----------------------------------------------------------------
    // READ — Daftar semua pelanggan aktif
    // ----------------------------------------------------------------
    public function index(): void
    {
        requireAuth();

        $data = [
            'title'      => 'Data Pelanggan',
            'pelanggan'  => $this->model->all(),
            'flash'      => $this->getFlash(),
        ];

        $this->render('pelanggan/index', $data);
    }

    // ----------------------------------------------------------------
    // CREATE — Form tambah pelanggan
    // ----------------------------------------------------------------
    public function create(): void
    {
        requireAuth();

        $data = [
            'title'  => 'Tambah Pelanggan',
            'errors' => [],
            'old'    => [],
        ];

        $this->render('pelanggan/create', $data);
    }

    // ----------------------------------------------------------------
    // STORE — Proses simpan pelanggan baru
    // ----------------------------------------------------------------
    public function store(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pelanggan');
            return;
        }

        if (!validate_csrf()) {
            $this->redirect('/pelanggan', 'flash_error', 'Token CSRF tidak valid. Silakan coba lagi.');
            return;
        }

        $input = [
            'nama_pelanggan' => htmlspecialchars(trim($_POST['nama_pelanggan'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'no_telp'        => htmlspecialchars(trim($_POST['no_telp'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'alamat'         => htmlspecialchars(trim($_POST['alamat'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'email'          => htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'),
        ];

        // Konversi empty string ke null untuk field opsional
        $input['alamat'] = $input['alamat'] !== '' ? $input['alamat'] : null;
        $input['email']  = $input['email']  !== '' ? $input['email']  : null;

        $errors = $this->model->validate($input);

        if (!empty($errors)) {
            // Tampilkan form kembali dengan error dan old input
            $data = [
                'title'  => 'Tambah Pelanggan',
                'errors' => $errors,
                'old'    => $input,
            ];
            $this->render('pelanggan/create', $data);
            return;
        }

        $this->model->create($input);
        $this->redirect('/pelanggan', 'flash_success', 'Pelanggan berhasil ditambahkan.');
    }

    // ----------------------------------------------------------------
    // EDIT — Form edit pelanggan
    // ----------------------------------------------------------------
    public function edit(int $id): void
    {
        requireAuth();

        $pelanggan = $this->model->findById($id);

        if (!$pelanggan) {
            $this->redirect('/pelanggan', 'flash_error', 'Pelanggan tidak ditemukan.');
            return;
        }

        $data = [
            'title'      => 'Edit Pelanggan',
            'pelanggan'  => $pelanggan,
            'errors'     => [],
        ];

        $this->render('pelanggan/edit', $data);
    }

    // ----------------------------------------------------------------
    // UPDATE — Proses update data pelanggan
    // ----------------------------------------------------------------
    public function update(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pelanggan');
            return;
        }

        if (!validate_csrf()) {
            $this->redirect('/pelanggan', 'flash_error', 'Token CSRF tidak valid.');
            return;
        }

        $pelanggan = $this->model->findById($id);
        if (!$pelanggan) {
            $this->redirect('/pelanggan', 'flash_error', 'Pelanggan tidak ditemukan.');
            return;
        }

        $input = [
            'nama_pelanggan' => htmlspecialchars(trim($_POST['nama_pelanggan'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'no_telp'        => htmlspecialchars(trim($_POST['no_telp'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'alamat'         => htmlspecialchars(trim($_POST['alamat'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'email'          => htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'),
        ];

        $input['alamat'] = $input['alamat'] !== '' ? $input['alamat'] : null;
        $input['email']  = $input['email']  !== '' ? $input['email']  : null;

        $errors = $this->model->validate($input, true);

        if (!empty($errors)) {
            $data = [
                'title'     => 'Edit Pelanggan',
                'pelanggan' => array_merge($pelanggan, $input),
                'errors'    => $errors,
            ];
            $this->render('pelanggan/edit', $data);
            return;
        }

        $this->model->update($id, $input);
        $this->redirect('/pelanggan', 'flash_success', 'Data pelanggan berhasil diperbarui.');
    }

    // ----------------------------------------------------------------
    // DELETE — Soft delete pelanggan
    // ----------------------------------------------------------------
    public function delete(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pelanggan');
            return;
        }

        if (!validate_csrf()) {
            $this->redirect('/pelanggan', 'flash_error', 'Token CSRF tidak valid.');
            return;
        }

        $pelanggan = $this->model->findById($id);
        if (!$pelanggan) {
            $this->redirect('/pelanggan', 'flash_error', 'Pelanggan tidak ditemukan.');
            return;
        }

        $this->model->softDelete($id);
        $this->redirect('/pelanggan', 'flash_success', "Pelanggan \"{$pelanggan['nama_pelanggan']}\" berhasil dihapus.");
    }

    // ----------------------------------------------------------------
    // ARCHIVE — Daftar pelanggan yang sudah dihapus
    // ----------------------------------------------------------------
    public function archive(): void
    {
        requireAuth();

        $data = [
            'title'     => 'Arsip Pelanggan',
            'pelanggan' => $this->model->archived(),
            'flash'     => $this->getFlash(),
        ];

        $this->render('pelanggan/archive', $data);
    }

    // ----------------------------------------------------------------
    // RESTORE — Pulihkan pelanggan dari arsip
    // ----------------------------------------------------------------
    public function restore(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pelanggan/archive');
            return;
        }

        if (!validate_csrf()) {
            $this->redirect('/pelanggan/archive', 'flash_error', 'Token CSRF tidak valid.');
            return;
        }

        $this->model->restore($id);
        $this->redirect('/pelanggan', 'flash_success', 'Pelanggan berhasil dipulihkan.');
    }

    // ----------------------------------------------------------------
    // Private Helpers
    // ----------------------------------------------------------------

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $contentView = __DIR__ . "/../Views/{$view}.php";
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    private function redirect(string $path, string $flashKey = '', string $flashMsg = ''): void
    {
        if ($flashKey && $flashMsg) {
            $_SESSION[$flashKey] = $flashMsg;
        }
        header('Location: ' . $path);
        exit;
    }

    private function getFlash(): array
    {
        $flash = [];
        foreach (['flash_success', 'flash_error', 'flash_info'] as $key) {
            if (isset($_SESSION[$key])) {
                $flash[$key] = $_SESSION[$key];
                unset($_SESSION[$key]);
            }
        }
        return $flash;
    }
}
```

### Step C.3 — Buat View: Daftar Pelanggan

Buat file: `app/Views/pelanggan/index.php`

```php
<?php // app/Views/pelanggan/index.php
// Layout main.php akan include file ini via $contentView
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Data Pelanggan</h1>
        <p class="page-subtitle">Kelola data pelanggan laundry Anda</p>
    </div>
    <a href="/pelanggan/create" class="btn btn-primary">
        <i class="ph-bold ph-user-plus"></i>
        Tambah Pelanggan
    </a>
</div>

<?php if (!empty($flash['flash_success'])): ?>
    <div class="alert alert-success">
        <i class="ph ph-check-circle"></i>
        <?= htmlspecialchars($flash['flash_success'], ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (!empty($flash['flash_error'])): ?>
    <div class="alert alert-error">
        <i class="ph ph-x-circle"></i>
        <?= htmlspecialchars($flash['flash_error'], ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">
            <i class="ph ph-users"></i>
            Daftar Pelanggan Aktif
        </span>
        <a href="/pelanggan/archive" class="btn btn-ghost btn-sm">
            <i class="ph ph-archive"></i>
            Lihat Arsip
        </a>
    </div>

    <?php if (empty($pelanggan)): ?>
        <div class="empty-state">
            <i class="ph ph-users" style="font-size: 3rem; opacity: 0.3;"></i>
            <p>Belum ada data pelanggan.</p>
            <a href="/pelanggan/create" class="btn btn-primary btn-sm">Tambah Pelanggan Pertama</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Pelanggan</th>
                        <th>No. Telepon</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pelanggan as $index => $p): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($p['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($p['no_telp'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($p['email']): ?>
                                    <?= htmlspecialchars($p['email'], ENT_QUOTES, 'UTF-8') ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['alamat']): ?>
                                    <?= htmlspecialchars(mb_substr($p['alamat'], 0, 40) . (mb_strlen($p['alamat']) > 40 ? '...' : ''), ENT_QUOTES, 'UTF-8') ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="/pelanggan/edit/<?= (int)$p['id'] ?>"
                                       class="btn btn-warning btn-sm"
                                       title="Edit Pelanggan">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </a>

                                    <!-- Tombol Hapus: buka modal konfirmasi -->
                                    <button type="button"
                                            class="btn btn-danger btn-sm"
                                            title="Hapus Pelanggan"
                                            onclick="openDeleteModal(
                                                <?= (int)$p['id'] ?>,
                                                '<?= htmlspecialchars(addslashes($p['nama_pelanggan']), ENT_QUOTES, 'UTF-8') ?>',
                                                '/pelanggan/delete/<?= (int)$p['id'] ?>'
                                            )">
                                        <i class="ph-bold ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
```

### Step C.4 — Buat View: Form Tambah Pelanggan

Buat file: `app/Views/pelanggan/create.php`

```php
<?php // app/Views/pelanggan/create.php ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Pelanggan</h1>
        <p class="page-subtitle">Isi data pelanggan baru</p>
    </div>
    <a href="/pelanggan" class="btn btn-ghost">
        <i class="ph ph-arrow-left"></i>
        Kembali
    </a>
</div>

<div class="card" style="max-width: 680px;">
    <div class="card-header">
        <span class="card-title">
            <i class="ph ph-user-plus"></i>
            Form Data Pelanggan
        </span>
    </div>
    <div class="card-body">
        <form method="POST" action="/pelanggan/store" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

            <!-- Nama Pelanggan -->
            <div class="form-group <?= !empty($errors['nama_pelanggan']) ? 'has-error' : '' ?>">
                <label class="form-label" for="nama_pelanggan">
                    Nama Pelanggan <span class="required">*</span>
                </label>
                <input type="text"
                       id="nama_pelanggan"
                       name="nama_pelanggan"
                       class="form-control"
                       value="<?= htmlspecialchars($old['nama_pelanggan'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Contoh: Budi Santoso"
                       maxlength="100"
                       required>
                <?php if (!empty($errors['nama_pelanggan'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <!-- No. Telepon -->
            <div class="form-group <?= !empty($errors['no_telp']) ? 'has-error' : '' ?>">
                <label class="form-label" for="no_telp">
                    No. Telepon <span class="required">*</span>
                </label>
                <input type="tel"
                       id="no_telp"
                       name="no_telp"
                       class="form-control"
                       value="<?= htmlspecialchars($old['no_telp'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Contoh: 081234567890"
                       maxlength="15"
                       required>
                <?php if (!empty($errors['no_telp'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['no_telp'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="form-group <?= !empty($errors['email']) ? 'has-error' : '' ?>">
                <label class="form-label" for="email">Email <span class="text-muted">(opsional)</span></label>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control"
                       value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Contoh: budi@email.com">
                <?php if (!empty($errors['email'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <!-- Alamat -->
            <div class="form-group <?= !empty($errors['alamat']) ? 'has-error' : '' ?>">
                <label class="form-label" for="alamat">Alamat <span class="text-muted">(opsional)</span></label>
                <textarea id="alamat"
                          name="alamat"
                          class="form-control"
                          rows="3"
                          placeholder="Contoh: Jl. Merdeka No. 1, Jakarta"><?= htmlspecialchars($old['alamat'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php if (!empty($errors['alamat'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['alamat'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a href="/pelanggan" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary"
                        onclick="this.disabled=true; this.innerHTML='<i class=\'ph ph-spinner\' style=\'animation:spin 0.8s linear infinite;\'></i> Menyimpan...'; this.form.submit();">
                    <i class="ph-bold ph-floppy-disk"></i>
                    Simpan Pelanggan
                </button>
            </div>
        </form>
    </div>
</div>
```

### Step C.5 — Buat View: Form Edit Pelanggan

Buat file: `app/Views/pelanggan/edit.php`

```php
<?php // app/Views/pelanggan/edit.php ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Pelanggan</h1>
        <p class="page-subtitle">Perbarui data pelanggan</p>
    </div>
    <a href="/pelanggan" class="btn btn-ghost">
        <i class="ph ph-arrow-left"></i>
        Kembali
    </a>
</div>

<div class="card" style="max-width: 680px;">
    <div class="card-header">
        <span class="card-title">
            <i class="ph ph-pencil-simple"></i>
            Form Edit Pelanggan
        </span>
    </div>
    <div class="card-body">
        <form method="POST" action="/pelanggan/update/<?= (int)$pelanggan['id'] ?>" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group <?= !empty($errors['nama_pelanggan']) ? 'has-error' : '' ?>">
                <label class="form-label" for="nama_pelanggan">
                    Nama Pelanggan <span class="required">*</span>
                </label>
                <input type="text" id="nama_pelanggan" name="nama_pelanggan" class="form-control"
                       value="<?= htmlspecialchars($pelanggan['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?>"
                       maxlength="100" required>
                <?php if (!empty($errors['nama_pelanggan'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group <?= !empty($errors['no_telp']) ? 'has-error' : '' ?>">
                <label class="form-label" for="no_telp">
                    No. Telepon <span class="required">*</span>
                </label>
                <input type="tel" id="no_telp" name="no_telp" class="form-control"
                       value="<?= htmlspecialchars($pelanggan['no_telp'], ENT_QUOTES, 'UTF-8') ?>"
                       maxlength="15" required>
                <?php if (!empty($errors['no_telp'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['no_telp'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group <?= !empty($errors['email']) ? 'has-error' : '' ?>">
                <label class="form-label" for="email">Email <span class="text-muted">(opsional)</span></label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($pelanggan['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['email'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group <?= !empty($errors['alamat']) ? 'has-error' : '' ?>">
                <label class="form-label" for="alamat">Alamat <span class="text-muted">(opsional)</span></label>
                <textarea id="alamat" name="alamat" class="form-control" rows="3"><?= htmlspecialchars($pelanggan['alamat'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php if (!empty($errors['alamat'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['alamat'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a href="/pelanggan" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary"
                        onclick="this.disabled=true; this.innerHTML='<i class=\'ph ph-spinner\' style=\'animation:spin 0.8s linear infinite;\'></i> Memperbarui...'; this.form.submit();">
                    <i class="ph-bold ph-floppy-disk"></i>
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>
</div>
```

### Step C.6 — Buat View: Arsip Pelanggan

Buat file: `app/Views/pelanggan/archive.php`

```php
<?php // app/Views/pelanggan/archive.php ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Arsip Pelanggan</h1>
        <p class="page-subtitle">Pelanggan yang telah dihapus</p>
    </div>
    <a href="/pelanggan" class="btn btn-ghost">
        <i class="ph ph-arrow-left"></i>
        Kembali ke Daftar
    </a>
</div>

<?php if (!empty($flash['flash_success'])): ?>
    <div class="alert alert-success">
        <i class="ph ph-check-circle"></i>
        <?= htmlspecialchars($flash['flash_success'], ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">
            <i class="ph ph-archive"></i>
            Pelanggan Diarsipkan
        </span>
    </div>

    <?php if (empty($pelanggan)): ?>
        <div class="empty-state">
            <i class="ph ph-archive" style="font-size: 3rem; opacity: 0.3;"></i>
            <p>Tidak ada pelanggan yang diarsipkan.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Pelanggan</th>
                        <th>No. Telepon</th>
                        <th>Dihapus Pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pelanggan as $index => $p): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($p['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($p['no_telp'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= date('d M Y H:i', strtotime($p['deleted_at'])) ?></td>
                            <td>
                                <form method="POST"
                                      action="/pelanggan/restore/<?= (int)$p['id'] ?>"
                                      style="display:inline;">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-success btn-sm"
                                            onclick="this.disabled=true; this.form.submit();">
                                        <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                        Pulihkan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
```

### Step C.7 — Tambahkan Routes Pelanggan ke `app/Config/Routes.php`

Buka `app/Config/Routes.php`, tambahkan di bagian routes (setelah routes layanan yang sudah ada):

```php
// ================================================================
// Pelanggan Routes — SOAL 01 (tambahan v2.0)
// ================================================================
$routes->get('/pelanggan',                   'PelangganController::index');
$routes->get('/pelanggan/create',            'PelangganController::create');
$routes->post('/pelanggan/store',            'PelangganController::store');
$routes->get('/pelanggan/edit/(:num)',       'PelangganController::edit/$1');
$routes->post('/pelanggan/update/(:num)',    'PelangganController::update/$1');
$routes->post('/pelanggan/delete/(:num)',    'PelangganController::delete/$1');
$routes->get('/pelanggan/archive',           'PelangganController::archive');
$routes->post('/pelanggan/restore/(:num)',   'PelangganController::restore/$1');
```

**PENTING:** Jika menggunakan custom `index.php` router (bukan CI4 Routes), tambahkan juga di routing block `index.php`:

```php
// Di dalam index.php — tambahkan setelah block layanan
if ($segment1 === 'pelanggan') {
    require_once __DIR__ . '/app/Controllers/PelangganController.php';
    $c = new PelangganController();
    $id = !empty($segment3) && is_numeric($segment3) ? (int)$segment3 : null;

    switch ($segment2) {
        case '':       $c->index();   break;
        case 'create': $c->create();  break;
        case 'store':  $c->store();   break;
        case 'archive': $c->archive(); break;
        case 'edit':   $id ? $c->edit($id)   : header('Location: /pelanggan'); break;
        case 'update': $id ? $c->update($id) : header('Location: /pelanggan'); break;
        case 'delete': $id ? $c->delete($id) : header('Location: /pelanggan'); break;
        case 'restore': $id ? $c->restore($id) : header('Location: /pelanggan/archive'); break;
        default: http_response_code(404); echo '<h1>404</h1>';
    }
    exit;
}
```

### Step C.8 — Tambahkan Link Pelanggan ke Sidebar

Buka `app/Views/layouts/main.php`. Cari bagian sidebar navigation (ada link ke `/dashboard` dan `/layanan`). Tambahkan link pelanggan di bawah layanan:

```html
<!-- Tambahkan ini setelah link layanan di sidebar -->
<a
  href="/pelanggan"
  class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/pelanggan') ? 'active' : '' ?>"
>
  <i class="ph ph-users"></i>
  <span>Pelanggan</span>
</a>
```

### Step C.9 — Verifikasi Checkpoint Phase C

- [ ] `http://localhost/laundry-in/public/pelanggan` — tampil daftar 5 pelanggan
- [ ] Klik "Tambah Pelanggan" — form muncul
- [ ] Submit form kosong — error validasi muncul per field
- [ ] Submit form lengkap — pelanggan tersimpan, redirect ke list dengan flash success
- [ ] Klik Edit — form pre-filled dengan data pelanggan
- [ ] Update data — perubahan tersimpan
- [ ] Klik Hapus — modal konfirmasi muncul
- [ ] Konfirmasi hapus — pelanggan hilang dari list, muncul di `/pelanggan/archive`
- [ ] Klik Pulihkan — pelanggan kembali ke list aktif

---

## Phase D — Login dari Database: Verifikasi & Hardening (SOAL 03)

**Tujuan:** Verifikasi bahwa login sudah menggunakan database (sudah ada sejak v1.0), dan tambahkan hardening untuk keamanan lebih baik.

**Estimasi waktu:** 10 menit
**File yang diubah:** `app/Controllers/AuthController.php`, `app/Helpers/auth.php`
**File yang TIDAK diubah:** View login, AdminModel

### Step D.1 — Verifikasi Login Sudah dari Database

Buka `app/Controllers/AuthController.php`. Pastikan method `processLogin()` menggunakan `AdminModel::findByUsername()` dan `password_verify()`:

```php
// Verifikasi method ini sudah ada dan benar:
public function processLogin(): void
{
    // ... CSRF validation ...

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Wajib: Ambil dari DATABASE, bukan array statis
    require_once __DIR__ . '/../Models/AdminModel.php';
    $adminModel = new AdminModel();
    $admin = $adminModel->findByUsername($username);

    // Wajib: Verifikasi bcrypt, error message generic
    if (!$admin || !password_verify($password, $admin['password'])) {
        $_SESSION['flash_error'] = 'Username atau password salah.';
        header('Location: /login');
        exit;
    }

    // Wajib: Regenerate session ID setelah login berhasil
    session_regenerate_id(true);

    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['logged_in']      = true;

    header('Location: /dashboard');
    exit;
}
```

Jika sudah seperti ini, SOAL 03 sudah terpenuhi. Tidak perlu mengubah apapun.

### Step D.2 — Tambah Login Rate Limiting (Hardening Opsional tapi Nilai Lebih)

Tambahkan simple rate limiting di `app/Helpers/auth.php` (tambahkan function baru, JANGAN ubah yang sudah ada):

```php
/**
 * Simple session-based rate limiting untuk login form.
 * Mencegah brute-force attack.
 *
 * @param int $maxAttempts Maksimal percobaan gagal
 * @param int $windowSeconds Waktu window dalam detik
 * @return bool true = masih boleh login, false = terkunci sementara
 */
function checkLoginRateLimit(int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    $now = time();
    $attempts = $_SESSION['login_attempts'] ?? [];

    // Hapus attempt yang sudah melewati window time
    $attempts = array_filter($attempts, fn($time) => ($now - $time) < $windowSeconds);

    if (count($attempts) >= $maxAttempts) {
        return false; // Terkunci
    }

    return true;
}

/**
 * Catat satu attempt login gagal
 */
function recordFailedLoginAttempt(): void
{
    $_SESSION['login_attempts']   = $_SESSION['login_attempts'] ?? [];
    $_SESSION['login_attempts'][] = time();
}

/**
 * Reset login attempts setelah login berhasil
 */
function resetLoginAttempts(): void
{
    unset($_SESSION['login_attempts']);
}
```

Kemudian di `AuthController::processLogin()`, gunakan functions ini:

```php
// Di awal processLogin(), sebelum cek credentials:
if (!checkLoginRateLimit()) {
    $_SESSION['flash_error'] = 'Terlalu banyak percobaan login. Coba lagi dalam 5 menit.';
    header('Location: /login');
    exit;
}

// Jika login GAGAL:
recordFailedLoginAttempt();
$_SESSION['flash_error'] = 'Username atau password salah.';
header('Location: /login');
exit;

// Jika login BERHASIL:
resetLoginAttempts();
session_regenerate_id(true);
// ... sisa kode ...
```

### Step D.3 — Verifikasi Checkpoint Phase D

- [ ] Login dengan `admin / admin123` → berhasil masuk dashboard
- [ ] Login dengan `admin / salah` → "Username atau password salah." (BUKAN detail error)
- [ ] Login dengan username yang tidak ada → pesan error yang sama
- [ ] Gagal login 5x berturut-turut → "Terlalu banyak percobaan login..."
- [ ] Setelah login berhasil, session ID berbeda dari sebelum login (buka DevTools → Application → Cookies)

---

## Phase E — Export PDF dengan Dompdf (SOAL 04)

**Tujuan:** Menggunakan library Dompdf untuk mengekspor halaman daftar layanan ke PDF.

**Estimasi waktu:** 25 menit
**File yang dibuat:** `app/Views/layanan/pdf.php`, method baru di `LayananController.php`
**File yang diubah:** `composer.json`, `app/Config/Routes.php` atau `index.php`, `app/Views/layanan/index.php`
**File yang TIDAK diubah:** Semua file lain

### Step E.1 — Install Dompdf via Composer

```bash
# Dari root project laundry-in/
composer require dompdf/dompdf

# Verifikasi:
# composer.json harus ada "dompdf/dompdf": "^2.0" di require
# vendor/dompdf/ harus ada
```

Pastikan `composer.json` ada field:

```json
{
  "require": {
    "dompdf/dompdf": "^2.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  }
}
```

Jika composer.json belum ada atau kosong, buat dulu:

```bash
composer init --no-interaction
composer require dompdf/dompdf
```

### Step E.2 — Pastikan Autoloader Dimuat di Entry Point

Buka `index.php` (entry point yang dipakai, bisa di root atau di `public/index.php`).

Pastikan baris ini ada di paling atas:

```php
// Pastikan vendor/autoload.php sudah di-require
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
```

### Step E.3 — Buat Template HTML untuk PDF

Buat file: `app/Views/layanan/pdf.php`

File ini adalah pure HTML yang akan di-render oleh Dompdf. Tidak menggunakan layout main.php.

```php
<?php // app/Views/layanan/pdf.php
// File ini di-render oleh Dompdf, BUKAN oleh browser langsung
// Tidak ada require layout di sini
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Jenis Layanan — Laundry-IN</title>
    <style>
        /* Dompdf mendukung CSS subset — hindari flexbox/grid */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a2e;
            background: #ffffff;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 20pt;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 10pt;
            color: #64748b;
        }
        .meta-info {
            margin-bottom: 16px;
            font-size: 9pt;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        thead tr {
            background-color: #1e3a8a;
            color: #ffffff;
        }
        thead th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10pt;
            font-weight: bold;
            border: 1px solid #1e3a8a;
        }
        tbody tr:nth-child(even) {
            background-color: #f1f5f9;
        }
        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        tbody td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            font-size: 10pt;
            vertical-align: top;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
        }
        .badge-express {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-reguler {
            background-color: #e0f2fe;
            color: #075985;
        }
        .harga {
            font-weight: bold;
            color: #1e3a8a;
        }
        .footer {
            margin-top: 24px;
            text-align: right;
            font-size: 9pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        .summary {
            margin-bottom: 16px;
            padding: 10px;
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            font-size: 10pt;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laundry-IN</h1>
        <p>Sistem Manajemen Layanan Laundry</p>
        <p>Daftar Jenis Layanan</p>
    </div>

    <div class="meta-info">
        Dicetak pada: <?= date('d F Y, H:i') ?> WIB
        &nbsp;|&nbsp;
        Total: <?= count($layanan) ?> layanan aktif
    </div>

    <div class="summary">
        <strong>Ringkasan:</strong>
        Total Layanan Aktif: <?= count($layanan) ?> |
        Express: <?= count(array_filter($layanan, fn($l) => $l['kategori'] === 'express')) ?> |
        Reguler: <?= count(array_filter($layanan, fn($l) => $l['kategori'] === 'reguler')) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 25%;">Nama Layanan</th>
                <th style="width: 12%;">Kategori</th>
                <th style="width: 15%;">Harga</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 13%;">Estimasi</th>
                <th style="width: 20%;">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($layanan)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Tidak ada data layanan.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($layanan as $index => $item): ?>
                    <tr>
                        <td style="text-align: center;"><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($item['nama_layanan'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="badge badge-<?= $item['kategori'] ?>">
                                <?= ucfirst(htmlspecialchars($item['kategori'], ENT_QUOTES, 'UTF-8')) ?>
                            </span>
                        </td>
                        <td class="harga">
                            Rp <?= number_format((int)$item['harga'], 0, ',', '.') ?>
                        </td>
                        <td><?= htmlspecialchars(strtoupper($item['satuan_harga']), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($item['estimasi_durasi'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= $item['deskripsi']
                                ? htmlspecialchars(mb_substr($item['deskripsi'], 0, 80) . (mb_strlen($item['deskripsi']) > 80 ? '...' : ''), ENT_QUOTES, 'UTF-8')
                                : '<span style="color:#94a3b8;">—</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh sistem Laundry-IN.
        Dicetak: <?= date('d/m/Y H:i') ?>
    </div>

</body>
</html>
```

### Step E.4 — Tambahkan Method `exportPdf()` di LayananController

Buka `app/Controllers/LayananController.php`. Tambahkan method berikut di akhir class (JANGAN ubah method yang sudah ada):

```php
// Tambahkan di akhir class LayananController, sebelum closing brace '}'

// ----------------------------------------------------------------
// EXPORT PDF — SOAL 04 (Dompdf)
// ----------------------------------------------------------------
public function exportPdf(): void
{
    requireAuth();

    // Load autoloader Dompdf
    // Path autoloader relatif terhadap index.php entry point
    $autoloaderPaths = [
        __DIR__ . '/../../vendor/autoload.php',
        __DIR__ . '/../../../vendor/autoload.php',
    ];

    $autoloaderLoaded = false;
    foreach ($autoloaderPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $autoloaderLoaded = true;
            break;
        }
    }

    if (!$autoloaderLoaded) {
        http_response_code(500);
        echo 'Error: Dompdf belum terinstall. Jalankan: composer require dompdf/dompdf';
        return;
    }

    // Ambil data layanan aktif dari model
    $layanan = $this->model->all();

    // Render HTML template ke string
    ob_start();
    extract(['layanan' => $layanan]);
    include __DIR__ . '/../Views/layanan/pdf.php';
    $html = ob_get_clean();

    // Inisialisasi Dompdf
    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', false);  // Security: jangan load remote resources
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');

    // Set ukuran kertas: A4 Landscape
    $dompdf->setPaper('A4', 'landscape');

    // Render PDF
    $dompdf->render();

    // Nama file PDF
    $filename = 'Laundry-IN_Daftar-Layanan_' . date('Y-m-d') . '.pdf';

    // Stream ke browser (false = tampilkan di browser, bukan download)
    // Ubah false ke true jika ingin langsung download
    $dompdf->stream($filename, ['Attachment' => false]);
    exit;
}
```

### Step E.5 — Tambahkan Route untuk Export PDF

Di `app/Config/Routes.php`:

```php
$routes->get('/layanan/export-pdf', 'LayananController::exportPdf');
```

Di custom `index.php` router (jika masih dipakai), tambahkan di switch case layanan:

```php
case 'export-pdf':
    $c->exportPdf();
    break;
```

**PENTING:** Route `export-pdf` harus didefinisikan SEBELUM route `(:num)` agar tidak dianggap sebagai ID. Urutan di switch case juga penting.

### Step E.6 — Tambahkan Tombol Export di View Layanan

Buka `app/Views/layanan/index.php`. Cari tombol "Tambah Layanan Baru" di page header. Tambahkan tombol Export PDF di sebelahnya:

```html
<!-- Tambahkan ini di page-header, sebelum atau sesudah tombol "Tambah Layanan" -->
<a
  href="/layanan/export-pdf"
  class="btn btn-secondary"
  target="_blank"
  title="Buka PDF di tab baru"
>
  <i class="ph-bold ph-file-pdf"></i>
  Export PDF
</a>
```

### Step E.7 — Verifikasi Checkpoint Phase E

```bash
# Pastikan Dompdf terinstall
ls vendor/dompdf/
```

- [ ] `vendor/dompdf/dompdf/` folder ada
- [ ] Tombol "Export PDF" muncul di halaman `/layanan`
- [ ] Klik "Export PDF" → PDF terbuka di tab baru
- [ ] PDF menampilkan tabel data layanan yang benar
- [ ] Header PDF menampilkan "Laundry-IN"
- [ ] Tanggal cetak akurat
- [ ] Format kertas A4 Landscape
- [ ] Tidak ada error PHP di console/log

---

## Phase F — Shopping Cart Library (SOAL 05)

**Tujuan:** Membuat library Cart berbasis session dengan method: `insert()`, `update()`, `total()`, `remove()`, `destroy()`.

**Estimasi waktu:** 30 menit
**File yang dibuat:** `app/Libraries/Cart.php`, `app/Controllers/CartController.php`, `app/Views/cart/index.php`
**File yang diubah:** Routes, sidebar, `app/Views/layanan/index.php` (tambah tombol add to cart)
**File yang TIDAK diubah:** CartController tidak ada di v1.0, jadi ini file baru semua

### Step F.1 — Buat Cart Library

Buat file: `app/Libraries/Cart.php`

```php
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
    // INSERT — Tambah item ke cart
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
    // UPDATE — Ubah quantity item di cart
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
    // TOTAL — Hitung total harga semua item di cart
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
    // REMOVE — Hapus satu item dari cart berdasarkan ID
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
    // DESTROY — Kosongkan seluruh isi cart
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
    // Helper Methods — tidak diminta soal tapi perlu untuk view
    // ----------------------------------------------------------------

    /**
     * Ambil semua item di cart sebagai array.
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
```

### Step F.2 — Buat CartController

Buat file: `app/Controllers/CartController.php`

```php
<?php

require_once __DIR__ . '/../Libraries/Cart.php';
require_once __DIR__ . '/../Models/LayananModel.php';
require_once __DIR__ . '/../Helpers/auth.php';

class CartController
{
    private Cart         $cart;
    private LayananModel $layananModel;

    public function __construct()
    {
        $this->cart         = new Cart();
        $this->layananModel = new LayananModel();
    }

    // ----------------------------------------------------------------
    // INDEX — Halaman tampil isi cart
    // ----------------------------------------------------------------
    public function index(): void
    {
        requireAuth();

        $data = [
            'title'  => 'Keranjang Belanja',
            'items'  => $this->cart->getItems(),
            'total'  => $this->cart->total(),
            'count'  => $this->cart->count(),
            'flash'  => $this->getFlash(),
        ];

        $this->render('cart/index', $data);
    }

    // ----------------------------------------------------------------
    // ADD — Tambah item ke cart (dari halaman layanan)
    // ----------------------------------------------------------------
    public function add(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/layanan');
            return;
        }

        if (!validate_csrf()) {
            $this->redirect('/layanan', 'flash_error', 'Token CSRF tidak valid.');
            return;
        }

        // Ambil data layanan dari database untuk memastikan valid
        $layanan = $this->layananModel->findById($id);

        if (!$layanan) {
            $this->redirect('/layanan', 'flash_error', 'Layanan tidak ditemukan.');
            return;
        }

        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        // Panggil Cart::insert()
        $this->cart->insert($id, [
            'nama_layanan' => $layanan['nama_layanan'],
            'harga'        => (int)$layanan['harga'],
            'satuan_harga' => $layanan['satuan_harga'],
            'quantity'     => $qty,
        ]);

        $this->redirect('/cart', 'flash_success', "\"" . $layanan['nama_layanan'] . "\" ditambahkan ke keranjang.");
    }

    // ----------------------------------------------------------------
    // UPDATE — Ubah quantity item di cart
    // ----------------------------------------------------------------
    public function update(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        if (!validate_csrf()) {
            $this->redirect('/cart', 'flash_error', 'Token CSRF tidak valid.');
            return;
        }

        $qty = (int)($_POST['quantity'] ?? 0);

        // Panggil Cart::update()
        $this->cart->update($id, $qty);

        if ($qty <= 0) {
            $this->redirect('/cart', 'flash_success', 'Item dihapus dari keranjang.');
        } else {
            $this->redirect('/cart', 'flash_success', 'Jumlah item diperbarui.');
        }
    }

    // ----------------------------------------------------------------
    // REMOVE — Hapus satu item dari cart
    // ----------------------------------------------------------------
    public function remove(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        if (!validate_csrf()) {
            $this->redirect('/cart', 'flash_error', 'Token CSRF tidak valid.');
            return;
        }

        // Panggil Cart::remove()
        $this->cart->remove($id);

        $this->redirect('/cart', 'flash_success', 'Item dihapus dari keranjang.');
    }

    // ----------------------------------------------------------------
    // DESTROY — Kosongkan seluruh cart
    // ----------------------------------------------------------------
    public function destroy(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        if (!validate_csrf()) {
            $this->redirect('/cart', 'flash_error', 'Token CSRF tidak valid.');
            return;
        }

        // Panggil Cart::destroy()
        $this->cart->destroy();

        $this->redirect('/cart', 'flash_success', 'Keranjang berhasil dikosongkan.');
    }

    // ----------------------------------------------------------------
    // Private Helpers
    // ----------------------------------------------------------------

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $contentView = __DIR__ . "/../Views/{$view}.php";
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    private function redirect(string $path, string $flashKey = '', string $flashMsg = ''): void
    {
        if ($flashKey && $flashMsg) {
            $_SESSION[$flashKey] = $flashMsg;
        }
        header('Location: ' . $path);
        exit;
    }

    private function getFlash(): array
    {
        $flash = [];
        foreach (['flash_success', 'flash_error'] as $key) {
            if (isset($_SESSION[$key])) {
                $flash[$key] = $_SESSION[$key];
                unset($_SESSION[$key]);
            }
        }
        return $flash;
    }
}
```

### Step F.3 — Buat View: Halaman Cart

Buat file: `app/Views/cart/index.php`

```php
<?php // app/Views/cart/index.php ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Keranjang Belanja</h1>
        <p class="page-subtitle">
            <?= $count > 0 ? "{$count} item dalam keranjang" : 'Keranjang kosong' ?>
        </p>
    </div>
    <a href="/layanan" class="btn btn-ghost">
        <i class="ph ph-arrow-left"></i>
        Lanjut Belanja
    </a>
</div>

<?php if (!empty($flash['flash_success'])): ?>
    <div class="alert alert-success">
        <i class="ph ph-check-circle"></i>
        <?= htmlspecialchars($flash['flash_success'], ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (!empty($flash['flash_error'])): ?>
    <div class="alert alert-error">
        <i class="ph ph-x-circle"></i>
        <?= htmlspecialchars($flash['flash_error'], ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (empty($items)): ?>
    <!-- Empty cart state -->
    <div class="card">
        <div class="empty-state" style="padding: 60px 20px;">
            <i class="ph ph-shopping-cart" style="font-size: 4rem; opacity: 0.2;"></i>
            <h3 style="margin: 16px 0 8px;">Keranjang Masih Kosong</h3>
            <p style="color: var(--text-muted);">Tambahkan layanan dari halaman Daftar Layanan.</p>
            <a href="/layanan" class="btn btn-primary" style="margin-top: 16px;">
                <i class="ph ph-plus"></i>
                Tambah Layanan
            </a>
        </div>
    </div>

<?php else: ?>
    <!-- Cart items table -->
    <div class="card" style="margin-bottom: 16px;">
        <div class="card-header">
            <span class="card-title">
                <i class="ph ph-shopping-cart"></i>
                Item dalam Keranjang
            </span>

            <!-- Tombol Kosongkan Semua (destroy) -->
            <form method="POST" action="/cart/destroy" style="display:inline;">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-danger btn-sm"
                        onclick="if(!confirm('Kosongkan semua keranjang?')) return false; this.disabled=true; this.form.submit();">
                    <i class="ph-bold ph-trash"></i>
                    Kosongkan Semua
                </button>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Layanan</th>
                        <th>Harga Satuan</th>
                        <th>Satuan</th>
                        <th style="width: 130px;">Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($item['nama_layanan'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>Rp <?= number_format((int)$item['harga'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars(strtoupper($item['satuan_harga']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <!-- Form untuk update quantity -->
                                <form method="POST"
                                      action="/cart/update/<?= (int)$item['id_layanan'] ?>"
                                      style="display:flex; align-items:center; gap:6px;">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="number"
                                           name="quantity"
                                           value="<?= (int)$item['quantity'] ?>"
                                           min="0"
                                           max="99"
                                           class="form-control"
                                           style="width:60px; padding:4px 8px; text-align:center;"
                                           onchange="this.form.submit()">
                                </form>
                            </td>
                            <td style="font-weight: bold;">
                                Rp <?= number_format((int)$item['subtotal'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <!-- Form untuk remove item -->
                                <form method="POST"
                                      action="/cart/remove/<?= (int)$item['id_layanan'] ?>"
                                      style="display:inline;">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="this.disabled=true; this.form.submit();"
                                            title="Hapus dari keranjang">
                                        <i class="ph-bold ph-x"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: bold; padding: 12px 8px;">
                            TOTAL KESELURUHAN:
                        </td>
                        <td style="font-weight: bold; font-size: 1.1rem; color: var(--color-primary);">
                            Rp <?= number_format($total, 0, ',', '.') ?>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="card" style="max-width: 400px; margin-left: auto;">
        <div class="card-header">
            <span class="card-title">
                <i class="ph ph-receipt"></i>
                Ringkasan Pesanan
            </span>
        </div>
        <div class="card-body">
            <table style="width: 100%;">
                <tr>
                    <td>Total Item</td>
                    <td style="text-align:right;"><?= $count ?> item</td>
                </tr>
                <tr>
                    <td>Total Layanan</td>
                    <td style="text-align:right;"><?= count($items) ?> jenis</td>
                </tr>
                <tr style="border-top: 2px solid var(--border-color); font-weight:bold; font-size:1.05rem;">
                    <td style="padding-top:10px;">Total Harga</td>
                    <td style="text-align:right; padding-top:10px; color:var(--color-primary);">
                        Rp <?= number_format($total, 0, ',', '.') ?>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 16px;">
                <button type="button" class="btn btn-primary" style="width:100%;"
                        onclick="alert('Fitur checkout belum tersedia di versi ini.')">
                    <i class="ph-bold ph-check-circle"></i>
                    Pesan Sekarang
                </button>
            </div>
        </div>
    </div>

<?php endif; ?>
```

### Step F.4 — Tambahkan Tombol "Tambah ke Cart" di Halaman Layanan

Buka `app/Views/layanan/index.php`. Di setiap baris tabel, tambahkan tombol "Tambah ke Cart" di kolom Aksi:

```html
<!-- Tambahkan sebelum atau sesudah tombol Edit dan Hapus yang sudah ada -->
<form
  method="POST"
  action="/cart/add/<?= (int)$item['id'] ?>"
  style="display:inline;"
>
  <input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
  />
  <input type="hidden" name="quantity" value="1" />
  <button
    type="submit"
    class="btn btn-success btn-sm"
    title="Tambah ke Keranjang"
  >
    <i class="ph-bold ph-shopping-cart-simple"></i>
  </button>
</form>
```

### Step F.5 — Tambahkan Badge Cart Counter di Sidebar

Buka `app/Views/layouts/main.php`. Di bagian sidebar, tambahkan link ke cart dengan badge:

```html
<!-- Tambahkan di sidebar navigation, setelah link Pelanggan -->
<?php require_once __DIR__ . '/../../Libraries/Cart.php'; $cartInstance = new
Cart(); $cartCount = $cartInstance->count(); ?>
<a
  href="/cart"
  class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/cart') ? 'active' : '' ?>"
>
  <i class="ph ph-shopping-cart"></i>
  <span>Keranjang</span>
  <?php if ($cartCount > 0): ?>
  <span class="badge-counter"><?= $cartCount ?></span>
  <?php endif; ?>
</a>
```

Tambahkan CSS untuk badge counter di `assets/css/components.css` (atau `utilities.css`):

```css
/* Cart badge counter di sidebar */
.badge-counter {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  border-radius: 10px;
  background: var(--color-danger, #ef4444);
  color: #ffffff;
  font-size: 0.7rem;
  font-weight: 700;
  margin-left: auto;
}
```

### Step F.6 — Tambahkan Routes untuk Cart

Di `app/Config/Routes.php`:

```php
// ================================================================
// Cart Routes — SOAL 05 (v2.0)
// ================================================================
$routes->get('/cart',                    'CartController::index');
$routes->post('/cart/add/(:num)',        'CartController::add/$1');
$routes->post('/cart/update/(:num)',     'CartController::update/$1');
$routes->post('/cart/remove/(:num)',     'CartController::remove/$1');
$routes->post('/cart/destroy',          'CartController::destroy');
```

Di custom `index.php` router (jika masih dipakai):

```php
if ($segment1 === 'cart') {
    require_once __DIR__ . '/app/Controllers/CartController.php';
    $c = new CartController();
    $id = !empty($segment3) && is_numeric($segment3) ? (int)$segment3 : null;

    switch ($segment2) {
        case '':        $c->index();   break;
        case 'add':     $id ? $c->add($id)    : header('Location: /layanan'); break;
        case 'update':  $id ? $c->update($id) : header('Location: /cart');    break;
        case 'remove':  $id ? $c->remove($id) : header('Location: /cart');    break;
        case 'destroy': $c->destroy(); break;
        default: http_response_code(404); echo '<h1>404</h1>';
    }
    exit;
}
```

### Step F.7 — Verifikasi Checkpoint Phase F

- [ ] `http://localhost/laundry-in/public/cart` — halaman cart tampil (kosong awalnya)
- [ ] Tombol "Tambah ke Cart" ada di halaman layanan
- [ ] Klik tombol tersebut → item masuk ke cart, redirect ke /cart
- [ ] `insert()`: Item tampil di tabel cart dengan quantity 1
- [ ] Klik tombol yang sama lagi → quantity bertambah (bukan duplicate row)
- [ ] `update()`: Ubah angka di kolom "Jumlah" → subtotal berubah otomatis
- [ ] `update()` dengan angka 0 → item terhapus dari cart
- [ ] `remove()`: Tombol × → item hilang dari cart
- [ ] `total()`: Total harga di bawah tabel akurat
- [ ] `destroy()`: Tombol "Kosongkan Semua" → cart kosong
- [ ] Badge counter di sidebar menampilkan jumlah item yang benar
- [ ] Setelah `destroy()`, badge counter hilang

---

## Phase G — Dashboard Update & Integrasi Final

**Tujuan:** Update dashboard untuk menampilkan statistik Pelanggan, dan pastikan semua modul terintegrasi.

**Estimasi waktu:** 10 menit
**File yang diubah:** `app/Controllers/DashboardController.php`, `app/Views/dashboard/index.php`

### Step G.1 — Update DashboardController

Buka `app/Controllers/DashboardController.php`. Tambahkan hitungan pelanggan ke method `index()`:

```php
// Di dalam DashboardController::index(), tambahkan PelangganModel:
require_once __DIR__ . '/../Models/PelangganModel.php';
$pelangganModel = new PelangganModel();

// Tambahkan ke array $data:
$data['total_pelanggan']    = $pelangganModel->countActive();
$data['total_pelanggan_arsip'] = /* Tambahkan method countArchived di PelangganModel */;
```

Tambahkan method `countArchived()` di `PelangganModel.php` (tambahkan, JANGAN ubah yang lain):

```php
public function countArchived(): int
{
    $result = $this->queryOne(
        "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NOT NULL"
    );
    return (int)($result['total'] ?? 0);
}
```

### Step G.2 — Update Dashboard View

Buka `app/Views/dashboard/index.php`. Tambahkan summary card untuk pelanggan di samping card yang sudah ada:

```html
<!-- Tambahkan ini setelah card "Total Arsip" yang sudah ada -->
<div class="summary-card">
  <div class="summary-icon">
    <i class="ph ph-users"></i>
  </div>
  <div class="summary-info">
    <span class="summary-value"><?= (int)($total_pelanggan ?? 0) ?></span>
    <span class="summary-label">Pelanggan Aktif</span>
  </div>
</div>
```

### Step G.3 — Verifikasi Checkpoint Phase G

- [ ] Dashboard menampilkan jumlah pelanggan yang benar
- [ ] Angka bertambah ketika pelanggan baru ditambahkan
- [ ] Angka berkurang ketika pelanggan di-soft-delete

---

## Phase H — Final Testing & Submission Checklist

### Step H.1 — Jalankan Ulang dari Awal (Fresh Test)

```bash
# 1. Reset database (HATI-HATI: akan hapus semua data!)
php spark migrate:rollback --all

# 2. Jalankan ulang migration
php spark migrate

# 3. Seed ulang
php spark db:seed DatabaseSeeder

# 4. Jalankan server
php spark serve
# Atau akses via XAMPP: http://localhost/laundry-in/public/
```

### Step H.2 — Testing Checklist Lengkap

**Prerequisite:**

- [ ] PHP 8.1+ (`php -v`)
- [ ] Composer installed (`composer --version`)
- [ ] XAMPP/MySQL berjalan
- [ ] Database `kampusin_db` ada

**Migration & Seeder (SOAL 02):**

- [ ] `php spark migrate` berjalan tanpa error
- [ ] Tabel `admins`, `jenis_layanan`, `pelanggan` terbuat di DB
- [ ] `php spark db:seed DatabaseSeeder` berjalan tanpa error
- [ ] Admin `admin/admin123` bisa login
- [ ] 6 data layanan seed muncul di `/layanan`
- [ ] 5 data pelanggan seed muncul di `/pelanggan`

**SOAL 01 — CRUD Layanan (sudah ada sejak v1.0):**

- [ ] List layanan tampil
- [ ] Tambah layanan berhasil
- [ ] Edit layanan berhasil
- [ ] Soft delete → muncul di arsip, tidak di list aktif
- [ ] Restore → kembali ke list aktif

**SOAL 01 — CRUD Pelanggan (baru di v2.0):**

- [ ] List pelanggan tampil di `/pelanggan`
- [ ] Tambah pelanggan — validasi berjalan (coba submit kosong)
- [ ] Tambah pelanggan — data tersimpan dengan benar
- [ ] Edit pelanggan — form pre-filled
- [ ] Edit pelanggan — perubahan tersimpan
- [ ] Soft delete → `deleted_at` terisi di DB, muncul di `/pelanggan/archive`
- [ ] Restore → `deleted_at = NULL` di DB, kembali ke list

**SOAL 03 — Login dari Database:**

- [ ] Login `admin / admin123` → masuk dashboard
- [ ] Login `admin / salah` → "Username atau password salah." (TIDAK tampilkan detail error)
- [ ] Akses `/dashboard` tanpa login → redirect ke `/login`
- [ ] Logout → session hancur, tidak bisa akses dashboard lagi
- [ ] Password di DB adalah bcrypt hash (cek di phpMyAdmin, kolom `password` dimulai `$2y$`)

**SOAL 04 — Dompdf PDF:**

- [ ] Tombol "Export PDF" muncul di halaman `/layanan`
- [ ] Klik tombol → PDF muncul di tab baru
- [ ] PDF berisi tabel data layanan yang benar
- [ ] Header PDF: "Laundry-IN"
- [ ] Tanggal cetak akurat
- [ ] Format A4 Landscape
- [ ] `vendor/dompdf/` folder ada

**SOAL 05 — Shopping Cart:**

- [ ] Tombol "Tambah ke Cart" ada di halaman layanan
- [ ] `insert()`: Item masuk ke cart
- [ ] `insert()` item yang sama lagi: quantity bertambah (tidak duplicate)
- [ ] `update()`: Angka di field quantity diubah → subtotal berubah
- [ ] `update()` dengan 0: Item terhapus
- [ ] `remove()`: Tombol × menghapus item dari cart
- [ ] `total()`: Total harga akurat = sum semua subtotal
- [ ] `destroy()`: "Kosongkan Semua" → cart kosong
- [ ] Badge counter di sidebar akurat

**Security (semua harus pass):**

- [ ] XSS Test: Submit `<script>alert(1)</script>` sebagai nama pelanggan → tampil sebagai text biasa, TIDAK execute
- [ ] CSRF: Semua form POST punya `<input type="hidden" name="csrf_token">`
- [ ] SQL Injection: Coba submit `' OR '1'='1` sebagai username login → tidak berhasil masuk
- [ ] Redirect setelah login menggunakan session check, bukan parameter URL
- [ ] `.env` ada di `.gitignore`

**UI/UX:**

- [ ] Dark mode toggle berfungsi, preference tersimpan di localStorage
- [ ] TIDAK ada emoji di UI manapun — semua icon dari Phosphor Icons
- [ ] Font adalah Poppins/Inter — bukan Times New Roman
- [ ] Mobile responsive (test di Chrome DevTools, 375px)
- [ ] Flash messages muncul setelah operasi CRUD
- [ ] Tidak ada error PHP di halaman manapun

### Step H.3 — Update `State.md`

Setelah semua checklist di atas centang, update `docs/State.md`:

```markdown
# State.md — Project Status

**Project:** Laundry-IN | Laundry Service Management Web App
**Last Updated:** [TANGGAL HARI INI]
**Status:** COMPLETE v2.0.0

## v2.0 Additions

- Phase A: Routing Unification — single CI4 router
- Phase B: Migration CI4 + Seeder CI4 (SOAL 02)
- Phase C: CRUD Pelanggan — PelangganModel, PelangganController, 4 views (SOAL 01)
- Phase D: Login hardening — rate limiting (SOAL 03 already complete, enhanced)
- Phase E: Dompdf PDF export — LayananController::exportPdf(), pdf.php view (SOAL 04)
- Phase F: Cart Library — Cart.php, CartController, cart/index.php view (SOAL 05)
- Phase G: Dashboard update — pelanggan count card
```

### Step H.4 — Update README.md

Tambahkan bagian berikut di `README.md`:

```markdown
## Fitur v2.0 (Patch Update)

- CRUD Pelanggan (Tambah, Edit, Soft Delete, Restore, Arsip)
- Migration & Seeder CI4 (`php spark migrate`, `php spark db:seed DatabaseSeeder`)
- Export PDF daftar layanan menggunakan Dompdf
- Shopping Cart berbasis session (insert, update, total, remove, destroy)
- Login hardening dengan rate limiting

## Pemenuhan Soal Ujian

| Soal      | Keterangan                                           | Bobot    |
| --------- | ---------------------------------------------------- | -------- |
| SOAL 01   | CRUD Layanan + CRUD Pelanggan dengan Soft Delete     | 20%      |
| SOAL 02   | Migration CI4 + Seeder CI4 + Model                   | 20%      |
| SOAL 03   | Login validasi dari database (bcrypt)                | 20%      |
| SOAL 04   | Export PDF menggunakan Dompdf                        | 20%      |
| SOAL 05   | Cart Library: insert, update, total, remove, destroy | 20%      |
| **Total** |                                                      | **100%** |

## Cara Setup (v2.0)

1. Clone repository
2. `cp .env.example .env` dan isi kredensial database
3. `composer install` (untuk Dompdf)
4. `php spark migrate`
5. `php spark db:seed DatabaseSeeder`
6. Akses via XAMPP: `http://localhost/laundry-in/public/`
   atau via built-in server: `php spark serve` → `http://localhost:8080/`

## Login

- Username: `admin`
- Password: `admin123`
```

---

## File Tree Final v2.0

```
laundry-in/
├── index.php                          ← DIUPDATE (thin redirect ke public/)
├── .htaccess                          ← DIUPDATE (forward ke public/index.php)
├── .env                               ← (sama, gitignored)
├── .env.example                       ← (sama)
├── .gitignore                         ← (sama)
├── README.md                          ← DIUPDATE (tambah fitur v2.0)
├── composer.json                      ← DIUPDATE (tambah dompdf/dompdf)
│
├── assets/                            ← (sama, sudah dipindah dari public/ di v1.0)
│   ├── css/
│   │   ├── variables.css
│   │   ├── reset.css
│   │   ├── layout.css
│   │   ├── components.css             ← DIUPDATE (tambah .badge-counter)
│   │   └── utilities.css
│   └── js/
│       ├── theme.js
│       ├── sidebar.js
│       └── modal.js
│
├── app/
│   ├── Config/
│   │   ├── App.php                    ← DIUPDATE (set baseURL)
│   │   ├── Database.php               ← VERIFIKASI (isi credentials)
│   │   └── Routes.php                 ← DIUPDATE (tambah routes pelanggan, cart, pdf)
│   │
│   ├── Controllers/
│   │   ├── AuthController.php         ← DIUPDATE (tambah rate limiting)
│   │   ├── DashboardController.php    ← DIUPDATE (tambah count pelanggan)
│   │   ├── LayananController.php      ← DIUPDATE (tambah method exportPdf)
│   │   ├── PelangganController.php    ← BARU (Phase C)
│   │   └── CartController.php         ← BARU (Phase F)
│   │
│   ├── Database/
│   │   ├── Migrations/                ← BARU (Phase B)
│   │   │   ├── 2026-06-29-000001_CreateAdminsTable.php
│   │   │   ├── 2026-06-29-000002_CreateJenisLayananTable.php
│   │   │   └── 2026-06-29-000003_CreatePelangganTable.php
│   │   └── Seeds/                     ← BARU (Phase B)
│   │       ├── DatabaseSeeder.php
│   │       ├── AdminSeeder.php
│   │       ├── LayananSeeder.php
│   │       └── PelangganSeeder.php
│   │
│   ├── Helpers/
│   │   └── auth.php                   ← DIUPDATE (tambah rate limiting functions)
│   │
│   ├── Libraries/
│   │   └── Cart.php                   ← BARU (Phase F)
│   │
│   ├── Models/
│   │   ├── BaseModel.php              ← TIDAK DIUBAH
│   │   ├── AdminModel.php             ← TIDAK DIUBAH
│   │   ├── LayananModel.php           ← TIDAK DIUBAH
│   │   └── PelangganModel.php         ← BARU (Phase C)
│   │
│   └── Views/
│       ├── layouts/
│       │   ├── main.php               ← DIUPDATE (asset path + sidebar nav baru)
│       │   ├── auth.php               ← DIUPDATE (asset path fix)
│       │   └── landing.php            ← DIUPDATE (asset path fix)
│       ├── auth/login.php             ← TIDAK DIUBAH
│       ├── dashboard/index.php        ← DIUPDATE (tambah card pelanggan)
│       ├── layanan/
│       │   ├── index.php              ← DIUPDATE (tambah tombol export PDF + cart btn)
│       │   ├── create.php             ← TIDAK DIUBAH
│       │   ├── edit.php               ← TIDAK DIUBAH
│       │   ├── archive.php            ← TIDAK DIUBAH
│       │   └── pdf.php                ← BARU (Phase E) — template Dompdf
│       ├── pelanggan/                 ← BARU (Phase C)
│       │   ├── index.php
│       │   ├── create.php
│       │   ├── edit.php
│       │   └── archive.php
│       └── cart/                      ← BARU (Phase F)
│           └── index.php
│
├── docs/
│   ├── PRD.md
│   ├── Planning.md
│   ├── Patch_Update_v2.md             ← INI FILE INI
│   └── State.md                       ← DIUPDATE setelah semua phase selesai
│
├── vendor/                            ← DIUPDATE (tambah dompdf setelah composer require)
└── public/
    └── index.php                      ← CI4 entry point (tidak diubah)
```

---

## Ringkasan Eksekutif

| Soal           | Bobot    | v1.0                | v2.0     | v3.0               |
| -------------- | -------- | ------------------- | -------- | ------------------ |
| SOAL 01        | 20%      | 10% (hanya Layanan) | 20%      | 20%                |
| SOAL 02        | 20%      | 0%                  | 20%      | 20%                |
| SOAL 03        | 20%      | 20%                 | 20%      | 20%                |
| SOAL 04        | 20%      | 0%                  | 20%      | 20%                |
| SOAL 05        | 20%      | 0%                  | 20%      | 20%                |
| **Fitur Baru** | –        | –                   | –        | User + Pesanan     |
| **Total**      | **100%** | **~30%**            | **100%** | **100% + Fitbaru** |

**Fitur baru Phase I (v3.0):** Sidebar theme, User Auth (registrasi/login), Cart di user, Checkout → Pesanan, Workflow 4 status, Export PDF struk + Print.

**Urutan eksekusi yang wajib diikuti:**

```
Phase A → Phase B → Phase C → Phase D → Phase E → Phase F → Phase G → Phase H → Phase I
```

Jangan loncat phase. Setiap phase punya verification checkpoint — jangan lanjut sebelum checkpoint pass semua.

---

## Phase I — Sidebar Theme, User Auth, Pesanan Workflow & Struk PDF

**Tujuan:** Memperbaiki sidebar agar mengikuti tema dark/light, membangun sistem autentikasi untuk **User (Pelanggan)**, memindahkan fitur Cart ke sisi User, membuat halaman **Pesanan** di sisi Admin dengan workflow status 4 tahap, serta memindahkan fitur Export PDF ke halaman Pesanan dalam bentuk struk pembelian.

**Estimasi waktu:** 120 menit
**File yang dibuat:** ~15 file baru (model, controller, views, migration, seeder)
**File yang diubah:** ~10 file (CSS, layout, routes, helpers)
**File yang TIDAK diubah:** Semua file CRUD Layanan, CRUD Pelanggan, Dashboard yang sudah ada

---

### I.1 — Perbaikan Sidebar Dark/Light Mode

**Masalah:** Sidebar saat ini selalu gelap (`--sidebar-bg: #0D1117`) di kedua mode. Diminta agar sidebar mengikuti tema: light mode → sidebar terang, dark mode → sidebar gelap.

**Perubahan di `assets/css/variables.css`:**

```css
/* HAPUS dari :root (light) dan .dark (dark) — sidebar-bg yang hardcoded */
/* GANTI dengan: */

:root {
  /* Sidebar — ikut tema light */
  --sidebar-bg: #ffffff;
  --sidebar-text: #4b5563;
  --sidebar-text-hover: #111827;
  --sidebar-active-bg: rgba(13, 148, 136, 0.1);
  --sidebar-active-text: #0d9488;
  --sidebar-active-bar: #0d9488;
  --sidebar-border: #e5e7eb;
}

.dark {
  /* Sidebar — ikut tema dark */
  --sidebar-bg: #0d1117;
  --sidebar-text: #64748b;
  --sidebar-text-hover: #e2e8f0;
  --sidebar-active-bg: rgba(13, 148, 136, 0.15);
  --sidebar-active-text: #14b8a6;
  --sidebar-active-bar: #14b8a6;
  --sidebar-border: rgba(255, 255, 255, 0.06);
}
```

**Perubahan di `assets/css/layout.css`:**

Sesuaikan warna teks brand di sidebar agar kontras di light mode:

```css
.sidebar-brand-name {
  /* Ubah dari color: #f1f5f9 (hardcoded) menjadi: */
  color: var(--color-text-primary);
}

.sidebar-user-name {
  /* Ubah dari color: #cbd5e1 (hardcoded) menjadi: */
  color: var(--color-text-primary);
}
```

**Verifikasi:** Toggle dark/light mode → sidebar berubah warna mengikuti tema.

---

### I.2 — Database: Tabel User (Pelanggan), Pesanan & Detail Pesanan

#### Migration 4: Tabel `users` (untuk login pelanggan)

Buat file: `app/Database/Migrations/2026-06-29-000004_CreateUsersTable.php`

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'unique'     => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'no_telp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
    }
}
```

#### Migration 5: Tabel `pesanan` (orders)

Buat file: `app/Database/Migrations/2026-06-29-000005_CreatePesananTable.php`

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePesananTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'kode_pesanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'unique'     => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['diterima', 'dibuat', 'siap', 'selesai'],
                'null'       => false,
                'default'    => 'diterima',
            ],
            'metode_pengiriman' => [
                'type'       => 'ENUM',
                'constraint' => ['diantar', 'diambil'],
                'null'       => false,
                'default'    => 'diambil',
            ],
            'total_harga' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 0,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tanggal_pesan' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pesanan', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('pesanan', true);
    }
}
```

#### Migration 6: Tabel `detail_pesanan` (order items)

Buat file: `app/Database/Migrations/2026-06-29-000006_CreateDetailPesananTable.php`

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDetailPesananTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'pesanan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'nama_layanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'harga_satuan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'satuan_harga' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => 'kg',
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 1,
            ],
            'subtotal' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pesanan_id', 'pesanan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('detail_pesanan', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('detail_pesanan', true);
    }
}
```

#### Seeder 4: UsersSeeder (data dummy)

Buat file: `app/Database/Seeds/UsersSeeder.php`

```php
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $count = $this->db->table('users')->countAll();
        if ($count > 0) return;

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
```

Update `DatabaseSeeder.php` — tambah `$this->call('UsersSeeder');` di urutan terakhir.

#### Jalankan Migration

```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

---

### I.3 — User Auth (Registrasi & Login untuk Pelanggan)

#### Buat UserModel

File: `app/Models/UserModel.php` — extends BaseModel (PDO), method: findByEmail(), findById(), create().

#### Buat UserAuthController

File: `app/Controllers/UserAuthController.php`

Method:

- `showRegister()` — GET /daftar — form registrasi
- `register()` — POST /daftar — proses registrasi (bcrypt, validasi)
- `showLogin()` — GET /masuk — form login user
- `login()` — POST /masuk — proses login (session user_id, user_nama)
- `logout()` — GET /keluar — destroy session user

#### Buat View: `app/Views/auth/user-register.php`

#### Buat View: `app/Views/auth/user-login.php`

Gunakan layout `app/Views/layouts/auth.php` yang sudah ada (dengan penyesuaian).

**Fitur registrasi:** nama, email, password, no_telp (opsional), alamat (opsional). Validasi server-side, CSRF, double-submit prevention.

**Session key untuk user:** `user_id`, `user_nama`, `user_email`

#### Helper: `requireUserAuth()`

Di `app/Helpers/auth.php`, tambahkan:

```php
function requireUserAuth(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: /masuk');
        exit;
    }
}
```

#### Routes (di `app/Config/Routes.php`)

```php
// ─── User Auth ──────────────────────────────────
$routes->get('/daftar', function () {
    session();
    include_once APPPATH . 'Controllers/UserAuthController.php';
    (new UserAuthController())->showRegister();
});
$routes->post('/daftar', function () {
    session();
    include_once APPPATH . 'Controllers/UserAuthController.php';
    (new UserAuthController())->register();
});
$routes->get('/masuk', function () {
    session();
    include_once APPPATH . 'Controllers/UserAuthController.php';
    (new UserAuthController())->showLogin();
});
$routes->post('/masuk', function () {
    session();
    include_once APPPATH . 'Controllers/UserAuthController.php';
    (new UserAuthController())->login();
});
$routes->get('/keluar', function () {
    session();
    include_once APPPATH . 'Controllers/UserAuthController.php';
    (new UserAuthController())->logout();
});
```

---

### I.4 — Cart Dipindahkan ke User (Halaman Landing)

**Perubahan pada Landing Page (`app/Views/landing/index.php`):**

- Header landing page: tambahkan icon Keranjang (`ph-shopping-cart`) dengan badge counter (jumlah item).
- Icon Keranjang mengarah ke `/cart` (halaman cart milik user).
- Hanya tampil jika user sudah login (cek `$_SESSION['user_id']`).
- Di sampingnya, link "Masuk" / "Daftar" jika belum login, atau "Akun Saya" jika sudah login.

**Perubahan di Sidebar Admin (`app/Views/layouts/main.php`):**

- HAPUS link "Keranjang" dari sidebar admin.
- TAMBAH link "Pesanan" (`ph-clipboard-text`) mengarah ke `/pesanan`.
- Badge counter di sidebar admin menampilkan jumlah pesanan baru (status 'diterima').

**Perubahan di Routes Admin:**

- Cart routes tetap ada untuk sisi user (`/cart`, `/cart/add`, dll).
- CartController tetap dipakai untuk sisi user.
- BUTUH requireUserAuth() di CartController (bukan requireAuth()).

**Perubahan CartController:**

- Ubah semua `requireAuth()` menjadi `requireUserAuth()`.
- Route cart diproteksi untuk user login, bukan admin.

---

### I.5 — Halaman Keranjang (User Side)

**Layout untuk user:** Buat layout terpisah `app/Views/layouts/user.php` — mirip main.php tapi tanpa sidebar admin, hanya header landing + konten.

Atau gunakan layout landing yang sudah ada dengan konten cart di dalamnya.

**View Cart User:** `app/Views/cart/index.php` — sudah ada, hanya perlu penyesuaian:

- Tambah field pilihan "Diantar / Diambil" (select/radio) di Ringkasan Pesanan.
- Tombol "Pesan Sekarang" berubah fungsi: simpan ke database `pesanan` + `detail_pesanan`, kosongkan session cart, redirect ke halaman status pesanan.

**Fitur Checkout (User Cart → Pesanan):**

Method baru di CartController: `checkout()` — POST /cart/checkout

1. Validasi user sudah login (requireUserAuth)
2. Validasi CSRF
3. Validasi cart tidak kosong
4. Generate kode pesanan unik: `LND-{Ymd}-{random 4 digit}`
5. Simpan ke tabel `pesanan` (user_id, kode_pesanan, status='diterima', metode_pengiriman, total_harga)
6. Simpan semua item cart ke `detail_pesanan`
7. Kosongkan session cart
8. Redirect ke `/pesanan-saya/{id}` dengan flash success

**Route baru:**

```php
$routes->post('/cart/checkout', function () {
    session();
    include_once APPPATH . 'Controllers/CartController.php';
    (new CartController())->checkout();
});
$routes->get('/pesanan-saya/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/CartController.php';
    (new CartController())->status((int) $id);
});
```

---

### I.6 — Halaman Pesanan (Admin Side)

#### Buat PesananModel

File: `app/Models/PesananModel.php` — extends BaseModel (PDO).

Method:

- `all()` — Semua pesanan JOIN users (nama), ORDER BY created_at DESC
- `findById($id)` — Satu pesanan + detail items
- `findByStatus($status)` — Filter by status
- `updateStatus($id, $status)` — Update status pesanan
- `countByStatus($status)` — Jumlah pesanan per status (untuk badge)
- `countNew()` — Jumlah pesanan dengan status 'diterima' (untuk badge sidebar)

#### Buat PesananController

File: `app/Controllers/PesananController.php`

Method:

- `index()` — GET /pesanan — Daftar semua pesanan dengan filter status
- `detail($id)` — GET /pesanan/{id} — Detail satu pesanan + tombol update status
- `updateStatus($id)` — POST /pesanan/update-status/{id} — Update status workflow
- `exportPdf($id)` — GET /pesanan/export-pdf/{id} — Export struk PDF
- `printStruk($id)` — GET /pesanan/print-struk/{id} — Halaman HTML untuk print struk

**Semua method requireAuth() untuk admin.**

#### View: `app/Views/pesanan/index.php`

Tabel dengan kolom:

- No, Kode Pesanan, Pelanggan, Tanggal, Total, Status, Metode, Aksi

Filter tabs: Semua | Diterima | Dibuat | Siap | Selesai

Status badges dengan warna berbeda:

- `diterima` → badge-warning (kuning)
- `dibuat` → badge-info (biru)
- `siap` → badge-express (teal)
- `selesai` → badge-success (hijau)

#### View: `app/Views/pesanan/detail.php`

Card detail pesanan:

- Informasi pelanggan: nama, email, no_telp, alamat
- Informasi pesanan: kode, tanggal, metode (Diantar/Diambil)
- Tabel item layanan: nama, harga satuan, qty, subtotal
- Total harga
- Status saat ini dengan tombol workflow (hanya status berikutnya yang bisa diklik)

**Workflow tombol:**

- Jika status `diterima` → tombol "Proses ke Dibuat"
- Jika status `dibuat` → tombol "Tandai Siap"
- Jika status `siap` → tombol "Tandai Selesai"
- Jika status `selesai` → tidak ada tombol (sudah final)

#### Route Pesanan (Admin)

```php
$routes->get('/pesanan', function () {
    session();
    include_once APPPATH . 'Controllers/PesananController.php';
    (new PesananController())->index();
});
$routes->get('/pesanan/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PesananController.php';
    (new PesananController())->detail((int) $id);
});
$routes->post('/pesanan/update-status/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PesananController.php';
    (new PesananController())->updateStatus((int) $id);
});
$routes->get('/pesanan/export-pdf/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PesananController.php';
    (new PesananController())->exportPdf((int) $id);
});
$routes->get('/pesanan/print-struk/(:num)', function ($id) {
    session();
    include_once APPPATH . 'Controllers/PesananController.php';
    (new PesananController())->printStruk((int) $id);
});
```

---

### I.7 — Hapus Export PDF dari Layanan & Kembalikan Posisi Tombol

#### Di `app/Views/layanan/index.php`:

- HAPUS tombol Export PDF dari page-header.
- Pastikan tombol "Tambah Layanan" kembali ke posisi semula (di sisi kanan header, sendirian atau dengan tombol lain yang memang diperlukan).

#### Di `app/Config/Routes.php`:

- HAPUS atau COMMENT route `GET /layanan/export-pdf`.

#### Di `app/Controllers/LayananController.php`:

- Method `exportPdf()` boleh dihapus atau dikomentari. Jangan dihapus jika masih dipanggil di tempat lain. Karena sudah tidak ada route, aman untuk dihapus.

---

### I.8 — Struk PDF & Print (Dompdf)

#### View Struk PDF: `app/Views/pesanan/struk-pdf.php`

Desain struk pembelian Laundry-IN — format **A4 Portrait** (karena struk):

```php
<?php // app/Views/pesanan/struk-pdf.php ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pembelian — Laundry-IN</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1a1a2e;
            padding: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #0d9488;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            color: #0d9488;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 9pt;
            color: #64748b;
        }
        .info-section {
            margin-bottom: 16px;
            font-size: 9pt;
        }
        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-section td {
            padding: 2px 4px;
        }
        .info-section .label {
            width: 120px;
            color: #64748b;
        }
        .divider {
            border-top: 1px dashed #cbd5e1;
            margin: 12px 0;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.items thead tr {
            background-color: #0d9488;
            color: #ffffff;
        }
        table.items th {
            padding: 8px 6px;
            text-align: left;
            font-size: 9pt;
        }
        table.items td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9pt;
        }
        table.items tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .total-row td {
            font-weight: bold;
            font-size: 11pt;
            padding-top: 10px;
            border-top: 2px solid #0d9488;
        }
        .total-row .amount {
            color: #0d9488;
        }
        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
            border-top: 2px dashed #0d9488;
            padding-top: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }
        .status-diterima { background: #fef3c7; color: #92400e; }
        .status-dibuat    { background: #dbeafe; color: #1e40af; }
        .status-siap      { background: #ccfbf1; color: #0d9488; }
        .status-selesai   { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laundry-IN</h1>
        <p>Sistem Manajemen Layanan Laundry</p>
        <p style="margin-top:4px; font-size:11pt; font-weight:bold;">STRUK PEMBELIAN</p>
    </div>

    <div class="info-section">
        <table>
            <tr><td class="label">Kode Pesanan</td><td>: <strong><?= htmlspecialchars($pesanan['kode_pesanan']) ?></strong></td></tr>
            <tr><td class="label">Tanggal</td><td>: <?= date('d/m/Y H:i', strtotime($pesanan['tanggal_pesan'])) ?> WIB</td></tr>
            <tr><td class="label">Pelanggan</td><td>: <?= htmlspecialchars($pesanan['nama_pelanggan'] ?? $pesanan['nama']) ?></td></tr>
            <tr><td class="label">No. Telepon</td><td>: <?= htmlspecialchars($pesanan['no_telp'] ?? '-') ?></td></tr>
            <tr><td class="label">Alamat</td><td>: <?= htmlspecialchars($pesanan['alamat'] ?? '-') ?></td></tr>
            <tr><td class="label">Metode</td><td>: <?= ucfirst($pesanan['metode_pengiriman']) ?></td></tr>
            <tr><td class="label">Status</td><td>: <span class="status-badge status-<?= $pesanan['status'] ?>"><?= strtoupper($pesanan['status']) ?></span></td></tr>
        </table>
    </div>

    <div class="divider"></div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:40%;">Layanan</th>
                <th style="width:15%;">Harga</th>
                <th style="width:10%;">Qty</th>
                <th style="width:15%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; $total = 0; ?>
            <?php foreach ($detail as $item): ?>
            <tr>
                <td style="text-align:center;"><?= $no++ ?></td>
                <td><?= htmlspecialchars($item['nama_layanan']) ?></td>
                <td>Rp <?= number_format((int)$item['harga_satuan'], 0, ',', '.') ?></td>
                <td style="text-align:center;"><?= (int)$item['quantity'] ?> <?= htmlspecialchars($item['satuan_harga'] ?? '') ?></td>
                <td style="text-align:right;">Rp <?= number_format((int)$item['subtotal'], 0, ',', '.') ?></td>
            </tr>
            <?php $total += (int)$item['subtotal']; ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align:right;">TOTAL</td>
                <td class="amount" style="text-align:right;">Rp <?= number_format($total, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Terima kasih telah menggunakan layanan Laundry-IN</p>
        <p>Struk ini dibuat otomatis pada <?= date('d/m/Y H:i') ?> WIB</p>
    </div>

</body>
</html>
```

#### Method `exportPdf()` di PesananController:

```php
public function exportPdf(int $id): void
{
    requireAuth();

    // Load Dompdf
    $autoloaderPaths = [
        __DIR__ . '/../../vendor/autoload.php',
        __DIR__ . '/../../../vendor/autoload.php',
    ];
    $loaded = false;
    foreach ($autoloaderPaths as $path) {
        if (file_exists($path)) { require_once $path; $loaded = true; break; }
    }
    if (!$loaded) { http_response_code(500); echo 'Dompdf belum terinstall.'; return; }

    $pesananModel = new PesananModel();
    $pesanan = $pesananModel->findById($id);
    if (!$pesanan) { http_response_code(404); echo 'Pesanan tidak ditemukan.'; return; }

    $detail = $pesananModel->getDetail($id);

    ob_start();
    extract(['pesanan' => $pesanan, 'detail' => $detail]);
    include __DIR__ . '/../Views/pesanan/struk-pdf.php';
    $html = ob_get_clean();

    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', false);
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = 'Struk-Laundry-IN_' . $pesanan['kode_pesanan'] . '.pdf';
    $dompdf->stream($filename, ['Attachment' => false]);
    exit;
}
```

#### Method `printStruk()` di PesananController:

View print HTML (`app/Views/pesanan/print-struk.php`) — mirip dengan struk-pdf.php tapi tanpa Dompdf, dengan CSS `@media print` dan tombol `window.print()`.

```php
public function printStruk(int $id): void
{
    requireAuth();
    $pesananModel = new PesananModel();
    $pesanan = $pesananModel->findById($id);
    $detail = $pesananModel->getDetail($id);

    ob_start();
    extract(['pesanan' => $pesanan, 'detail' => $detail]);
    include __DIR__ . '/../Views/pesanan/print-struk.php';
    $content = ob_get_clean();
    // Tampilkan langsung, tanpa layout (pure HTML untuk print)
    echo $content;
    exit;
}
```

#### Di halaman Detail Pesanan (`app/Views/pesanan/detail.php`):

Tambahkan 2 tombol aksi:

```html
<a
  href="/pesanan/export-pdf/<?= $pesanan['id'] ?>"
  class="btn btn-secondary"
  target="_blank"
>
  <i class="ph-bold ph-file-pdf"></i>
  Export PDF
</a>
<a
  href="/pesanan/print-struk/<?= $pesanan['id'] ?>"
  class="btn btn-primary"
  target="_blank"
>
  <i class="ph-bold ph-printer"></i>
  Print Struk
</a>
```

---

### I.9 — Update Sidebar Admin: Link Pesanan + Badge

Di `app/Views/layouts/main.php`:

```html
<!-- HAPUS block Keranjang (cart) dari sidebar -->
<!-- TAMBAH blocK Pesanan: -->

<?php require_once __DIR__ . '/../../Models/PesananModel.php'; $pesananModel =
new PesananModel(); $pesananBaru = $pesananModel->countNew(); ?>
<a
  href="/pesanan"
  class="sidebar-nav-item <?= $activePage === 'pesanan' ? 'active' : '' ?>"
>
  <i class="ph-bold ph-clipboard-text"></i>
  Pesanan <?php if ($pesananBaru > 0): ?>
  <span class="badge-counter"><?= $pesananBaru ?></span>
  <?php endif; ?>
</a>
```

**Letakkan link "Pesanan" di sidebar setelah "Dashboard", sebelum "Jenis Layanan".**

---

### I.10 — User Melihat Status Pesanan (Real-time)

#### View: `app/Views/pesanan/user-status.php`

Halaman untuk user melihat detail dan status pesanan mereka setelah checkout.

Menampilkan:

- Kode pesanan
- Status saat ini (dengan visual progress bar 4 tahap)
- Daftar item yang dipesan
- Total harga
- Tombol "Kembali ke Beranda"

Method di CartController: `status($id)` — GET /pesanan-saya/{id} — requireUserAuth, cek apakah pesanan milik user yang login.

---

### I.11 — Routes Update (Ringkasan)

**Routes yang DITAMBAHKAN:**

```
GET  /daftar                   → UserAuthController::showRegister()
POST /daftar                   → UserAuthController::register()
GET  /masuk                    → UserAuthController::showLogin()
POST /masuk                    → UserAuthController::login()
GET  /keluar                   → UserAuthController::logout()
POST /cart/checkout            → CartController::checkout()
GET  /pesanan-saya/{id}        → CartController::status()
GET  /pesanan                  → PesananController::index()
GET  /pesanan/{id}             → PesananController::detail()
POST /pesanan/update-status/{id} → PesananController::updateStatus()
GET  /pesanan/export-pdf/{id}  → PesananController::exportPdf()
GET  /pesanan/print-struk/{id} → PesananController::printStruk()
```

**Routes yang DIHAPUS/DICOMMENT:**

```
GET  /layanan/export-pdf       → HAPUS
```

**Routes yang DIUBAH (Cart → User):**

```
Cart routes tetap ada, tapi proteksi dari requireAuth() → requireUserAuth()
```

---

### I.12 — Verifikasi Checkpoint Phase I

**Sidebar Theme:**

- [ ] Light mode: sidebar berwarna terang (putih), teks gelap
- [ ] Dark mode: sidebar berwarna gelap, teks terang
- [ ] Toggle theme bekerja tanpa FOUC

**User Auth:**

- [ ] Halaman `/daftar` menampilkan form registrasi
- [ ] Registrasi user baru berhasil (bcrypt, session)
- [ ] Halaman `/masuk` menampilkan form login user
- [ ] Login user berhasil → redirect ke beranda
- [ ] Logout → session hancur

**Cart (User Side):**

- [ ] Icon Keranjang dengan badge muncul di header landing (untuk user login)
- [ ] Klik icon → masuk ke halaman `/cart`
- [ ] Tambah item dari `/layanan` (akses publik) → masuk ke cart
- [ ] Pilihan Diantar/Diambil tersedia di Ringkasan Pesanan
- [ ] Checkout → data masuk ke tabel `pesanan` + `detail_pesanan`
- [ ] Session cart dikosongkan setelah checkout

**Pesanan (Admin Side):**

- [ ] Sidebar admin menampilkan "Pesanan" dengan badge jumlah baru
- [ ] Halaman `/pesanan` menampilkan daftar semua pesanan
- [ ] Filter status bekerja (Semua/Diterima/Dibuat/Siap/Selesai)
- [ ] Detail pesanan menampilkan info pelanggan + item
- [ ] Update status workflow: Diterima → Dibuat → Siap → Selesai
- [ ] Status yang sudah selesai tidak memiliki tombol aksi

**Export PDF & Print:**

- [ ] Tombol Export PDF di halaman detail pesanan → download struk PDF
- [ ] PDF berformat A4 Portrait
- [ ] Struk berisi: header Laundry-IN, kode, pelanggan, item, total, status
- [ ] Tombol Print → halaman HTML dengan `window.print()`
- [ ] `vendor/dompdf/` folder ada

**Export PDF di Layanan:**

- [ ] Tombol Export PDF TIDAK ADA di halaman `/layanan`
- [ ] Route `/layanan/export-pdf` dihapus/dicomment
- [ ] Tombol "Tambah Layanan" kembali ke posisi semula

**Security & Rules:**

- [ ] CSRF token di semua form POST
- [ ] Prepared statements di semua query baru
- [ ] htmlspecialchars() di semua output
- [ ] Double-submit prevention di semua tombol submit
- [ ] Soft delete di tabel users dan pesanan (deleted_at)
- [ ] Password user di-bcrypt
- [ ] Tidak ada emoji — semua icon Phosphor Icons
- [ ] Font Inter/Poppins — bukan Times New Roman

---

## File Tree Final v3.0

```
laundry-in/
├── index.php                          ← (sama, thin redirect)
├── .htaccess                          ← (sama)
├── .env / .env.example                ← (sama)
├── .gitignore                         ← (sama)
├── README.md                          ← DIUPDATE (tambah fitur v3.0)
├── composer.json                      ← (sama, dompdf sudah ada)
│
├── assets/
│   ├── css/
│   │   ├── variables.css              ← DIUPDATE (sidebar theme ikut mode)
│   │   ├── reset.css
│   │   ├── layout.css                 ← DIUPDATE (warna brand/user name)
│   │   ├── components.css
│   │   └── utilities.css
│   └── js/
│       ├── theme.js
│       ├── sidebar.js
│       └── modal.js
│
├── app/
│   ├── Config/
│   │   ├── App.php
│   │   ├── Database.php
│   │   └── Routes.php                 ← DIUPDATE (tambah 12 route baru, hapus export-pdf)
│   │
│   ├── Controllers/
│   │   ├── AuthController.php         ← (sama)
│   │   ├── CartController.php         ← DIUPDATE (requireUserAuth, tambah checkout & status)
│   │   ├── DashboardController.php    ← (sama)
│   │   ├── LayananController.php      ← DIUPDATE (hapus exportPdf)
│   │   ├── PelangganController.php    ← (sama)
│   │   ├── PesananController.php      ← BARU (Phase I)
│   │   ├── UserAuthController.php     ← BARU (Phase I)
│   │   └── LandingController.php      ← (sama)
│   │
│   ├── Database/
│   │   ├── Migrations/
│   │   │   ├── 2026-06-29-000001_CreateAdminsTable.php
│   │   │   ├── 2026-06-29-000002_CreateJenisLayananTable.php
│   │   │   ├── 2026-06-29-000003_CreatePelangganTable.php
│   │   │   ├── 2026-06-29-000004_CreateUsersTable.php         ← BARU
│   │   │   ├── 2026-06-29-000005_CreatePesananTable.php       ← BARU
│   │   │   └── 2026-06-29-000006_CreateDetailPesananTable.php ← BARU
│   │   └── Seeds/
│   │       ├── DatabaseSeeder.php     ← DIUPDATE (tambah UsersSeeder)
│   │       ├── AdminSeeder.php
│   │       ├── LayananSeeder.php
│   │       ├── PelangganSeeder.php
│   │       └── UsersSeeder.php        ← BARU
│   │
│   ├── Helpers/
│   │   └── auth.php                   ← DIUPDATE (tambah requireUserAuth)
│   │
│   ├── Libraries/
│   │   └── Cart.php                   ← (sama)
│   │
│   ├── Models/
│   │   ├── BaseModel.php
│   │   ├── AdminModel.php
│   │   ├── LayananModel.php
│   │   ├── PelangganModel.php
│   │   ├── PesananModel.php           ← BARU (Phase I)
│   │   └── UserModel.php              ← BARU (Phase I)
│   │
│   └── Views/
│       ├── layouts/
│       │   ├── main.php               ← DIUPDATE (sidebar: Cart → Pesanan + badge)
│       │   ├── auth.php               ← (sama)
│       │   └── landing.php            ← DIUPDATE (tambah icon cart + user menu)
│       ├── auth/
│       │   ├── login.php              ← (sama)
│       │   ├── user-login.php         ← BARU
│       │   └── user-register.php      ← BARU
│       ├── dashboard/index.php        ← (sama)
│       ├── landing/index.php          ← (sama)
│       ├── layanan/
│       │   ├── index.php              ← DIUPDATE (hapus export PDF, kembalikan posisi tombol)
│       │   ├── create.php
│       │   ├── edit.php
│       │   ├── archive.php
│       │   └── pdf.php                ← (bisa dihapus karena export PDF pindah ke Pesanan)
│       ├── pelanggan/
│       │   ├── index.php
│       │   ├── create.php
│       │   ├── edit.php
│       │   └── archive.php
│       ├── cart/
│       │   └── index.php              ← DIUPDATE (tambah pilihan Diantar/Diambil, checkout)
│       └── pesanan/                    ← BARU (Phase I)
│           ├── index.php              ← Daftar pesanan (admin)
│           ├── detail.php             ← Detail + update status
│           ├── struk-pdf.php          ← Template PDF Dompdf
│           ├── print-struk.php        ← Template print HTML
│           └── user-status.php        ← Status untuk user
│
├── docs/
│   ├── PRD.md
│   ├── Planning.md
│   ├── Patch_Update_v2.md             ← (file ini, DIUPDATE Phase I)
│   └── State.md
│
├── vendor/
└── public/
    └── index.php
```

---

## Ringkasan Eksekutif v3.0

| Soal           | Bobot    | v1.0     | v2.0     | v3.0                            |
| -------------- | -------- | -------- | -------- | ------------------------------- |
| SOAL 01        | 20%      | 10%      | 20%      | 20%                             |
| SOAL 02        | 20%      | 0%       | 20%      | 20%                             |
| SOAL 03        | 20%      | 20%      | 20%      | 20%                             |
| SOAL 04        | 20%      | 0%       | 20%      | 20%                             |
| SOAL 05        | 20%      | 0%       | 20%      | 20%                             |
| **Fitur Baru** | –        | –        | –        | **User Auth + Pesanan + Struk** |
| **Total**      | **100%** | **~30%** | **100%** | **100% + Fitur Baru**           |

**Fitur baru di Phase I:**

- Sidebar mengikuti tema dark/light
- Autentikasi user (pelanggan) — registrasi & login
- Cart dipindahkan ke sisi user (ikon di header landing)
- Checkout: Cart → Pesanan (tersimpan di database)
- Halaman Pesanan di admin dengan workflow 4 status
- Export PDF struk pembelian (A4 Portrait) di setiap pesanan
- Tombol Print langsung ke printer
- Notifikasi badge jumlah pesanan baru di sidebar admin
