# Laundry-IN — Project Development Rules

> **Tujuan:** Panduan ini berisi aturan main untuk pengembangan website Laundry-IN. Semua perubahan kode HARUS mengikuti aturan di bawah ini agar konsisten, aman, dan bebas bug.

---

## 1. Aturan Umum

### 1.1 — Jangan Merusak yang Sudah Jalan

- ❌ DILARANG mengubah kode yang sudah berfungsi dengan baik hanya karena "biar rapi"
- ✅ Jika ingin refactor, buat file baru dulu, test, baru hapus yang lama
- ✅ Kalau ada bug, fix bugnya aja — jangan rewrite seluruh file

### 1.2 — Satu Perubahan, Satu Tujuan

- ❌ Jangan campur aduk multiple fitur dalam satu commit/file change
- ✅ Setiap perubahan harus fokus pada SATU hal (fix bug A, atau tambah fitur B)

### 1.3 — Test Dulu Sebelum Push

- ✅ Setelah setiap perubahan, cek di browser apakah halaman masih berfungsi
- ✅ Cek di mode light & dark mode
- ✅ Cek di mobile view (Chrome DevTools → toggle device toolbar)

---

## 2. Routing System

### 2.1 — Hanya SATU Routing System

- ✅ Semua route HARUS didefinisikan di `app/Config/Routes.php`
- ❌ JANGAN nambah route di custom `index.php` front controller
- ✅ Custom `index.php` hanya boleh handle: session start, helper load, lalu redirect ke CI4

### 2.2 — Format Route

```php
// Controllers pake namespace lengkap:
$routes->get('/pelanggan', 'App\Controllers\PelangganController::index');
// atau via closure (kalo masih pake cara lama):
$routes->get('/pelanggan', function() {
    require_once APPPATH . 'Controllers/PelangganController.php';
    (new PelangganController())->index();
});
```

### 2.3 — Prefix URL

- ✅ Semua URL path TANPA prefix `/laundry-in/`
- ✅ Contoh: `/dashboard`, `/layanan`, `/pelanggan`, `/login`
- ❌ JANGAN pakai `/laundry-in/dashboard` di redirect atau href

---

## 3. Controller Rules

### 3.1 — Auth Check Wajib

```php
// Setiap method yang butuh login WAJIB panggil ini di baris pertama:
public function index(): void
{
    requireAuth(); // ← WAJIB!
    // ... sisanya
}
```

### 3.2 — CSRF di Setiap POST

- ✅ Setiap form dengan method POST WAJIB punya CSRF token

```php
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
```

- ✅ Di controller, validasi CSRF sebelum proses data:

```php
if (!validate_csrf()) {
    $this->redirect('/path', 'flash_error', 'Token CSRF tidak valid.');
}
```

### 3.3 — Redirect Konsisten

- ✅ Redirect pake `$this->redirect('/path')` BUKAN `header('Location: ...')`
- ✅ Flash message harus lewat parameter ke-3:

```php
$this->redirect('/layanan', 'flash_success', 'Berhasil disimpan.');
```

### 3.4 — Method Naming

| Method         | Fungsi                    |
| -------------- | ------------------------- |
| `index()`      | Menampilkan list data     |
| `create()`     | Menampilkan form tambah   |
| `store()`      | Proses simpan data baru   |
| `edit($id)`    | Menampilkan form edit     |
| `update($id)`  | Proses update data        |
| `delete($id)`  | Soft delete data          |
| `archive()`    | Menampilkan data terhapus |
| `restore($id)` | Memulihkan data           |

---

## 4. Model & Database Rules

### 4.1 — Hanya Model yang Boleh SQL

- ❌ DILARANG keras menulis SQL query di Controller atau View
- ✅ SEMUA query SQL harus di Model class
- ✅ View hanya boleh echo data yang sudah disiapkan Model/Controller

### 4.2 — Wajib Prepared Statement

- ✅ SEMUA query WAJIB pakai PDO prepared statement dengan named parameter (`:param`)
- ❌ DILARANG concat string untuk query:

```php
// ❌ SALAH:
$sql = "SELECT * FROM layanan WHERE id = " . $id;

// ✅ BENAR:
$sql = "SELECT * FROM layanan WHERE id = :id";
$stmt->execute([':id' => $id]);
```

### 4.3 — Soft Delete Wajib

- ❌ JANGAN pernah pakai `DELETE FROM` untuk data bisnis
- ✅ UPDATE `deleted_at = NOW()` untuk soft delete
- ✅ SELECT wajib filter `WHERE deleted_at IS NULL` untuk data aktif
- ✅ SELECT `WHERE deleted_at IS NOT NULL` untuk data arsip

### 4.4 — Migration & Seeding

- ✅ Setiap perubahan tabel WAJIB bikin file migration baru
- ✅ Setiap data awal WAJIB lewat seeder, bukan SQL manual
- ✅ Format file migration: `YYYY-MM-DD-XXXXXX_NamaMigration.php`

---

## 5. View & Template Rules

### 5.1 — Output Safety

- ✅ Setiap data dari user atau database WAJIB di-escape:

```php
<?= htmlspecialchars($variable) ?>
```

- ❌ JANGAN pakai `<?= $variable ?>` langsung tanpa `htmlspecialchars()`

### 5.2 — No Business Logic in Views

- ❌ DILARANG keras menulis logic (if/else untuk bisnis, loop untuk perhitungan) di View
- ✅ View hanya untuk menampilkan data, format harga, dan HTML structure

### 5.3 — CSS Path

- ✅ Path assets: `/laundry-in/assets/css/...` atau pake `base_url()`
- ✅ Semua file CSS di `assets/css/`
- ✅ Semua file JS di `assets/js/`

### 5.4 — No Emoji, No Times New Roman

- ❌ DILARANG pakai emoji sebagai elemen UI (`😊`, `✅`, `🚀`)
- ✅ Gunakan Phosphor Icons untuk semua icon
- ❌ DILARANG Times New Roman atau font serif lainnya

---

## 6. CSS Design System Rules

### 6.1 — Wajib Pakai CSS Variables

- ✅ Semua warna, spacing, font-size HARUS dari variables di `variables.css`
- ❌ JANGAN hardcode nilai color/spacing, pakai `var(--color-primary)` dll

### 6.2 — Layout Rules

- ✅ Sidebar: `260px` fixed, dark di kedua mode
- ✅ Main content: `margin-left: var(--sidebar-width)`
- ✅ Max content width: `1200px`
- ✅ Responsive breakpoints: 768px (mobile), 1024px (tablet)

### 6.3 — Dark Mode

- ✅ Toggle class `dark` di `<html>` element
- ✅ Preference disimpan di `localStorage` key `laundry-in-theme`
- ✅ CSS variables untuk dark mode di selector `.dark`

---

## 7. Security Rules

### 7.1 — CSRF Token Regeneration

- ✅ CSRF token WAJIB diregenerasi SETELAH berhasil digunakan:

```php
function validate_csrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    $valid = hash_equals($_SESSION['csrf_token'] ?? '', $token);
    if ($valid) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // ← WAJIB!
    }
    return $valid;
}
```

### 7.2 — Login Security

- ✅ Password disimpan sebagai bcrypt hash (`password_hash()`)
- ✅ Verifikasi pakai `password_verify()`
- ✅ Session regenerated setelah login (`session_regenerate_id(true)`)
- ✅ Error message generic: "Username atau password salah."

### 7.3 — Form Validation

- ✅ Validasi di sisi SERVER (Controller), bukan cuma client-side
- ✅ Tampilkan error message spesifik per field
- ✅ Old input tetap dipertahankan saat validasi gagal

### 7.4 — Double Submit Prevention

- ✅ Tombol submit WAJIB di-disable setelah klik pertama:

```html
<button onclick="this.disabled=true; this.form.submit();"></button>
```

---

## 8. File & Directory Rules

### 8.1 — Structure

```
laundry-in/
├── app/
│   ├── Config/          ← Routes, Database config
│   ├── Controllers/     ← Business logic
│   ├── Database/
│   │   ├── Migrations/  ← File migration CI4
│   │   └── Seeds/       ← File seeder CI4
│   ├── Helpers/         ← Fungsi bantuan (auth, csrf)
│   ├── Libraries/       ← Library custom (Cart, dll)
│   ├── Models/          ← Query database
│   └── Views/           ← Template HTML
├── assets/              ← CSS, JS (BUKAN di public/)
├── docs/                ← Dokumentasi
├── writable/            ← Logs, cache (CI4)
└── vendor/              ← Composer dependencies
```

### 8.2 — Naming Convention

- ✅ Model: `NamaModel.php` (PascalCase)
- ✅ Controller: `NamaController.php` (PascalCase)
- ✅ View: `nama-action.php` (lowercase, kebab-case)
- ✅ Migration: `YYYY-MM-DD-XXXXXX_DescriptiveName.php`
- ✅ Seeder: `NamaSeeder.php` (PascalCase)

---

## 9. Cart Library Rules (SOAL 05)

### 9.1 — Method Wajib

Cart library WAJIB memiliki method berikut:

- `insert($id, $data)` — Tambah item
- `update($id, $qty)` — Ubah quantity
- `total()` — Hitung total harga
- `remove($id)` — Hapus item spesifik
- `destroy()` — Kosongkan semua

### 9.2 — Session-Based

- ✅ Cart disimpan di `$_SESSION['shopping_cart']`
- ❌ JANGAN simpan cart di database untuk assignment ini (kecuali diminta)

---

## 10. Dompdf Rules (SOAL 04)

### 10.1 — Export PDF

- ✅ Export PDF menggunakan library `dompdf/dompdf` via Composer
- ✅ Format: Landscape A4
- ✅ File di-stream ke browser (bukan di-save ke server)
- ✅ Tombol export di halaman daftar layanan

---

## 11. Git & Commit Rules

### 11.1 — Commit Message Format

```
<type>: <description>

Types: feat, fix, refactor, docs, style, chore
Contoh:
- feat: add pelanggan CRUD with soft delete
- fix: css asset path not loading on production
- docs: update README with setup instructions
```

### 11.2 — File yang WAJIB di-gitignore

```
.env
node_modules/
.DS_Store
Thumbs.db
*.log
```

---

## 12. Checklist Sebelum Push

- [ ] `php -l` semua file PHP (cek syntax error)
- [ ] Cek semua halaman di browser — apakah muncul?
- [ ] Cek console browser — adakah 404 untuk CSS/JS?
- [ ] Cek mode dark/light toggle
- [ ] Cek mobile responsive
- [ ] Cek CSRF token ada di semua form POST
- [ ] Cek XSS: input `<script>alert(1)</script>` — harusnya tidak jalan
- [ ] Cek double click pada tombol — harusnya tidak double submit
- [ ] Cek bahwa tidak ada emoji di UI
- [ ] Cek bahwa font Inter/Poppins terload (bukan Times New Roman)

jangan mengubah code yang tidak semestinya dirubah, kalau misal itu perlu ya tinggal ditulis ulang saja.

jangan gunakan emoji sama sekali, karna saya tidak suka (paling benci emoji dalam coding). pakai lah icon, amu boleh download tambahan apa gitu untuk me-load icon tersebut, harusnya pasangan CI 4 untuk icon kan ada yaa. dan juga pastikan iconnya itu nyambung sama yang di maksut, bukan asal nyari gitu broo.
