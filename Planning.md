# Planning.md — Implementation Plan

# Project: Laundry-IN | Laundry Service Management Web App

**Version:** 1.0.0
**Format:** Sequential Phase Guide for GitHub Copilot & AI-Assisted Development
**Prerequisite Reading:** `PRD.md` (must be read before any implementation step)

---

> **INSTRUCTION FOR COPILOT/AI ASSISTANT:**
> Work through each phase in strict order. Do not skip phases. Each step within a phase must be completed and verified before proceeding to the next. Where SQL or PHP code is shown, it is the final expected output — generate it exactly as specified. File paths are relative to the project root `laundry-in/`.

---

## Table of Contents

- [Phase 1: Environment Setup & Project Scaffold](#phase-1-environment-setup--project-scaffold)
- [Phase 2: Database Configuration & Connection](#phase-2-database-configuration--connection)
- [Phase 3: Frontend Asset Pipeline & Design System](#phase-3-frontend-asset-pipeline--design-system)
- [Phase 4: Layout & View Templates](#phase-4-layout--view-templates)
- [Phase 5: Authentication Module](#phase-5-authentication-module)
- [Phase 6: Dashboard Module](#phase-6-dashboard-module)
- [Phase 7: Layanan CRUD — Read & List](#phase-7-layanan-crud--read--list)
- [Phase 8: Layanan CRUD — Create](#phase-8-layanan-crud--create)
- [Phase 9: Layanan CRUD — Edit & Update](#phase-9-layanan-crud--edit--update)
- [Phase 10: Layanan CRUD — Soft Delete, Archive & Restore](#phase-10-layanan-crud--soft-delete-archive--restore)
- [Phase 11: Polish, Accessibility & Testing](#phase-11-polish-accessibility--testing)
- [Phase 12: GitHub Upload & Submission](#phase-12-github-upload--submission)

---

## Phase 1: Environment Setup & Project Scaffold

### Step 1.1 — Verify Prerequisites

Confirm the following are installed and running on the development machine:

- [ ] PHP 8.1+ (`php -v` in terminal)
- [ ] MariaDB / MySQL 10.6+ (via XAMPP, WAMP, or standalone)
- [ ] Apache web server with `mod_rewrite` enabled
- [ ] Git (`git --version`)
- [ ] A code editor (VS Code recommended with GitHub Copilot extension)
- [ ] Browser DevTools for responsive testing

### Step 1.2 — Create Project Directory

```bash
# Navigate to your web server root
# XAMPP on Windows: C:/xampp/htdocs/
# XAMPP on macOS/Linux: /Applications/XAMPP/htdocs/ or /opt/lampp/htdocs/

mkdir laundry-in
cd laundry-in
```

### Step 1.3 — Create Full Directory Structure

Execute the following to build the complete folder tree:

```bash
mkdir -p app/config
mkdir -p app/controllers
mkdir -p app/models
mkdir -p app/views/layouts
mkdir -p app/views/auth
mkdir -p app/views/dashboard
mkdir -p app/views/layanan
mkdir -p public/assets/css
mkdir -p public/assets/js
mkdir -p docs
```

Verify with: `find . -type d | sort`

Expected output should match the directory tree in PRD.md Section 9.3.

### Step 1.4 — Create `.gitignore`

Create file: `.gitignore`

```gitignore
# Environment file with DB credentials
.env

# OS files
.DS_Store
Thumbs.db

# IDE files
.vscode/
.idea/

# Logs
*.log
error_log
```

### Step 1.5 — Create `.env` File

Create file: `.env`

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=kampusin_db
DB_USER=root
DB_PASS=
```

> **SECURITY NOTE:** This file is gitignored. The `.env` must never be committed to the repository. Provide a `.env.example` file (with blank values) for reference.

Create file: `.env.example`

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=kampusin_db
DB_USER=root
DB_PASS=
```

### Step 1.6 — Create `.htaccess` for URL Rewriting

Create file: `.htaccess`

```apache
Options -Indexes
RewriteEngine On

# Redirect all requests to index.php (Front Controller)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Block direct access to app/ directory
RewriteRule ^app/ - [F,L]
```

> **Verification:** Go to `http://localhost/laundry-in/anything-random` — it should route through `index.php` (will show errors until index.php is created, but should NOT show a 403/directory listing).

---

## Phase 2: Database Configuration & Connection

### Step 2.1 — Create Database Tables in `kampusin_db`

Open phpMyAdmin or MySQL client and run the following SQL against the **existing** `kampusin_db` database:

```sql
USE kampusin_db;

-- Table: admins
CREATE TABLE IF NOT EXISTS `admins` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(50)  NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: jenis_layanan
CREATE TABLE IF NOT EXISTS `jenis_layanan` (
    `id`               INT(11)                        NOT NULL AUTO_INCREMENT,
    `nama_layanan`     VARCHAR(100)                   NOT NULL,
    `kategori`         ENUM('express', 'reguler')     NOT NULL,
    `harga`            INT(11)                        NOT NULL,
    `satuan_harga`     ENUM('kg', 'item', 'paket')   NOT NULL DEFAULT 'kg',
    `estimasi_durasi`  VARCHAR(50)                    NOT NULL,
    `deskripsi`        TEXT                           NULL DEFAULT NULL,
    `created_at`       DATETIME                       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME                       NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`       DATETIME                       NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 2.2 — Seed Admin User

```sql
-- Generate bcrypt hash for 'admin123' in PHP:
-- echo password_hash('admin123', PASSWORD_BCRYPT);
-- Copy the output and paste below

INSERT INTO `admins` (`username`, `password`) VALUES (
    'admin',
    '$2y$12$replacethiswithactualbcrypthashgeneratedinphp'
);
```

> **IMPORTANT:** Run `php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"` in terminal, copy the output hash (starts with `$2y$`), then paste it into the SQL above.

### Step 2.3 — Seed Sample Service Data

```sql
INSERT INTO `jenis_layanan` (`nama_layanan`, `kategori`, `harga`, `satuan_harga`, `estimasi_durasi`, `deskripsi`) VALUES
('Cuci Express',     'express',  8000,  'kg',    '2-3 Jam',   'Cuci cepat selesai hari itu juga, cocok untuk kebutuhan mendesak.'),
('Cuci Reguler',     'reguler',  5000,  'kg',    '1-2 Hari',  'Layanan cuci standar dengan kualitas terjaga.'),
('Setrika Saja',     'reguler',  4000,  'kg',    '6 Jam',     'Hanya setrika tanpa cuci, untuk pakaian bersih yang kusut.'),
('Cuci + Setrika',   'express',  12000, 'kg',    '3-4 Jam',   'Paket lengkap cuci dan setrika, hasil rapi langsung bisa dipakai.'),
('Cuci Sepatu',      'reguler',  25000, 'item',  '1 Hari',    'Cuci bersih sepatu dengan metode khusus, aman untuk berbagai bahan.'),
('Laundry Paket',    'reguler',  35000, 'paket', '2 Hari',    'Paket hemat untuk 5kg cucian termasuk cuci dan setrika.');
```

### Step 2.4 — Create Database Connection Class

Create file: `app/config/Database.php`

```php
<?php

class Database
{
    private static ?PDO $instance = null;

    /**
     * Returns a singleton PDO connection instance.
     * Reads credentials from .env file at project root.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // Load .env file
            $envPath = dirname(__DIR__, 2) . '/.env';
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (str_starts_with(trim($line), '#')) continue;
                    if (str_contains($line, '=')) {
                        [$key, $value] = explode('=', $line, 2);
                        $_ENV[trim($key)] = trim($value);
                    }
                }
            }

            $host    = $_ENV['DB_HOST'] ?? 'localhost';
            $port    = $_ENV['DB_PORT'] ?? '3306';
            $dbname  = $_ENV['DB_NAME'] ?? 'kampusin_db';
            $user    = $_ENV['DB_USER'] ?? 'root';
            $pass    = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // In production, log this — never expose connection strings
                error_log('Database connection failed: ' . $e->getMessage());
                http_response_code(500);
                die('<h1>Koneksi database gagal. Hubungi administrator.</h1>');
            }
        }

        return self::$instance;
    }

    // Prevent instantiation and cloning
    private function __construct() {}
    private function __clone() {}
}
```

### Step 2.5 — Create Base Model

Create file: `app/models/BaseModel.php`

```php
<?php

require_once __DIR__ . '/../config/Database.php';

abstract class BaseModel
{
    protected PDO $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Run a raw query and return all results.
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Run a query and return a single row.
     */
    protected function queryOne(string $sql, array $params = []): array|false
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Execute a write query (INSERT, UPDATE, DELETE).
     * Returns number of affected rows.
     */
    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Returns the last auto-incremented ID after an INSERT.
     */
    protected function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }
}
```

### Step 2.6 — Test Database Connection

Create file: `test_db.php` (temporary — delete after verification)

```php
<?php
require_once 'app/config/Database.php';

try {
    $db = Database::getConnection();
    $result = $db->query("SELECT COUNT(*) as total FROM admins")->fetch(PDO::FETCH_ASSOC);
    echo "Connection OK. Admin count: " . $result['total'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

Visit `http://localhost/laundry-in/test_db.php`. Expected output: `Connection OK. Admin count: 1`. Then delete this file.

---

## Phase 3: Frontend Asset Pipeline & Design System

### Step 3.1 — Create CSS Variables File

Create file: `public/assets/css/variables.css`

```css
/* ============================================
   LAUNDRY-IN Design System — CSS Custom Properties
   ============================================ */

/* --- LIGHT MODE (Default) --- */
:root {
  /* Backgrounds */
  --color-bg-base: #f7f8fa;
  --color-bg-surface: #ffffff;
  --color-bg-elevated: #eceef2;

  /* Brand Colors */
  --color-primary: #0d9488; /* Teal 600 */
  --color-primary-soft: #ccfbf1; /* Teal 100 */
  --color-primary-dark: #0f766e; /* Teal 700 — hover */
  --color-accent: #f59e0b; /* Amber 500 */
  --color-accent-soft: #fef3c7; /* Amber 100 */

  /* Text */
  --color-text-primary: #111827;
  --color-text-secondary: #6b7280;
  --color-text-muted: #9ca3af;
  --color-text-inverse: #ffffff;

  /* Semantic */
  --color-success: #10b981;
  --color-success-soft: #d1fae5;
  --color-danger: #ef4444;
  --color-danger-soft: #fee2e2;
  --color-warning: #f59e0b;
  --color-warning-soft: #fef3c7;
  --color-info: #3b82f6;
  --color-info-soft: #dbeafe;

  /* Borders */
  --color-border: #e5e7eb;
  --color-border-focus: #0d9488;

  /* Sidebar (intentionally always dark) */
  --sidebar-bg: #0d1117;
  --sidebar-text: #94a3b8;
  --sidebar-text-hover: #e2e8f0;
  --sidebar-active-bg: rgba(13, 148, 136, 0.15);
  --sidebar-active-text: #14b8a6;
  --sidebar-active-bar: #0d9488;
  --sidebar-border: rgba(255, 255, 255, 0.06);

  /* Shadows */
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04);
  --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.06);
  --shadow-xl: 0 20px 50px rgba(0, 0, 0, 0.12), 0 8px 16px rgba(0, 0, 0, 0.06);

  /* Geometry */
  --radius-xs: 4px;
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 16px;
  --radius-xl: 24px;
  --radius-full: 9999px;

  /* Layout */
  --sidebar-width: 260px;
  --sidebar-width-mini: 64px;
  --topbar-height: 64px;
  --content-max-width: 1200px;

  /* Typography */
  --font-base:
    "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  --font-display: "Poppins", "Inter", sans-serif;

  /* Text Scale */
  --text-xs: 0.75rem;
  --text-sm: 0.875rem;
  --text-base: 1rem;
  --text-lg: 1.125rem;
  --text-xl: 1.25rem;
  --text-2xl: 1.5rem;
  --text-3xl: 1.875rem;
  --text-4xl: 2.25rem;

  /* Spacing Scale */
  --space-1: 0.25rem;
  --space-2: 0.5rem;
  --space-3: 0.75rem;
  --space-4: 1rem;
  --space-5: 1.25rem;
  --space-6: 1.5rem;
  --space-8: 2rem;
  --space-10: 2.5rem;
  --space-12: 3rem;

  /* Transitions */
  --transition-fast: 150ms ease;
  --transition-base: 200ms ease;
  --transition-slow: 300ms ease;
  --transition-bounce: 400ms cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* --- DARK MODE --- */
.dark {
  --color-bg-base: #0f1117;
  --color-bg-surface: #1a1d27;
  --color-bg-elevated: #252836;

  --color-primary: #14b8a6;
  --color-primary-soft: #134e4a;
  --color-primary-dark: #0d9488;
  --color-accent: #fbbf24;
  --color-accent-soft: #78350f;

  --color-text-primary: #f1f5f9;
  --color-text-secondary: #94a3b8;
  --color-text-muted: #64748b;
  --color-text-inverse: #111827;

  --color-success: #34d399;
  --color-success-soft: #064e3b;
  --color-danger: #f87171;
  --color-danger-soft: #450a0a;
  --color-warning: #fbbf24;
  --color-warning-soft: #451a03;
  --color-info: #60a5fa;
  --color-info-soft: #1e3a5f;

  --color-border: #2d3247;
  --color-border-focus: #14b8a6;

  /* Sidebar stays the same dark in dark mode */
  --sidebar-bg: #0d1117;
  --sidebar-text: #64748b;
  --sidebar-text-hover: #cbd5e1;

  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3), 0 1px 2px rgba(0, 0, 0, 0.2);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.4), 0 2px 4px rgba(0, 0, 0, 0.2);
  --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.5), 0 4px 8px rgba(0, 0, 0, 0.3);
}
```

### Step 3.2 — Create CSS Reset File

Create file: `public/assets/css/reset.css`

```css
/* ============================================
   Laundry-IN — CSS Reset & Base Styles
   ============================================ */

*,
*::before,
*::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html {
  font-size: 16px;
  scroll-behavior: smooth;
  -webkit-text-size-adjust: 100%;
}

body {
  font-family: var(--font-base);
  font-size: var(--text-base);
  font-weight: 400;
  line-height: 1.6;
  color: var(--color-text-primary);
  background-color: var(--color-bg-base);
  transition:
    background-color var(--transition-slow),
    color var(--transition-slow);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

h1,
h2,
h3,
h4,
h5,
h6 {
  font-family: var(--font-display);
  font-weight: 600;
  line-height: 1.3;
  color: var(--color-text-primary);
}

a {
  color: var(--color-primary);
  text-decoration: none;
  transition: color var(--transition-fast);
}

a:hover {
  color: var(--color-primary-dark);
}

img,
svg {
  display: block;
  max-width: 100%;
}

input,
textarea,
select,
button {
  font-family: inherit;
  font-size: inherit;
}

button {
  cursor: pointer;
  border: none;
  background: none;
}

ul,
ol {
  list-style: none;
}

table {
  border-collapse: collapse;
  width: 100%;
}

/* Focus styles — accessible, not ugly */
:focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
  border-radius: var(--radius-sm);
}

/* Scrollbar styling */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: var(--color-border);
  border-radius: var(--radius-full);
}
::-webkit-scrollbar-thumb:hover {
  background: var(--color-text-muted);
}
```

### Step 3.3 — Create Layout CSS

Create file: `public/assets/css/layout.css`

```css
/* ============================================
   Laundry-IN — Layout: Sidebar, Topbar, Grid
   ============================================ */

/* ---- App Shell ---- */
.app-shell {
  display: flex;
  min-height: 100vh;
}

/* ---- Sidebar ---- */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: var(--sidebar-width);
  height: 100vh;
  background-color: var(--sidebar-bg);
  display: flex;
  flex-direction: column;
  z-index: 100;
  border-right: 1px solid var(--sidebar-border);
  transition: transform var(--transition-slow);
  overflow-y: auto;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-6) var(--space-6);
  border-bottom: 1px solid var(--sidebar-border);
}

.sidebar-brand-icon {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, var(--color-primary), #0f766e);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.sidebar-brand-name {
  font-family: var(--font-display);
  font-size: var(--text-lg);
  font-weight: 700;
  color: #f1f5f9;
  letter-spacing: -0.02em;
}

.sidebar-brand-sub {
  font-size: var(--text-xs);
  color: var(--sidebar-text);
  margin-top: 1px;
}

.sidebar-nav {
  flex: 1;
  padding: var(--space-4) var(--space-3);
}

.sidebar-section-label {
  font-size: 0.65rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--sidebar-text);
  padding: var(--space-4) var(--space-3) var(--space-2);
}

.sidebar-nav-item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-4);
  border-radius: var(--radius-md);
  color: var(--sidebar-text);
  font-size: var(--text-sm);
  font-weight: 500;
  margin-bottom: 2px;
  transition:
    background-color var(--transition-fast),
    color var(--transition-fast),
    padding-left var(--transition-fast);
  position: relative;
}

.sidebar-nav-item:hover {
  background-color: rgba(255, 255, 255, 0.05);
  color: var(--sidebar-text-hover);
  padding-left: calc(var(--space-4) + 4px);
}

.sidebar-nav-item.active {
  background-color: var(--sidebar-active-bg);
  color: var(--sidebar-active-text);
}

.sidebar-nav-item.active::before {
  content: "";
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 60%;
  background-color: var(--sidebar-active-bar);
  border-radius: 0 var(--radius-full) var(--radius-full) 0;
}

.sidebar-nav-item i {
  font-size: 1.1rem;
  flex-shrink: 0;
}

.sidebar-footer {
  padding: var(--space-4) var(--space-3);
  border-top: 1px solid var(--sidebar-border);
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-3);
  border-radius: var(--radius-md);
  margin-bottom: var(--space-2);
}

.sidebar-user-avatar {
  width: 34px;
  height: 34px;
  background: linear-gradient(
    135deg,
    var(--color-primary-soft),
    var(--color-primary)
  );
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--color-primary-dark);
  flex-shrink: 0;
}

.sidebar-user-name {
  font-size: var(--text-sm);
  font-weight: 500;
  color: #cbd5e1;
}

.sidebar-user-role {
  font-size: var(--text-xs);
  color: var(--sidebar-text);
}

.sidebar-logout {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  width: 100%;
  padding: var(--space-3) var(--space-4);
  border-radius: var(--radius-md);
  color: var(--sidebar-text);
  font-size: var(--text-sm);
  font-weight: 500;
  transition:
    background-color var(--transition-fast),
    color var(--transition-fast);
}

.sidebar-logout:hover {
  background-color: rgba(239, 68, 68, 0.1);
  color: #f87171;
}

/* ---- Main Content ---- */
.main-content {
  margin-left: var(--sidebar-width);
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  transition: margin-left var(--transition-slow);
}

/* ---- Topbar ---- */
.topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  height: var(--topbar-height);
  background-color: var(--color-bg-surface);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--space-8);
  gap: var(--space-4);
  transition:
    background-color var(--transition-slow),
    border-color var(--transition-slow);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: var(--space-4);
}

.topbar-hamburger {
  display: none;
  padding: var(--space-2);
  border-radius: var(--radius-md);
  color: var(--color-text-secondary);
  transition:
    background-color var(--transition-fast),
    color var(--transition-fast);
}

.topbar-hamburger:hover {
  background-color: var(--color-bg-elevated);
  color: var(--color-text-primary);
}

.topbar-breadcrumb {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
}

.topbar-breadcrumb .current {
  color: var(--color-text-primary);
  font-weight: 500;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.theme-toggle {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-secondary);
  background: transparent;
  transition:
    background-color var(--transition-fast),
    color var(--transition-fast);
  font-size: 1.1rem;
}

.theme-toggle:hover {
  background-color: var(--color-bg-elevated);
  color: var(--color-text-primary);
}

.theme-toggle i {
  transition: transform var(--transition-bounce);
}

.theme-toggle:active i {
  transform: rotate(360deg);
}

/* ---- Page Content Area ---- */
.page-content {
  flex: 1;
  padding: var(--space-8);
  max-width: var(--content-max-width);
  width: 100%;
}

.page-header {
  margin-bottom: var(--space-8);
}

.page-title {
  font-size: var(--text-2xl);
  font-weight: 700;
  color: var(--color-text-primary);
  letter-spacing: -0.02em;
}

.page-subtitle {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  margin-top: var(--space-1);
}

/* ---- Grid Layouts ---- */
.grid-4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--space-6);
}

.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-6);
}

.grid-2 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-6);
}

/* ---- Sidebar Overlay (mobile) ---- */
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 99;
  backdrop-filter: blur(2px);
  opacity: 0;
  transition: opacity var(--transition-base);
}

.sidebar-overlay.visible {
  opacity: 1;
}

/* ---- Responsive ---- */
@media (max-width: 1023px) {
  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .sidebar-overlay {
    display: block;
  }

  .main-content {
    margin-left: 0;
  }

  .topbar-hamburger {
    display: flex;
  }

  .grid-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 767px) {
  .topbar {
    padding: 0 var(--space-4);
  }

  .page-content {
    padding: var(--space-4);
  }

  .grid-4,
  .grid-3,
  .grid-2 {
    grid-template-columns: 1fr;
  }

  .topbar-breadcrumb {
    display: none;
  }
}
```

### Step 3.4 — Create Components CSS

Create file: `public/assets/css/components.css`

```css
/* ============================================
   Laundry-IN — UI Components
   ============================================ */

/* ---- Buttons ---- */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  padding: 0.6rem 1.25rem;
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  font-weight: 500;
  line-height: 1;
  cursor: pointer;
  border: none;
  text-decoration: none;
  white-space: nowrap;
  transition:
    background-color var(--transition-fast),
    transform var(--transition-fast),
    box-shadow var(--transition-fast),
    color var(--transition-fast);
  position: relative;
  overflow: hidden;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}
.btn:active {
  transform: translateY(0);
  box-shadow: var(--shadow-sm);
}

.btn-primary {
  background-color: var(--color-primary);
  color: var(--color-text-inverse);
}
.btn-primary:hover {
  background-color: var(--color-primary-dark);
  color: var(--color-text-inverse);
}

.btn-danger {
  background-color: var(--color-danger);
  color: white;
}
.btn-danger:hover {
  background-color: #dc2626;
  color: white;
}

.btn-secondary {
  background-color: var(--color-bg-elevated);
  color: var(--color-text-primary);
  border: 1px solid var(--color-border);
}
.btn-secondary:hover {
  background-color: var(--color-border);
}

.btn-ghost {
  background-color: transparent;
  color: var(--color-text-secondary);
}
.btn-ghost:hover {
  background-color: var(--color-bg-elevated);
  color: var(--color-text-primary);
}

.btn-success {
  background-color: var(--color-success);
  color: white;
}
.btn-success:hover {
  background-color: #059669;
  color: white;
}

.btn-sm {
  padding: 0.4rem 0.875rem;
  font-size: var(--text-xs);
  gap: var(--space-1);
}

.btn-lg {
  padding: 0.75rem 1.75rem;
  font-size: var(--text-base);
}

.btn i {
  font-size: 1em;
}

/* ---- Cards ---- */
.card {
  background-color: var(--color-bg-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  transition:
    background-color var(--transition-slow),
    border-color var(--transition-slow),
    box-shadow var(--transition-base),
    transform var(--transition-base);
}

.card:hover {
  box-shadow: var(--shadow-md);
}

.card-body {
  padding: var(--space-6);
}

.card-header {
  padding: var(--space-5) var(--space-6);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card-title {
  font-size: var(--text-base);
  font-weight: 600;
  color: var(--color-text-primary);
}

/* ---- Summary Cards ---- */
.summary-card {
  background-color: var(--color-bg-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  padding: var(--space-6);
  box-shadow: var(--shadow-sm);
  transition:
    transform var(--transition-base),
    box-shadow var(--transition-base);
  cursor: default;
  overflow: hidden;
  position: relative;
}

.summary-card::after {
  content: "";
  position: absolute;
  top: -20px;
  right: -20px;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: var(--summary-accent, var(--color-primary-soft));
  opacity: 0.4;
  transition: transform var(--transition-slow);
}

.summary-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-lg);
}

.summary-card:hover::after {
  transform: scale(1.5);
}

.summary-card-icon {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  margin-bottom: var(--space-4);
  background-color: var(--summary-accent, var(--color-primary-soft));
  color: var(--summary-icon-color, var(--color-primary));
}

.summary-card-number {
  font-family: var(--font-display);
  font-size: var(--text-3xl);
  font-weight: 700;
  color: var(--color-text-primary);
  line-height: 1;
  margin-bottom: var(--space-1);
}

.summary-card-label {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  font-weight: 500;
}

.summary-card-divider {
  height: 1px;
  background-color: var(--color-border);
  margin: var(--space-4) 0;
}

.summary-card-meta {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
}

/* ---- Badges ---- */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  border-radius: var(--radius-full);
  font-size: var(--text-xs);
  font-weight: 600;
  letter-spacing: 0.02em;
  text-transform: capitalize;
}

.badge-express {
  background-color: var(--color-primary-soft);
  color: var(--color-primary);
}

.badge-reguler {
  background-color: var(--color-bg-elevated);
  color: var(--color-text-secondary);
}

.badge-success {
  background-color: var(--color-success-soft);
  color: var(--color-success);
}

.badge-danger {
  background-color: var(--color-danger-soft);
  color: var(--color-danger);
}

.badge-warning {
  background-color: var(--color-warning-soft);
  color: var(--color-warning);
}

/* ---- Data Table ---- */
.table-wrapper {
  overflow-x: auto;
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: var(--text-sm);
}

.data-table thead {
  background-color: var(--color-bg-elevated);
}

.data-table th {
  padding: var(--space-3) var(--space-5);
  text-align: left;
  font-size: var(--text-xs);
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--color-text-secondary);
  border-bottom: 1px solid var(--color-border);
  white-space: nowrap;
}

.data-table td {
  padding: var(--space-4) var(--space-5);
  border-bottom: 1px solid var(--color-border);
  color: var(--color-text-primary);
  vertical-align: middle;
}

.data-table tbody tr {
  transition: background-color var(--transition-fast);
}

.data-table tbody tr:hover {
  background-color: var(--color-bg-elevated);
}

.data-table tbody tr:last-child td {
  border-bottom: none;
}

.table-actions {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

/* ---- Forms ---- */
.form-group {
  margin-bottom: var(--space-5);
}

.form-label {
  display: block;
  font-size: var(--text-sm);
  font-weight: 500;
  color: var(--color-text-primary);
  margin-bottom: var(--space-2);
}

.form-label span.required {
  color: var(--color-danger);
  margin-left: 2px;
}

.form-control {
  display: block;
  width: 100%;
  padding: 0.6rem 0.875rem;
  font-size: var(--text-sm);
  font-family: var(--font-base);
  color: var(--color-text-primary);
  background-color: var(--color-bg-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  transition:
    border-color var(--transition-fast),
    box-shadow var(--transition-fast),
    background-color var(--transition-slow);
  outline: none;
}

.form-control:focus {
  border-color: var(--color-border-focus);
  box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
}

.form-control::placeholder {
  color: var(--color-text-muted);
}

.form-control-error {
  border-color: var(--color-danger) !important;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
}

.form-error-msg {
  font-size: var(--text-xs);
  color: var(--color-danger);
  margin-top: var(--space-1);
  display: flex;
  align-items: center;
  gap: 4px;
}

.form-hint {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  margin-top: var(--space-1);
}

textarea.form-control {
  min-height: 100px;
  resize: vertical;
}

select.form-control {
  cursor: pointer;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-5);
}

/* ---- Flash / Alert Messages ---- */
.alert {
  display: flex;
  align-items: flex-start;
  gap: var(--space-3);
  padding: var(--space-4) var(--space-5);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  margin-bottom: var(--space-6);
  animation: fadeSlideDown 300ms ease forwards;
}

@keyframes fadeSlideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.alert-success {
  background-color: var(--color-success-soft);
  color: var(--color-success);
  border-left: 4px solid var(--color-success);
}

.alert-danger {
  background-color: var(--color-danger-soft);
  color: var(--color-danger);
  border-left: 4px solid var(--color-danger);
}

.alert-warning {
  background-color: var(--color-warning-soft);
  color: var(--color-warning);
  border-left: 4px solid var(--color-warning);
}

.alert i {
  font-size: 1.1rem;
  flex-shrink: 0;
  margin-top: 1px;
}

/* ---- Modal ---- */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(3px);
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition:
    opacity var(--transition-base),
    visibility var(--transition-base);
}

.modal-backdrop.open {
  opacity: 1;
  visibility: visible;
}

.modal {
  background-color: var(--color-bg-surface);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-xl);
  width: 90%;
  max-width: 440px;
  padding: var(--space-8);
  transform: scale(0.95) translateY(10px);
  transition: transform var(--transition-bounce);
}

.modal-backdrop.open .modal {
  transform: scale(1) translateY(0);
}

.modal-icon {
  width: 56px;
  height: 56px;
  border-radius: var(--radius-xl);
  background-color: var(--color-danger-soft);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: var(--color-danger);
  margin: 0 auto var(--space-5);
}

.modal-title {
  font-size: var(--text-xl);
  font-weight: 700;
  text-align: center;
  margin-bottom: var(--space-2);
}

.modal-body {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  text-align: center;
  margin-bottom: var(--space-6);
  line-height: 1.7;
}

.modal-actions {
  display: flex;
  gap: var(--space-3);
}

.modal-actions .btn {
  flex: 1;
  justify-content: center;
}

/* ---- Quick Action Shortcuts ---- */
.shortcut-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-4);
  margin-top: var(--space-4);
}

.shortcut-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  padding: var(--space-5) var(--space-4);
  background-color: var(--color-bg-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  text-align: center;
  transition:
    background-color var(--transition-fast),
    transform var(--transition-fast),
    box-shadow var(--transition-fast),
    border-color var(--transition-fast);
  color: var(--color-text-primary);
}

.shortcut-card:hover {
  background-color: var(--color-primary-soft);
  border-color: var(--color-primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  color: var(--color-primary);
}

.shortcut-card i {
  font-size: 1.5rem;
  color: var(--color-primary);
  transition: transform var(--transition-bounce);
}

.shortcut-card:hover i {
  transform: scale(1.15);
}

.shortcut-card-label {
  font-size: var(--text-xs);
  font-weight: 500;
}

/* ---- Empty State ---- */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--space-12) var(--space-8);
  text-align: center;
  color: var(--color-text-secondary);
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: var(--space-4);
  opacity: 0.4;
}

.empty-state-title {
  font-size: var(--text-lg);
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: var(--space-2);
}

.empty-state-text {
  font-size: var(--text-sm);
  margin-bottom: var(--space-6);
}

/* ---- Section Divider ---- */
.section-gap {
  margin-top: var(--space-8);
}
.section-gap-sm {
  margin-top: var(--space-4);
}
```

### Step 3.5 — Create Utility CSS

Create file: `public/assets/css/utilities.css`

```css
/* ============================================
   Laundry-IN — Utility Classes
   ============================================ */

/* Display */
.flex {
  display: flex;
}
.grid {
  display: grid;
}
.block {
  display: block;
}
.hidden {
  display: none;
}

/* Flex utilities */
.items-center {
  align-items: center;
}
.items-start {
  align-items: flex-start;
}
.justify-between {
  justify-content: space-between;
}
.justify-end {
  justify-content: flex-end;
}
.justify-center {
  justify-content: center;
}
.flex-1 {
  flex: 1;
}
.flex-col {
  flex-direction: column;
}
.gap-2 {
  gap: var(--space-2);
}
.gap-3 {
  gap: var(--space-3);
}
.gap-4 {
  gap: var(--space-4);
}
.gap-6 {
  gap: var(--space-6);
}

/* Text */
.text-primary {
  color: var(--color-text-primary);
}
.text-secondary {
  color: var(--color-text-secondary);
}
.text-muted {
  color: var(--color-text-muted);
}
.text-danger {
  color: var(--color-danger);
}
.text-success {
  color: var(--color-success);
}
.text-brand {
  color: var(--color-primary);
}
.text-sm {
  font-size: var(--text-sm);
}
.text-xs {
  font-size: var(--text-xs);
}
.text-lg {
  font-size: var(--text-lg);
}
.text-xl {
  font-size: var(--text-xl);
}
.font-medium {
  font-weight: 500;
}
.font-semibold {
  font-weight: 600;
}
.font-bold {
  font-weight: 700;
}
.text-center {
  text-align: center;
}

/* Spacing */
.mb-0 {
  margin-bottom: 0;
}
.mb-2 {
  margin-bottom: var(--space-2);
}
.mb-4 {
  margin-bottom: var(--space-4);
}
.mb-6 {
  margin-bottom: var(--space-6);
}
.mt-4 {
  margin-top: var(--space-4);
}
.mt-6 {
  margin-top: var(--space-6);
}
.mt-auto {
  margin-top: auto;
}

/* Width */
.w-full {
  width: 100%;
}

/* Overflow */
.overflow-hidden {
  overflow: hidden;
}

/* Responsive helpers */
@media (max-width: 767px) {
  .hide-mobile {
    display: none !important;
  }
  .form-row {
    grid-template-columns: 1fr;
  }
  .shortcut-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .table-actions {
    flex-direction: column;
  }
}
```

### Step 3.6 — Create JavaScript Files

Create file: `public/assets/js/theme.js`

```javascript
/**
 * Laundry-IN — Dark/Light Mode Toggle
 * Applies theme on page load and handles toggle clicks.
 */
(function () {
  const html = document.documentElement;
  const saved = localStorage.getItem("laundry-in-theme");
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

  // Apply saved or system theme before paint (prevents flash)
  if (saved === "dark" || (!saved && prefersDark)) {
    html.classList.add("dark");
  }

  document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("theme-toggle");
    const iconSun = document.getElementById("icon-sun");
    const iconMoon = document.getElementById("icon-moon");

    function updateIcons() {
      if (html.classList.contains("dark")) {
        iconSun && iconSun.classList.remove("hidden");
        iconMoon && iconMoon.classList.add("hidden");
      } else {
        iconSun && iconSun.classList.add("hidden");
        iconMoon && iconMoon.classList.remove("hidden");
      }
    }

    updateIcons();

    toggle &&
      toggle.addEventListener("click", function () {
        const isDark = html.classList.toggle("dark");
        localStorage.setItem("laundry-in-theme", isDark ? "dark" : "light");
        updateIcons();
      });
  });
})();
```

Create file: `public/assets/js/sidebar.js`

```javascript
/**
 * Laundry-IN — Mobile Sidebar Toggle
 */
document.addEventListener("DOMContentLoaded", function () {
  const hamburger = document.getElementById("sidebar-toggle");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebar-overlay");

  function openSidebar() {
    sidebar && sidebar.classList.add("open");
    overlay && overlay.classList.add("visible");
    document.body.style.overflow = "hidden";
  }

  function closeSidebar() {
    sidebar && sidebar.classList.remove("open");
    overlay && overlay.classList.remove("visible");
    document.body.style.overflow = "";
  }

  hamburger && hamburger.addEventListener("click", openSidebar);
  overlay && overlay.addEventListener("click", closeSidebar);

  // Close on nav item click (mobile UX)
  const navItems = document.querySelectorAll(".sidebar-nav-item");
  navItems.forEach((item) => item.addEventListener("click", closeSidebar));
});
```

Create file: `public/assets/js/modal.js`

```javascript
/**
 * Laundry-IN — Delete Confirmation Modal
 *
 * Usage: Add data-modal-target="delete-modal" data-service-name="Name"
 *        data-form-action="/layanan/delete/1" to the delete button.
 */
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("delete-modal");
  const backdrop = document.getElementById("modal-backdrop");
  const cancelBtn = document.getElementById("modal-cancel");
  const confirmBtn = document.getElementById("modal-confirm");
  const serviceNameEl = document.getElementById("modal-service-name");
  const deleteForm = document.getElementById("delete-form");

  function openModal(serviceName, formAction) {
    if (serviceNameEl) serviceNameEl.textContent = serviceName;
    if (deleteForm) deleteForm.setAttribute("action", formAction);
    backdrop && backdrop.classList.add("open");
  }

  function closeModal() {
    backdrop && backdrop.classList.remove("open");
  }

  // Attach to all delete trigger buttons
  document.querySelectorAll("[data-delete-trigger]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      openModal(
        this.getAttribute("data-service-name"),
        this.getAttribute("data-form-action"),
      );
    });
  });

  cancelBtn && cancelBtn.addEventListener("click", closeModal);
  backdrop &&
    backdrop.addEventListener("click", function (e) {
      if (e.target === backdrop) closeModal();
    });

  // Close on Escape key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeModal();
  });
});
```

---

## Phase 4: Layout & View Templates

### Step 4.1 — Create Main Layout

Create file: `app/views/layouts/main.php`

```php
<?php
// $pageTitle — set by each view before including layout
// $activePage — 'dashboard', 'layanan', 'arsip'
$pageTitle   = $pageTitle ?? 'Dashboard';
$activePage  = $activePage ?? 'dashboard';
$adminUser   = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Laundry-IN</title>

    <!-- Anti-FOUC: Apply theme immediately before render -->
    <script>
        (function(){
            var t = localStorage.getItem('laundry-in-theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) document.documentElement.classList.add('dark');
        })();
    </script>

    <!-- Google Fonts: Inter + Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <!-- Phosphor Icons CDN -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>

    <!-- Laundry-IN CSS -->
    <link rel="stylesheet" href="/laundry-in/public/assets/css/variables.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/reset.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/layout.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/components.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/utilities.css">
</head>
<body>

<div class="app-shell">

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- ============ SIDEBAR ============ -->
    <aside class="sidebar" id="sidebar">
        <!-- Brand -->
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="ph-bold ph-washing-machine"></i>
            </div>
            <div>
                <div class="sidebar-brand-name">Laundry-IN</div>
                <div class="sidebar-brand-sub">Management System</div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Menu Utama</div>

            <a href="/laundry-in/dashboard"
               class="sidebar-nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <i class="ph-bold ph-house"></i>
                Dashboard
            </a>

            <a href="/laundry-in/layanan"
               class="sidebar-nav-item <?= $activePage === 'layanan' ? 'active' : '' ?>">
                <i class="ph-bold ph-list-bullets"></i>
                Jenis Layanan
            </a>

            <div class="sidebar-section-label">Data</div>

            <a href="/laundry-in/layanan/archive"
               class="sidebar-nav-item <?= $activePage === 'arsip' ? 'active' : '' ?>">
                <i class="ph-bold ph-archive"></i>
                Arsip Terhapus
            </a>
        </nav>

        <!-- Footer: User Info + Logout -->
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?= strtoupper(substr($adminUser, 0, 1)) ?>
                </div>
                <div>
                    <div class="sidebar-user-name"><?= htmlspecialchars($adminUser) ?></div>
                    <div class="sidebar-user-role">Administrator</div>
                </div>
            </div>
            <a href="/laundry-in/logout" class="sidebar-logout">
                <i class="ph-bold ph-sign-out"></i>
                Keluar
            </a>
        </div>
    </aside>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-hamburger" id="sidebar-toggle" aria-label="Toggle sidebar">
                    <i class="ph-bold ph-list" style="font-size:1.3rem;"></i>
                </button>
                <nav class="topbar-breadcrumb" aria-label="Breadcrumb">
                    <a href="/laundry-in/dashboard">Beranda</a>
                    <?php if ($activePage !== 'dashboard'): ?>
                        <i class="ph ph-caret-right" style="font-size:0.75rem;"></i>
                        <span class="current"><?= htmlspecialchars($pageTitle) ?></span>
                    <?php endif; ?>
                </nav>
            </div>
            <div class="topbar-right">
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                    <i class="ph-bold ph-sun" id="icon-sun" class="hidden"></i>
                    <i class="ph-bold ph-moon" id="icon-moon"></i>
                </button>
            </div>
        </header>

        <!-- Flash Messages -->
        <div style="padding: 0 var(--space-8); padding-top: var(--space-6);">
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success">
                    <i class="ph-bold ph-check-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger">
                    <i class="ph-bold ph-warning-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
        </div>

        <!-- Page Content Slot -->
        <main class="page-content">
            <?= $content ?? '' ?>
        </main>

    </div><!-- /.main-content -->

</div><!-- /.app-shell -->

<!-- Delete Confirmation Modal -->
<div class="modal-backdrop" id="modal-backdrop">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-icon">
            <i class="ph-bold ph-trash"></i>
        </div>
        <h2 class="modal-title" id="modal-title">Hapus Layanan?</h2>
        <p class="modal-body">
            Layanan "<strong id="modal-service-name"></strong>" akan dipindahkan ke arsip.
            Data tidak akan hilang dan dapat dipulihkan kapan saja.
        </p>
        <div class="modal-actions">
            <button class="btn btn-secondary" id="modal-cancel">Batal</button>
            <form id="delete-form" method="POST">
                <?php
                // CSRF token in modal form
                if (!isset($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                ?>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit" class="btn btn-danger" id="modal-confirm">
                    <i class="ph-bold ph-trash"></i>
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="/laundry-in/public/assets/js/theme.js"></script>
<script src="/laundry-in/public/assets/js/sidebar.js"></script>
<script src="/laundry-in/public/assets/js/modal.js"></script>

</body>
</html>
```

### Step 4.2 — Create Auth Layout

Create file: `app/views/layouts/auth.php`

```php
<!DOCTYPE html>
<html lang="id" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Laundry-IN</title>
    <script>
        (function(){
            var t = localStorage.getItem('laundry-in-theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) document.documentElement.classList.add('dark');
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>
    <link rel="stylesheet" href="/laundry-in/public/assets/css/variables.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/reset.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/components.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/utilities.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--color-bg-base);
        }
        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: var(--space-4);
        }
        .auth-card {
            background-color: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            padding: var(--space-10);
            box-shadow: var(--shadow-lg);
        }
        .auth-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: var(--space-8);
        }
        .auth-brand-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--color-primary), #0F766E);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: var(--space-4);
        }
        .auth-brand-name {
            font-family: var(--font-display);
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--color-text-primary);
            letter-spacing: -0.03em;
        }
        .auth-brand-sub {
            font-size: var(--text-sm);
            color: var(--color-text-secondary);
            margin-top: 4px;
        }
        .auth-footer {
            text-align: center;
            margin-top: var(--space-6);
            font-size: var(--text-xs);
            color: var(--color-text-muted);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <?= $content ?? '' ?>
        <div class="auth-footer">Laundry-IN &copy; <?= date('Y') ?> — Sistem Manajemen Laundry</div>
    </div>
    <script src="/laundry-in/public/assets/js/theme.js"></script>
</body>
</html>
```

---

## Phase 5: Authentication Module

### Step 5.1 — Create Admin Model

Create file: `app/models/AdminModel.php`

```php
<?php

require_once __DIR__ . '/BaseModel.php';

class AdminModel extends BaseModel
{
    protected string $table = 'admins';

    /**
     * Find an admin record by username.
     * Returns the row array or false if not found.
     */
    public function findByUsername(string $username): array|false
    {
        return $this->queryOne(
            "SELECT id, username, password FROM {$this->table} WHERE username = :username LIMIT 1",
            [':username' => $username]
        );
    }
}
```

### Step 5.2 — Create Auth Controller

Create file: `app/controllers/AuthController.php`

```php
<?php

require_once __DIR__ . '/../models/AdminModel.php';

class AuthController
{
    private AdminModel $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    /**
     * GET /login — Show login form
     */
    public function showLogin(): void
    {
        // Already logged in? Redirect to dashboard
        if (isset($_SESSION['admin_id'])) {
            header('Location: /laundry-in/dashboard');
            exit;
        }

        $content = $this->renderView('auth/login.php', [
            'error' => $_SESSION['login_error'] ?? null,
        ]);
        unset($_SESSION['login_error']);

        ob_start();
        $pageVars = ['content' => $content];
        extract($pageVars);
        include __DIR__ . '/../views/layouts/auth.php';
        ob_end_flush();
    }

    /**
     * POST /login — Process login credentials
     */
    public function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /laundry-in/login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic input validation
        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Username dan password wajib diisi.';
            header('Location: /laundry-in/login');
            exit;
        }

        $admin = $this->adminModel->findByUsername($username);

        if ($admin && password_verify($password, $admin['password'])) {
            // Regenerate session ID to prevent fixation attacks
            session_regenerate_id(true);

            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: /laundry-in/dashboard');
            exit;
        }

        // Generic error — do not reveal whether username or password was wrong
        $_SESSION['login_error'] = 'Username atau password salah.';
        header('Location: /laundry-in/login');
        exit;
    }

    /**
     * GET /logout — Destroy session and redirect
     */
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        header('Location: /laundry-in/login');
        exit;
    }

    /**
     * Render a view file and return it as a string.
     */
    private function renderView(string $viewPath, array $data = []): string
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../views/' . $viewPath;
        return ob_get_clean();
    }
}
```

### Step 5.3 — Create Login View

Create file: `app/views/auth/login.php`

```php
<div class="auth-card">
    <div class="auth-brand">
        <div class="auth-brand-icon">
            <i class="ph-bold ph-washing-machine"></i>
        </div>
        <div class="auth-brand-name">Laundry-IN</div>
        <div class="auth-brand-sub">Masuk ke panel admin</div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: var(--space-5);">
            <i class="ph-bold ph-warning-circle"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="/laundry-in/login" novalidate>
        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                class="form-control"
                placeholder="Masukkan username"
                autocomplete="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                required
            >
        </div>

        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label class="form-label" for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Masukkan password"
                autocomplete="current-password"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary w-full btn-lg">
            <i class="ph-bold ph-sign-in"></i>
            Masuk
        </button>
    </form>
</div>
```

### Step 5.4 — Create Auth Middleware Helper

Create file: `app/helpers/auth.php`

```php
<?php

/**
 * Require authentication. If not logged in, redirect to /login.
 * Include this at the top of every protected controller method.
 */
function requireAuth(): void
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /laundry-in/login');
        exit;
    }
}

/**
 * Generate and verify CSRF tokens.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
```

---

## Phase 6: Dashboard Module

### Step 6.1 — Create Layanan Model (partial — counts only)

Create file: `app/models/LayananModel.php`

```php
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
    //  WRITE METHODS (added in Phase 8+)
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
     * The record remains in the database for historical reference.
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
```

### Step 6.2 — Create Dashboard Controller

Create file: `app/controllers/DashboardController.php`

```php
<?php

require_once __DIR__ . '/../models/LayananModel.php';
require_once __DIR__ . '/../helpers/auth.php';

class DashboardController
{
    private LayananModel $layananModel;

    public function __construct()
    {
        $this->layananModel = new LayananModel();
    }

    public function index(): void
    {
        requireAuth();

        $data = [
            'pageTitle'      => 'Dashboard',
            'activePage'     => 'dashboard',
            'totalAktif'     => $this->layananModel->countActive(),
            'totalExpress'   => $this->layananModel->countByKategori('express'),
            'totalReguler'   => $this->layananModel->countByKategori('reguler'),
            'totalArsip'     => $this->layananModel->countArchived(),
            'recentLayanan'  => $this->layananModel->recent(5),
        ];

        $this->render('dashboard/index.php', $data);
    }

    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../views/' . $viewPath;
        $content = ob_get_clean();

        include __DIR__ . '/../views/layouts/main.php';
    }
}
```

### Step 6.3 — Create Dashboard View

Create file: `app/views/dashboard/index.php`

```php
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Selamat Datang, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></h1>
    <p class="page-subtitle">Kelola semua jenis layanan laundry dari satu tempat.</p>
</div>

<!-- Summary Cards -->
<div class="grid-4">

    <!-- Total Aktif -->
    <div class="summary-card" style="--summary-accent: var(--color-primary-soft); --summary-icon-color: var(--color-primary);">
        <div class="summary-card-icon">
            <i class="ph-bold ph-stack"></i>
        </div>
        <div class="summary-card-number"><?= $totalAktif ?></div>
        <div class="summary-card-label">Total Layanan Aktif</div>
        <div class="summary-card-divider"></div>
        <div class="summary-card-meta">Semua layanan yang tersedia</div>
    </div>

    <!-- Total Express -->
    <div class="summary-card" style="--summary-accent: var(--color-accent-soft); --summary-icon-color: var(--color-accent);">
        <div class="summary-card-icon">
            <i class="ph-bold ph-lightning"></i>
        </div>
        <div class="summary-card-number"><?= $totalExpress ?></div>
        <div class="summary-card-label">Layanan Express</div>
        <div class="summary-card-divider"></div>
        <div class="summary-card-meta">Prioritas & cepat selesai</div>
    </div>

    <!-- Total Reguler -->
    <div class="summary-card" style="--summary-accent: var(--color-info-soft); --summary-icon-color: var(--color-info);">
        <div class="summary-card-icon">
            <i class="ph-bold ph-clock"></i>
        </div>
        <div class="summary-card-number"><?= $totalReguler ?></div>
        <div class="summary-card-label">Layanan Reguler</div>
        <div class="summary-card-divider"></div>
        <div class="summary-card-meta">Standar waktu normal</div>
    </div>

    <!-- Total Arsip -->
    <div class="summary-card" style="--summary-accent: var(--color-danger-soft); --summary-icon-color: var(--color-danger);">
        <div class="summary-card-icon">
            <i class="ph-bold ph-archive"></i>
        </div>
        <div class="summary-card-number"><?= $totalArsip ?></div>
        <div class="summary-card-label">Layanan Diarsipkan</div>
        <div class="summary-card-divider"></div>
        <div class="summary-card-meta">Dapat dipulihkan kapan saja</div>
    </div>

</div>

<!-- Quick Actions -->
<div class="section-gap">
    <h2 class="card-title mb-4">Akses Cepat</h2>
    <div class="shortcut-grid">
        <a href="/laundry-in/layanan/create" class="shortcut-card">
            <i class="ph-bold ph-plus-circle"></i>
            <span class="shortcut-card-label">Tambah Layanan</span>
        </a>
        <a href="/laundry-in/layanan" class="shortcut-card">
            <i class="ph-bold ph-list-bullets"></i>
            <span class="shortcut-card-label">Lihat Semua Layanan</span>
        </a>
        <a href="/laundry-in/layanan/archive" class="shortcut-card">
            <i class="ph-bold ph-archive"></i>
            <span class="shortcut-card-label">Lihat Arsip</span>
        </a>
    </div>
</div>

<!-- Recent Services -->
<div class="section-gap">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Layanan Terbaru</h2>
            <a href="/laundry-in/layanan" class="btn btn-ghost btn-sm">
                Lihat Semua
                <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>
        <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
            <?php if (empty($recentLayanan)): ?>
                <div class="empty-state">
                    <i class="ph-bold ph-note-blank"></i>
                    <div class="empty-state-title">Belum Ada Layanan</div>
                    <p class="empty-state-text">Tambahkan layanan pertama Anda sekarang.</p>
                    <a href="/laundry-in/layanan/create" class="btn btn-primary">
                        <i class="ph-bold ph-plus-circle"></i>
                        Tambah Layanan
                    </a>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Layanan</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Estimasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLayanan as $item): ?>
                        <tr>
                            <td class="font-medium"><?= htmlspecialchars($item['nama_layanan']) ?></td>
                            <td>
                                <span class="badge badge-<?= $item['kategori'] ?>">
                                    <?= ucfirst($item['kategori']) ?>
                                </span>
                            </td>
                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?> / <?= $item['satuan_harga'] ?></td>
                            <td class="text-secondary"><?= htmlspecialchars($item['estimasi_durasi']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
```

---

## Phase 7: Layanan CRUD — Read & List

### Step 7.1 — Create Layanan Controller (index method)

Create file: `app/controllers/LayananController.php`

```php
<?php

require_once __DIR__ . '/../models/LayananModel.php';
require_once __DIR__ . '/../helpers/auth.php';

class LayananController
{
    private LayananModel $model;

    public function __construct()
    {
        $this->model = new LayananModel();
    }

    /** GET /layanan — List all active services */
    public function index(): void
    {
        requireAuth();

        $layanan = $this->model->all();

        $this->render('layanan/index.php', [
            'pageTitle'  => 'Jenis Layanan',
            'activePage' => 'layanan',
            'layanan'    => $layanan,
        ]);
    }

    // ... remaining methods added in later phases

    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../views/' . $viewPath;
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    private function redirect(string $path, string $flashKey = '', string $flashMsg = ''): void
    {
        if ($flashKey && $flashMsg) {
            $_SESSION[$flashKey] = $flashMsg;
        }
        header("Location: /laundry-in{$path}");
        exit;
    }
}
```

### Step 7.2 — Create Layanan List View

Create file: `app/views/layanan/index.php`

```php
<div class="page-header flex items-center justify-between">
    <div>
        <h1 class="page-title">Jenis Layanan</h1>
        <p class="page-subtitle">Kelola semua tipe layanan laundry yang tersedia.</p>
    </div>
    <a href="/laundry-in/layanan/create" class="btn btn-primary">
        <i class="ph-bold ph-plus-circle"></i>
        Tambah Layanan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Layanan Aktif</h2>
        <span class="badge badge-success"><?= count($layanan) ?> layanan</span>
    </div>
    <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
        <?php if (empty($layanan)): ?>
            <div class="empty-state">
                <i class="ph-bold ph-note-blank"></i>
                <div class="empty-state-title">Tidak Ada Layanan</div>
                <p class="empty-state-text">Belum ada layanan yang ditambahkan.</p>
                <a href="/laundry-in/layanan/create" class="btn btn-primary">
                    <i class="ph-bold ph-plus-circle"></i>
                    Tambah Layanan Pertama
                </a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 48px;">No.</th>
                        <th>Nama Layanan</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Satuan</th>
                        <th>Estimasi</th>
                        <th>Deskripsi</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($layanan as $i => $item): ?>
                    <tr>
                        <td class="text-muted text-sm"><?= $i + 1 ?></td>
                        <td class="font-medium"><?= htmlspecialchars($item['nama_layanan']) ?></td>
                        <td>
                            <span class="badge badge-<?= $item['kategori'] ?>">
                                <?= $item['kategori'] === 'express'
                                    ? '<i class="ph-bold ph-lightning" style="font-size:0.7rem;"></i> Express'
                                    : 'Reguler' ?>
                            </span>
                        </td>
                        <td class="font-semibold text-brand">
                            Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                        </td>
                        <td class="text-secondary">/ <?= $item['satuan_harga'] ?></td>
                        <td class="text-secondary"><?= htmlspecialchars($item['estimasi_durasi']) ?></td>
                        <td class="text-secondary text-sm" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?= $item['deskripsi'] ? htmlspecialchars($item['deskripsi']) : '—' ?>
                        </td>
                        <td>
                            <div class="table-actions" style="justify-content: center;">
                                <a href="/laundry-in/layanan/edit/<?= $item['id'] ?>" class="btn btn-secondary btn-sm">
                                    <i class="ph-bold ph-pencil-simple"></i>
                                    Edit
                                </a>
                                <button
                                    class="btn btn-danger btn-sm"
                                    data-delete-trigger
                                    data-service-name="<?= htmlspecialchars($item['nama_layanan']) ?>"
                                    data-form-action="/laundry-in/layanan/delete/<?= $item['id'] ?>"
                                    type="button"
                                >
                                    <i class="ph-bold ph-trash"></i>
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
```

---

## Phase 8: Layanan CRUD — Create

### Step 8.1 — Add create() and store() to LayananController

Append these methods to `app/controllers/LayananController.php` (inside the class, before the private methods):

```php
/** GET /layanan/create — Show add form */
public function create(): void
{
    requireAuth();
    $this->render('layanan/create.php', [
        'pageTitle'  => 'Tambah Layanan',
        'activePage' => 'layanan',
        'errors'     => $_SESSION['form_errors'] ?? [],
        'old'        => $_SESSION['form_old'] ?? [],
    ]);
    unset($_SESSION['form_errors'], $_SESSION['form_old']);
}

/** POST /layanan/store — Process form submission */
public function store(): void
{
    requireAuth();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('/layanan');
    }

    $data   = $this->sanitizeInput($_POST);
    $errors = $this->validate($data);

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old']    = $data;
        $this->redirect('/layanan/create');
    }

    if ($this->model->create($data)) {
        $this->redirect('/layanan', 'flash_success', 'Layanan baru berhasil ditambahkan.');
    } else {
        $this->redirect('/layanan/create', 'flash_error', 'Gagal menyimpan layanan. Coba lagi.');
    }
}

private function sanitizeInput(array $post): array
{
    return [
        'nama_layanan'    => trim($post['nama_layanan'] ?? ''),
        'kategori'        => trim($post['kategori'] ?? ''),
        'harga'           => trim($post['harga'] ?? ''),
        'satuan_harga'    => trim($post['satuan_harga'] ?? 'kg'),
        'estimasi_durasi' => trim($post['estimasi_durasi'] ?? ''),
        'deskripsi'       => trim($post['deskripsi'] ?? ''),
    ];
}

private function validate(array $data): array
{
    $errors = [];

    if (empty($data['nama_layanan'])) {
        $errors['nama_layanan'] = 'Nama layanan wajib diisi.';
    } elseif (strlen($data['nama_layanan']) > 100) {
        $errors['nama_layanan'] = 'Nama layanan maksimal 100 karakter.';
    }

    if (!in_array($data['kategori'], ['express', 'reguler'])) {
        $errors['kategori'] = 'Kategori harus Express atau Reguler.';
    }

    if (!is_numeric($data['harga']) || (int)$data['harga'] <= 0) {
        $errors['harga'] = 'Harga harus berupa angka positif.';
    }

    if (!in_array($data['satuan_harga'], ['kg', 'item', 'paket'])) {
        $errors['satuan_harga'] = 'Satuan harga tidak valid.';
    }

    if (empty($data['estimasi_durasi'])) {
        $errors['estimasi_durasi'] = 'Estimasi durasi wajib diisi.';
    }

    return $errors;
}
```

### Step 8.2 — Create Add Form View

Create file: `app/views/layanan/create.php`

```php
<div class="page-header">
    <div class="flex items-center gap-3 mb-2">
        <a href="/laundry-in/layanan" class="btn btn-ghost btn-sm">
            <i class="ph-bold ph-arrow-left"></i>
            Kembali
        </a>
    </div>
    <h1 class="page-title">Tambah Layanan Baru</h1>
    <p class="page-subtitle">Isi detail layanan yang ingin ditambahkan ke sistem.</p>
</div>

<div class="card" style="max-width: 720px;">
    <div class="card-body">
        <form method="POST" action="/laundry-in/layanan/store" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

            <!-- Nama Layanan -->
            <div class="form-group">
                <label class="form-label" for="nama_layanan">
                    Nama Layanan <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="nama_layanan"
                    name="nama_layanan"
                    class="form-control <?= isset($errors['nama_layanan']) ? 'form-control-error' : '' ?>"
                    placeholder="Contoh: Cuci Express, Setrika Saja"
                    value="<?= htmlspecialchars($old['nama_layanan'] ?? '') ?>"
                    maxlength="100"
                >
                <?php if (isset($errors['nama_layanan'])): ?>
                    <p class="form-error-msg">
                        <i class="ph-bold ph-warning-circle"></i>
                        <?= htmlspecialchars($errors['nama_layanan']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Kategori + Satuan (2 columns) -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="kategori">
                        Kategori <span class="required">*</span>
                    </label>
                    <select
                        id="kategori"
                        name="kategori"
                        class="form-control <?= isset($errors['kategori']) ? 'form-control-error' : '' ?>"
                    >
                        <option value="" disabled <?= empty($old['kategori']) ? 'selected' : '' ?>>Pilih kategori...</option>
                        <option value="express" <?= ($old['kategori'] ?? '') === 'express' ? 'selected' : '' ?>>Express</option>
                        <option value="reguler" <?= ($old['kategori'] ?? '') === 'reguler' ? 'selected' : '' ?>>Reguler</option>
                    </select>
                    <?php if (isset($errors['kategori'])): ?>
                        <p class="form-error-msg"><i class="ph-bold ph-warning-circle"></i> <?= htmlspecialchars($errors['kategori']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="satuan_harga">
                        Satuan Harga <span class="required">*</span>
                    </label>
                    <select id="satuan_harga" name="satuan_harga" class="form-control <?= isset($errors['satuan_harga']) ? 'form-control-error' : '' ?>">
                        <?php foreach (['kg' => 'Per Kilogram (kg)', 'item' => 'Per Item', 'paket' => 'Per Paket'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($old['satuan_harga'] ?? 'kg') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['satuan_harga'])): ?>
                        <p class="form-error-msg"><i class="ph-bold ph-warning-circle"></i> <?= htmlspecialchars($errors['satuan_harga']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Harga + Estimasi (2 columns) -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="harga">
                        Harga (Rp) <span class="required">*</span>
                    </label>
                    <input
                        type="number"
                        id="harga"
                        name="harga"
                        class="form-control <?= isset($errors['harga']) ? 'form-control-error' : '' ?>"
                        placeholder="Contoh: 8000"
                        value="<?= htmlspecialchars($old['harga'] ?? '') ?>"
                        min="1"
                    >
                    <?php if (isset($errors['harga'])): ?>
                        <p class="form-error-msg"><i class="ph-bold ph-warning-circle"></i> <?= htmlspecialchars($errors['harga']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estimasi_durasi">
                        Estimasi Durasi <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="estimasi_durasi"
                        name="estimasi_durasi"
                        class="form-control <?= isset($errors['estimasi_durasi']) ? 'form-control-error' : '' ?>"
                        placeholder="Contoh: 2-3 Jam, 1 Hari"
                        value="<?= htmlspecialchars($old['estimasi_durasi'] ?? '') ?>"
                        maxlength="50"
                    >
                    <?php if (isset($errors['estimasi_durasi'])): ?>
                        <p class="form-error-msg"><i class="ph-bold ph-warning-circle"></i> <?= htmlspecialchars($errors['estimasi_durasi']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label class="form-label" for="deskripsi">Deskripsi <span class="text-muted">(opsional)</span></label>
                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    class="form-control"
                    placeholder="Penjelasan singkat tentang layanan ini..."
                    rows="3"
                ><?= htmlspecialchars($old['deskripsi'] ?? '') ?></textarea>
                <p class="form-hint">Deskripsi membantu pelanggan memahami layanan ini.</p>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center gap-3" style="margin-top: var(--space-6); padding-top: var(--space-5); border-top: 1px solid var(--color-border);">
                <button type="submit" class="btn btn-primary">
                    <i class="ph-bold ph-floppy-disk"></i>
                    Simpan Layanan
                </button>
                <a href="/laundry-in/layanan" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
```

---

## Phase 9: Layanan CRUD — Edit & Update

### Step 9.1 — Add edit() and update() to LayananController

```php
/** GET /layanan/edit/{id} — Show edit form */
public function edit(int $id): void
{
    requireAuth();

    $layanan = $this->model->findById($id);
    if (!$layanan) {
        $this->redirect('/layanan', 'flash_error', 'Layanan tidak ditemukan.');
    }

    $this->render('layanan/edit.php', [
        'pageTitle'  => 'Edit Layanan',
        'activePage' => 'layanan',
        'layanan'    => $layanan,
        'errors'     => $_SESSION['form_errors'] ?? [],
        'old'        => $_SESSION['form_old'] ?? $layanan,
    ]);
    unset($_SESSION['form_errors'], $_SESSION['form_old']);
}

/** POST /layanan/update/{id} — Process edit form */
public function update(int $id): void
{
    requireAuth();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('/layanan');
    }

    $layanan = $this->model->findById($id);
    if (!$layanan) {
        $this->redirect('/layanan', 'flash_error', 'Layanan tidak ditemukan.');
    }

    $data   = $this->sanitizeInput($_POST);
    $errors = $this->validate($data);

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old']    = $data;
        $this->redirect("/layanan/edit/{$id}");
    }

    if ($this->model->update($id, $data)) {
        $this->redirect('/layanan', 'flash_success', 'Layanan berhasil diperbarui.');
    } else {
        $this->redirect("/layanan/edit/{$id}", 'flash_error', 'Tidak ada perubahan yang disimpan.');
    }
}
```

### Step 9.2 — Create Edit View

Create file: `app/views/layanan/edit.php`

This view is identical to `create.php` with two differences:

1. The `<form>` `action` points to `/laundry-in/layanan/update/<?= $layanan['id'] ?>`
2. All input `value` attributes default to `$old['field'] ?? $layanan['field']` (shows current DB value, or old input on validation failure)
3. The submit button reads "Perbarui Layanan" instead of "Simpan Layanan"

> **Copilot instruction:** Copy `app/views/layanan/create.php` exactly, then apply the 3 differences listed above.

---

## Phase 10: Layanan CRUD — Soft Delete, Archive & Restore

### Step 10.1 — Add delete(), archive(), restore() to LayananController

```php
/**
 * POST /layanan/delete/{id}
 * SOFT DELETE: Sets deleted_at = NOW(). Does NOT use SQL DELETE.
 */
public function delete(int $id): void
{
    requireAuth();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('/layanan');
    }

    $layanan = $this->model->findById($id);
    if (!$layanan) {
        $this->redirect('/layanan', 'flash_error', 'Layanan tidak ditemukan.');
    }

    if ($this->model->softDelete($id)) {
        $this->redirect('/layanan', 'flash_success',
            "Layanan \"{$layanan['nama_layanan']}\" berhasil dipindahkan ke arsip."
        );
    } else {
        $this->redirect('/layanan', 'flash_error', 'Gagal menghapus layanan. Coba lagi.');
    }
}

/** GET /layanan/archive — Show archived (soft-deleted) services */
public function archive(): void
{
    requireAuth();

    $archived = $this->model->archived();

    $this->render('layanan/archive.php', [
        'pageTitle'  => 'Arsip Layanan',
        'activePage' => 'arsip',
        'archived'   => $archived,
    ]);
}

/**
 * POST /layanan/restore/{id}
 * RESTORE: Sets deleted_at = NULL, making the record active again.
 */
public function restore(int $id): void
{
    requireAuth();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('/layanan/archive');
    }

    if ($this->model->restore($id)) {
        $this->redirect('/layanan', 'flash_success', 'Layanan berhasil dipulihkan.');
    } else {
        $this->redirect('/layanan/archive', 'flash_error', 'Gagal memulihkan layanan.');
    }
}
```

### Step 10.2 — Create Archive View

Create file: `app/views/layanan/archive.php`

```php
<div class="page-header">
    <div class="flex items-center gap-3 mb-2">
        <a href="/laundry-in/layanan" class="btn btn-ghost btn-sm">
            <i class="ph-bold ph-arrow-left"></i>
            Kembali ke Layanan
        </a>
    </div>
    <h1 class="page-title">Arsip Layanan</h1>
    <p class="page-subtitle">Layanan yang telah dihapus. Dapat dipulihkan kapan saja.</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Layanan Diarsipkan</h2>
        <span class="badge badge-warning"><?= count($archived) ?> arsip</span>
    </div>
    <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
        <?php if (empty($archived)): ?>
            <div class="empty-state">
                <i class="ph-bold ph-archive"></i>
                <div class="empty-state-title">Arsip Kosong</div>
                <p class="empty-state-text">Tidak ada layanan yang diarsipkan saat ini.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Layanan</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Tanggal Dihapus</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archived as $i => $item): ?>
                    <tr>
                        <td class="text-muted text-sm"><?= $i + 1 ?></td>
                        <td class="font-medium text-secondary"><?= htmlspecialchars($item['nama_layanan']) ?></td>
                        <td>
                            <span class="badge badge-<?= $item['kategori'] ?>" style="opacity: 0.6;">
                                <?= ucfirst($item['kategori']) ?>
                            </span>
                        </td>
                        <td class="text-secondary">Rp <?= number_format($item['harga'], 0, ',', '.') ?> / <?= $item['satuan_harga'] ?></td>
                        <td class="text-secondary text-sm">
                            <?= date('d M Y, H:i', strtotime($item['deleted_at'])) ?>
                        </td>
                        <td style="text-align:center;">
                            <form method="POST" action="/laundry-in/layanan/restore/<?= $item['id'] ?>" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                    Pulihkan
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
```

---

## Phase 11: Front Controller & Router

### Step 11.1 — Create index.php (Front Controller)

Create file: `index.php`

```php
<?php
declare(strict_types=1);

// Start session
session_start();

// Load all helpers and base classes
require_once __DIR__ . '/app/helpers/auth.php';
require_once __DIR__ . '/app/models/BaseModel.php';

// Get URL from query string (set by .htaccess)
$url = trim($_GET['url'] ?? '', '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$parts = explode('/', $url);

$segment1 = $parts[0] ?? '';  // e.g. 'layanan', 'dashboard', 'login'
$segment2 = $parts[1] ?? '';  // e.g. 'create', 'edit', 'delete', 'archive', 'restore'
$segment3 = $parts[2] ?? '';  // e.g. '5' (ID)

// =============================================
//  ROUTER
// =============================================

// Auth routes
if ($segment1 === '' || $segment1 === 'login') {
    require_once __DIR__ . '/app/controllers/AuthController.php';
    $controller = new AuthController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->processLogin();
    } else {
        $controller->showLogin();
    }
    exit;
}

if ($segment1 === 'logout') {
    require_once __DIR__ . '/app/controllers/AuthController.php';
    (new AuthController())->logout();
    exit;
}

// Dashboard route
if ($segment1 === 'dashboard') {
    require_once __DIR__ . '/app/controllers/DashboardController.php';
    (new DashboardController())->index();
    exit;
}

// Layanan routes
if ($segment1 === 'layanan') {
    require_once __DIR__ . '/app/controllers/LayananController.php';
    $c = new LayananController();

    $id = !empty($segment3) && is_numeric($segment3) ? (int)$segment3 : null;

    switch ($segment2) {
        case '':
            $c->index();
            break;
        case 'create':
            $c->create();
            break;
        case 'store':
            $c->store();
            break;
        case 'edit':
            $id ? $c->edit($id) : header('Location: /laundry-in/layanan');
            break;
        case 'update':
            $id ? $c->update($id) : header('Location: /laundry-in/layanan');
            break;
        case 'delete':
            $id ? $c->delete($id) : header('Location: /laundry-in/layanan');
            break;
        case 'archive':
            $c->archive();
            break;
        case 'restore':
            $id ? $c->restore($id) : header('Location: /laundry-in/layanan/archive');
            break;
        default:
            http_response_code(404);
            echo '<h1>404 — Halaman tidak ditemukan</h1>';
    }
    exit;
}

// 404 Fallback
http_response_code(404);
echo '<!DOCTYPE html><html><body style="font-family:sans-serif;text-align:center;padding:4rem;">
<h1 style="color:#EF4444;">404</h1><p>Halaman tidak ditemukan.</p>
<a href="/laundry-in/dashboard">Kembali ke Dashboard</a></body></html>';
```

---

## Phase 12: GitHub Upload & Submission

### Step 12.1 — Final File Checklist

Before pushing to GitHub, verify every file exists:

```
laundry-in/
├── index.php                             ✓
├── .htaccess                             ✓
├── .env.example                          ✓  (.env is gitignored)
├── .gitignore                            ✓
├── README.md                             (create in Step 12.3)
│
├── app/
│   ├── config/Database.php               ✓
│   ├── controllers/
│   │   ├── AuthController.php            ✓
│   │   ├── DashboardController.php       ✓
│   │   └── LayananController.php         ✓
│   ├── helpers/auth.php                  ✓
│   ├── models/
│   │   ├── BaseModel.php                 ✓
│   │   ├── AdminModel.php                ✓
│   │   └── LayananModel.php              ✓
│   └── views/
│       ├── layouts/
│       │   ├── main.php                  ✓
│       │   └── auth.php                  ✓
│       ├── auth/login.php                ✓
│       ├── dashboard/index.php           ✓
│       └── layanan/
│           ├── index.php                 ✓
│           ├── create.php                ✓
│           ├── edit.php                  ✓
│           └── archive.php               ✓
│
├── public/assets/
│   ├── css/
│   │   ├── variables.css                 ✓
│   │   ├── reset.css                     ✓
│   │   ├── layout.css                    ✓
│   │   ├── components.css                ✓
│   │   └── utilities.css                 ✓
│   └── js/
│       ├── theme.js                      ✓
│       ├── sidebar.js                    ✓
│       └── modal.js                      ✓
│
└── docs/
    ├── PRD.md                            ✓
    └── Planning.md                       ✓
```

### Step 12.2 — Create SQL Dump File

Export the `kampusin_db` structure (without data, or with seed data) for easy setup by evaluators:

```bash
# In terminal (XAMPP on Windows: use full path to mysqldump.exe)
mysqldump -u root -p kampusin_db --no-data > docs/kampusin_db_structure.sql

# Then separately export seed data:
mysqldump -u root -p kampusin_db admins jenis_layanan > docs/kampusin_db_seed.sql
```

### Step 12.3 — Create README.md

Create file: `README.md`

```markdown
# Laundry-IN — Sistem Manajemen Layanan Laundry

Web application untuk mengelola jenis layanan pada bisnis laundry.

## Tech Stack

- PHP 8.1+ (Native MVC)
- MariaDB (kampusin_db)
- PDO with Prepared Statements
- Vanilla CSS + JS (no framework)
- Phosphor Icons via CDN

## Setup

1. Clone repository ke direktori web server (`htdocs/laundry-in/`)
2. Copy `.env.example` → `.env`, isi kredensial database
3. Import `docs/kampusin_db_structure.sql` ke MariaDB
4. Jalankan `docs/kampusin_db_seed.sql` untuk data awal
5. Buka `http://localhost/laundry-in/`

## Login

- Username: `admin`
- Password: `admin123`

## Fitur

- Dashboard dengan summary card dan akses cepat
- CRUD lengkap untuk Jenis Layanan
- Soft Delete dengan fitur arsip & pemulihan
- Dark Mode / Light Mode toggle
- Fully Responsive (Mobile, Tablet, Desktop)
- Phosphor Icons (tanpa emoji)
- Font: Inter + Poppins
```

### Step 12.4 — Initialize Git & Push

```bash
cd laundry-in

# Initialize git
git init

# Add all files
git add .

# First commit
git commit -m "feat: initial commit — Laundry-IN CRUD app with soft delete, dark mode, responsive layout"

# Create repository on GitHub (via github.com UI), then:
git remote add origin https://github.com/YOUR_USERNAME/laundry-in.git
git branch -M main
git push -u origin main
```

### Step 12.5 — Final Testing Checklist

Run through every item before submission:

**Authentication**

- [ ] Login with `admin / admin123` works
- [ ] Wrong credentials show generic error
- [ ] Accessing `/dashboard` without login redirects to `/login`
- [ ] Logout clears session and redirects

**Dashboard**

- [ ] Summary cards show correct counts
- [ ] Recent services list populated
- [ ] Quick action shortcuts navigate correctly

**CRUD — Layanan**

- [ ] List page shows all active services (none with `deleted_at` set)
- [ ] Add form validates required fields (submit empty form → errors appear)
- [ ] New service appears in list after successful add
- [ ] Edit form pre-fills with existing data
- [ ] Update reflects in list immediately
- [ ] Delete button opens modal with correct service name
- [ ] After confirming delete: service disappears from list, `deleted_at` is set in DB (verify in phpMyAdmin)

**Archive & Restore**

- [ ] Deleted service appears in `/layanan/archive`
- [ ] Restore button moves service back to active list
- [ ] `deleted_at` is NULL again after restore (verify in DB)

**UI/UX**

- [ ] Dark mode toggle works; preference persists on refresh
- [ ] No emojis visible anywhere
- [ ] Font is Inter/Poppins (inspect in DevTools — Elements > Computed > font-family)
- [ ] Times New Roman does NOT appear anywhere
- [ ] Mobile layout: sidebar slides in/out from hamburger button
- [ ] Tablet layout: content fills available width
- [ ] Hover micro-interactions work on buttons, cards, nav items

**Security**

- [ ] Check a form field for XSS: submit `<script>alert(1)</script>` as `nama_layanan` — must render as plain text, not execute
- [ ] Verify CSRF token in all POST forms (View Source → find `<input type="hidden" name="csrf_token">`)
- [ ] Confirm SQL uses prepared statements (review LayananModel.php — no string concatenation in queries)
