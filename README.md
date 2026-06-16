# Laundry-IN — Sistem Manajemen Layanan Laundry

Web application untuk mengelola jenis layanan pada bisnis laundry berbasis web.

## Tech Stack

- **PHP 8.1+** (Native MVC Pattern)
- **MariaDB 10.6+** (kampusin_db)
- **PDO with Prepared Statements** (SQL Injection prevention)
- **Vanilla CSS + JS** (No framework, full design control)
- **Phosphor Icons** via CDN (no emoji)
- **Inter + Poppins** Font via Google Fonts

## Fitur

- ✅ **Dashboard** — Summary cards, recent services, quick actions
- ✅ **CRUD Lengkap** — Create, Read, Update untuk Jenis Layanan
- ✅ **Soft Delete** — Data tidak hilang, hanya diarsipkan
- ✅ **Arsip & Restore** — Pulihkan layanan yang telah dihapus
- ✅ **Dark Mode / Light Mode** — Toggle dengan persistensi localStorage
- ✅ **Fully Responsive** — Mobile (320px) hingga Desktop (1440px)
- ✅ **CSRF Protection** — Semua POST form dilindungi token CSRF
- ✅ **XSS Prevention** — Semua output melalui `htmlspecialchars()`
- ✅ **Phosphor Icons** — Tanpa emoji, ikon profesional
- ✅ **Anti-FOUC** — Theme diterapkan sebelum render

## Halaman

| Route                          | Deskripsi                        |
| ------------------------------ | -------------------------------- |
| `/login`                       | Halaman masuk admin              |
| `/dashboard`                   | Dashboard dengan ringkasan data  |
| `/layanan`                     | Daftar layanan aktif             |
| `/layanan/create`              | Tambah layanan baru              |
| `/layanan/edit/{id}`           | Edit layanan                     |
| `/layanan/archive`             | Arsip layanan yang dihapus       |

## Setup

1. **Clone repository** ke direktori web server (`htdocs/laundry-in/`):
   ```bash
   git clone https://github.com/CHUUL07/Laundry.git
   ```

2. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```
   Sesuaikan kredensial database di `.env`:
   ```
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=kampusin_db
   DB_USER=root
   DB_PASS=
   ```

3. **Import struktur database:**
   ```bash
   mysql -u root -p kampusin_db < docs/kampusin_db_structure.sql
   ```

4. **Import seed data (opsional):**
   ```bash
   mysql -u root -p kampusin_db < docs/kampusin_db_seed.sql
   ```

5. **Akses aplikasi:**
   Buka `http://localhost/laundry-in/`

## Login Default

- **Username:** `admin`
- **Password:** `admin123`

## Struktur Directory

```
laundry-in/
├── index.php              # Front Controller
├── .htaccess              # URL Rewriting
├── .env                   # Database credentials (gitignored)
├── app/
│   ├── config/Database.php
│   ├── controllers/
│   ├── helpers/auth.php
│   ├── models/
│   └── views/
├── public/assets/
│   ├── css/               # variables, reset, layout, components, utilities
│   └── js/                # theme, sidebar, modal
└── docs/
    ├── PRD.md
    ├── Planning.md
    ├── kampusin_db_structure.sql
    └── kampusin_db_seed.sql
```

## Keamanan

| Threat              | Mitigation                                        |
| ------------------- | ------------------------------------------------- |
| SQL Injection       | 100% PDO prepared statements dengan bound params  |
| XSS                 | Semua output via `htmlspecialchars()`             |
| CSRF                | Token CSRF di setiap form POST                    |
| Session Hijacking   | `session_regenerate_id()` saat login              |
| Password            | Bcrypt hash via `password_hash()` / `password_verify()` |
