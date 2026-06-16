# PRD — Product Requirements Document

# Project: Laundry-IN | Laundry Service Management Web App

**Version:** 1.0.0
**Last Updated:** June 2026
**Author:** University Assignment — Full-Stack Web Development
**Role Context:** Senior Fullstack Developer · System Architect · UI/UX Designer

---

## Table of Contents

1. [Background & Problem Statement](#1-background--problem-statement)
2. [Project Objectives](#2-project-objectives)
3. [Target Audience](#3-target-audience)
4. [Scope & Boundaries](#4-scope--boundaries)
5. [Functional Requirements](#5-functional-requirements)
6. [Non-Functional Requirements](#6-non-functional-requirements)
7. [UI/UX Design Specification](#7-uiux-design-specification)
8. [Database Specification](#8-database-specification)
9. [Tech Stack & Architecture](#9-tech-stack--architecture)
10. [Page & Route Map](#10-page--route-map)
11. [Security Requirements](#11-security-requirements)
12. [Acceptance Criteria](#12-acceptance-criteria)
13. [Glossary](#13-glossary)

---

## 1. Background & Problem Statement

Small and medium laundry businesses in Indonesia commonly manage their service catalog — price lists, categories, and service types — through manual methods: printed paper sheets, handwritten notebooks, or basic spreadsheets. This introduces the following operational problems:

- **Inconsistency:** Service prices and names differ across staff members.
- **No audit trail:** Deleted services leave no record, making historical reporting impossible.
- **Slow updates:** Changing a price requires reprinting physical materials.
- **No central access:** Owners cannot review their service catalog remotely.

**Laundry-IN** is a web-based management system built to digitize and centralize the management of **Jenis Layanan Laundry** (Laundry Service Types). It provides a clean, professional admin dashboard where the business owner can create, read, update, and soft-delete service types — all from any device.

---

## 2. Project Objectives

| #    | Objective                                           | Success Metric                                          |
| ---- | --------------------------------------------------- | ------------------------------------------------------- |
| O-01 | Provide a secure, login-protected admin panel       | Only authenticated admin can access dashboard           |
| O-02 | Enable full CRUD operations on `jenis_layanan` data | All 4 operations work correctly against MariaDB         |
| O-03 | Implement Soft Delete to preserve historical data   | `deleted_at` is set on delete; records remain in DB     |
| O-04 | Deliver a professional, non-generic UI              | Design passes anti-AI-vibe checklist (see Section 7)    |
| O-05 | Ensure 100% responsive layout                       | Layout functions on 320px mobile through 1440px desktop |
| O-06 | Integrate a proper icon library (no emojis)         | All icons sourced from Phosphor Icons via CDN           |
| O-07 | Support Dark Mode / Light Mode toggle               | Theme persists via `localStorage` across page loads     |

---

## 3. Target Audience

**Primary User: The Laundry Business Admin (Owner)**

- Age range: 25–50 years old
- Device: Primarily mobile phone; occasionally desktop/laptop
- Technical literacy: Low to moderate — needs a clean, intuitive interface
- Use frequency: Daily, to check, add, or update service listings

**Secondary User: University Assignment Evaluator (Lecturer/TA)**

- Evaluates code quality, MVC architecture, database design, and UI quality
- Requires clean, commented, and well-structured code
- Reviews documentation for comprehensiveness

---

## 4. Scope & Boundaries

### In Scope

- Admin authentication (login/logout)
- CRUD for `jenis_layanan` table with soft delete
- Dashboard with summary statistics
- Responsive layout with sidebar navigation
- Dark/Light mode toggle
- Deleted records archive view ("Recycle Bin")
- Restore soft-deleted records

### Out of Scope (explicitly excluded from v1.0)

- Customer-facing front page
- Transaction/order management
- Reporting and analytics charts
- Payment gateway integration
- Multiple admin roles/permissions
- Staff management

---

## 5. Functional Requirements

### FR-01: Authentication Module

| ID      | Requirement                                                                           | Priority |
| ------- | ------------------------------------------------------------------------------------- | -------- |
| FR-01-A | System shall display a login form with `username` and `password` fields               | HIGH     |
| FR-01-B | System shall validate credentials against the `admins` table in `kampusin_db`         | HIGH     |
| FR-01-C | Passwords shall be verified using `password_verify()` (bcrypt)                        | HIGH     |
| FR-01-D | On failed login, display a non-specific error: "Username atau password salah."        | HIGH     |
| FR-01-E | On successful login, start a PHP session and redirect to `/dashboard`                 | HIGH     |
| FR-01-F | All non-public routes shall check for a valid session; redirect to `/login` if absent | HIGH     |
| FR-01-G | Logout shall destroy the session and redirect to `/login`                             | HIGH     |
| FR-01-H | Login page shall be the only publicly accessible page                                 | HIGH     |

### FR-02: Dashboard / Home Module

| ID      | Requirement                                                                                                                               | Priority |
| ------- | ----------------------------------------------------------------------------------------------------------------------------------------- | -------- |
| FR-02-A | Dashboard shall display Summary Cards: Total Active Services, Total Express Services, Total Reguler Services, Total Soft-Deleted Services | HIGH     |
| FR-02-B | Dashboard shall display a "Recent Services" list showing the last 5 active `jenis_layanan` records ordered by `created_at DESC`           | MEDIUM   |
| FR-02-C | Dashboard shall display Quick Action shortcuts: "Tambah Layanan", "Lihat Semua Layanan", "Lihat Arsip"                                    | MEDIUM   |
| FR-02-D | All counts shall query only records where `deleted_at IS NULL` (except the archived count)                                                | HIGH     |

### FR-03: Service List (Read) Module

| ID      | Requirement                                                                                         | Priority |
| ------- | --------------------------------------------------------------------------------------------------- | -------- |
| FR-03-A | Display all `jenis_layanan` records where `deleted_at IS NULL` in a data table                      | HIGH     |
| FR-03-B | Table columns: No., Nama Layanan, Kategori, Harga, Satuan, Estimasi Durasi, Deskripsi, Aksi         | HIGH     |
| FR-03-C | `harga` shall be formatted as Indonesian Rupiah: `Rp X.XXX`                                         | MEDIUM   |
| FR-03-D | `kategori` shall be displayed as a styled Badge: "Express" (teal/accent) vs "Reguler" (neutral)     | MEDIUM   |
| FR-03-E | Each row shall have "Edit" and "Hapus" action buttons with distinct icon-only or icon+label styling | HIGH     |
| FR-03-F | A search/filter bar shall allow filtering by `nama_layanan` (client-side or server-side)            | MEDIUM   |
| FR-03-G | A category filter dropdown (`Semua`, `Express`, `Reguler`) shall narrow results                     | LOW      |
| FR-03-H | The list page shall include a prominent "Tambah Layanan Baru" button                                | HIGH     |

### FR-04: Add Service (Create) Module

| ID      | Requirement                                                                                                                                                        | Priority |
| ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ | -------- |
| FR-04-A | Form shall contain fields for: `nama_layanan`, `kategori` (select), `harga` (number), `satuan_harga` (select), `estimasi_durasi`, `deskripsi` (textarea, optional) | HIGH     |
| FR-04-B | `kategori` select options: `express`, `reguler`                                                                                                                    | HIGH     |
| FR-04-C | `satuan_harga` select options: `kg`, `item`, `paket`; default to `kg`                                                                                              | HIGH     |
| FR-04-D | All fields except `deskripsi` are required; validate server-side                                                                                                   | HIGH     |
| FR-04-E | `harga` must be a positive integer; validate server-side                                                                                                           | HIGH     |
| FR-04-F | On successful insert, redirect to list page with a success flash message                                                                                           | HIGH     |
| FR-04-G | On validation failure, re-render form with old input values and inline error messages                                                                              | HIGH     |
| FR-04-H | `created_at` is set automatically by MariaDB default; `updated_at` and `deleted_at` are NULL on creation                                                           | HIGH     |

### FR-05: Edit Service (Update) Module

| ID      | Requirement                                                                       | Priority |
| ------- | --------------------------------------------------------------------------------- | -------- |
| FR-05-A | Edit form is pre-populated with existing record values fetched by `id`            | HIGH     |
| FR-05-B | Form fields are identical to the Add form                                         | HIGH     |
| FR-05-C | On submit, run `UPDATE jenis_layanan SET ... WHERE id = ? AND deleted_at IS NULL` | HIGH     |
| FR-05-D | `updated_at` is updated automatically by MariaDB's `ON UPDATE CURRENT_TIMESTAMP`  | HIGH     |
| FR-05-E | If record not found or is soft-deleted, return 404 / redirect with error message  | HIGH     |
| FR-05-F | On successful update, redirect to list page with a success flash message          | HIGH     |

### FR-06: Delete Service (Soft Delete) Module

| ID      | Requirement                                                                                                    | Priority |
| ------- | -------------------------------------------------------------------------------------------------------------- | -------- |
| FR-06-A | "Hapus" action shall NOT execute `DELETE FROM jenis_layanan`                                                   | CRITICAL |
| FR-06-B | Delete action shall execute: `UPDATE jenis_layanan SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL` | CRITICAL |
| FR-06-C | A confirmation modal/dialog must appear before executing the soft delete                                       | HIGH     |
| FR-06-D | After soft delete, the record disappears from the active list but remains in the database                      | CRITICAL |
| FR-06-E | The `deleted_at` timestamp must accurately record the time of deletion                                         | HIGH     |

### FR-07: Archive / Recycle Bin Module

| ID      | Requirement                                                                                                | Priority |
| ------- | ---------------------------------------------------------------------------------------------------------- | -------- |
| FR-07-A | A separate "Arsip" (Archive) page shall list all records where `deleted_at IS NOT NULL`                    | HIGH     |
| FR-07-B | Table columns: No., Nama Layanan, Kategori, Harga, Tanggal Dihapus, Aksi                                   | HIGH     |
| FR-07-C | Each archived row shall have a "Pulihkan" (Restore) button                                                 | HIGH     |
| FR-07-D | Restore action shall execute: `UPDATE jenis_layanan SET deleted_at = NULL WHERE id = ?`                    | HIGH     |
| FR-07-E | After restore, record reappears in the active list                                                         | HIGH     |
| FR-07-F | A "Hapus Permanen" (Permanent Delete) option may optionally be included with a double-confirmation warning | LOW      |

---

## 6. Non-Functional Requirements

### NFR-01: Performance

- Page load time under normal conditions (local/shared hosting): < 2 seconds
- Database queries must use prepared statements exclusively (no raw string interpolation)
- No N+1 query problems on list pages

### NFR-02: Security

- All user input is sanitized and validated server-side before database interaction
- Passwords stored exclusively as bcrypt hashes via `password_hash()`
- PHP sessions used for authentication state; session ID regenerated on login
- SQL Injection prevention: 100% PDO prepared statements with bound parameters
- XSS prevention: all output passed through `htmlspecialchars()`

### NFR-03: Code Quality

- PHP 8.x compatible syntax
- OOP-based Model classes for all database interactions
- MVC directory structure enforced (see Planning.md)
- No business logic in view files
- All SQL queries live in Model classes only

### NFR-04: Browser Compatibility

- Chrome 110+, Firefox 110+, Safari 16+, Edge 110+
- No Internet Explorer support required

### NFR-05: Accessibility

- All form inputs have associated `<label>` elements
- Color contrast ratio meets WCAG AA (4.5:1 minimum) in both light and dark modes
- Focus-visible states on all interactive elements
- Keyboard-navigable sidebar and forms

---

## 7. UI/UX Design Specification

### 7.1 Design Philosophy

The Laundry-IN UI must read as **intentionally designed, not generated**. Every visual decision must be justifiable. The evaluating standard: if the design could appear unchanged in a Bootstrap Starter Template, it has failed.

**Anti-AI-Vibe Checklist (must pass all):**

- [ ] No generic card-with-blue-header pattern
- [ ] No Times New Roman or system serif fonts anywhere in the UI
- [ ] No emojis used as UI elements or decorative elements
- [ ] No gradient "hero banners" on the dashboard
- [ ] Border-radius is consistent and deliberate (not default Bootstrap values)
- [ ] Sidebar is visually distinct from the content area
- [ ] Typography scale is defined and consistent

### 7.2 Color Palette

| Role                     | Light Mode | Dark Mode | Usage                                      |
| ------------------------ | ---------- | --------- | ------------------------------------------ |
| `--color-bg-base`        | `#F7F8FA`  | `#0F1117` | Page background                            |
| `--color-bg-surface`     | `#FFFFFF`  | `#1A1D27` | Cards, sidebar, modals                     |
| `--color-bg-elevated`    | `#ECEEF2`  | `#252836` | Table rows (alt), inputs                   |
| `--color-primary`        | `#0D9488`  | `#14B8A6` | Teal — primary actions, active states      |
| `--color-primary-soft`   | `#CCFBF1`  | `#134E4A` | Primary badge background, hover overlays   |
| `--color-accent`         | `#F59E0B`  | `#FBBF24` | Amber — highlights, warnings, badges       |
| `--color-text-primary`   | `#111827`  | `#F1F5F9` | Headings, primary content                  |
| `--color-text-secondary` | `#6B7280`  | `#94A3B8` | Captions, labels, secondary text           |
| `--color-text-inverse`   | `#FFFFFF`  | `#111827` | Text on filled buttons                     |
| `--color-border`         | `#E5E7EB`  | `#2D3247` | Card borders, dividers, table rows         |
| `--color-danger`         | `#EF4444`  | `#F87171` | Delete actions, error states               |
| `--color-success`        | `#10B981`  | `#34D399` | Success flash messages                     |
| `--color-sidebar-bg`     | `#0D1117`  | `#0D1117` | Sidebar (intentionally dark in both modes) |
| `--color-sidebar-text`   | `#94A3B8`  | `#94A3B8` | Sidebar navigation text                    |
| `--color-sidebar-active` | `#0D9488`  | `#14B8A6` | Active sidebar item                        |

> **Design Decision:** The sidebar is kept dark in both modes. This creates a clear structural separation between navigation and content, and prevents the "washed out" look that appears when a sidebar matches a light background.

### 7.3 Typography

**Primary Font: Inter** (via Google Fonts CDN)

- Source: `https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap`
- Applied to: `body`, all paragraphs, labels, form inputs, table content

**Display/Heading Supplement: Poppins** (via Google Fonts CDN)

- Source: `https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap`
- Applied to: Page titles (h1), Dashboard welcome text, brand name in sidebar

**ABSOLUTE PROHIBITION:** Times New Roman, Georgia, or any serif font is forbidden for any UI element. The CSS reset must include: `*, *::before, *::after { font-family: 'Inter', sans-serif; }`

**Type Scale:**

```
--text-xs:   0.75rem  (12px) — table captions, timestamps
--text-sm:   0.875rem (14px) — table data, form help text
--text-base: 1rem     (16px) — body text, form labels
--text-lg:   1.125rem (18px) — card titles, section headers
--text-xl:   1.25rem  (20px) — page subtitles
--text-2xl:  1.5rem   (24px) — page main headings
--text-3xl:  1.875rem (30px) — dashboard welcome, summary numbers
```

### 7.4 Icon Library: Phosphor Icons

**Library:** Phosphor Icons
**Integration Method:** CDN Script tag (no npm required for PHP environment)

```html
<!-- Place in <head> of layout/header.php -->
<script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>
```

**Usage Pattern (HTML):**

```html
<!-- General syntax: <i class="ph ph-{icon-name}"></i> -->
<!-- With weight modifier: ph-bold, ph-fill, ph-light, ph-thin, ph-duotone -->

<i class="ph-bold ph-house"></i>
<!-- Dashboard -->
<i class="ph-bold ph-list-bullets"></i>
<!-- Services list -->
<i class="ph-bold ph-plus-circle"></i>
<!-- Add new -->
<i class="ph-bold ph-pencil-simple"></i>
<!-- Edit -->
<i class="ph-bold ph-trash"></i>
<!-- Delete -->
<i class="ph-bold ph-arrow-counter-clockwise"></i>
<!-- Restore -->
<i class="ph-bold ph-archive"></i>
<!-- Archive / Recycle Bin -->
<i class="ph-bold ph-sign-out"></i>
<!-- Logout -->
<i class="ph-bold ph-sun"></i>
<!-- Light mode -->
<i class="ph-bold ph-moon"></i>
<!-- Dark mode -->
<i class="ph-bold ph-magnifying-glass"></i>
<!-- Search -->
<i class="ph-bold ph-funnel"></i>
<!-- Filter -->
<i class="ph-bold ph-check-circle"></i>
<!-- Success -->
<i class="ph-bold ph-warning-circle"></i>
<!-- Warning/Error -->
```

**Sizing:** Icons sized via font-size CSS property: `style="font-size: 1.25rem"` or utility class.

### 7.5 Spacing & Geometry

```
--radius-sm:  6px   — inputs, small badges
--radius-md:  10px  — cards, buttons
--radius-lg:  16px  — modals, sidebar profile area
--radius-xl:  24px  — summary cards, hero elements

--shadow-sm:  0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04)
--shadow-md:  0 4px 12px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04)
--shadow-lg:  0 10px 30px rgba(0,0,0,0.10), 0 4px 8px rgba(0,0,0,0.06)

--spacing-sidebar-width: 260px
--spacing-topbar-height: 64px
```

### 7.6 Layout Structure

**Desktop Layout (≥1024px):**

```
┌────────────────────────────────────────────────────┐
│  SIDEBAR (260px fixed)  │  MAIN CONTENT AREA       │
│  ─────────────────────  │  ──────────────────────  │
│  [Brand Logo + Name]    │  [Top Bar: breadcrumb +  │
│                         │   dark mode toggle]       │
│  Navigation:            │                           │
│  ○ Dashboard            │  [Page Title]             │
│  ○ Jenis Layanan        │                           │
│  ○ Arsip                │  [Summary Cards Row]      │
│                         │                           │
│  ─────────────────────  │  [Content Area]           │
│  [Admin Name]           │                           │
│  [Logout button]        │                           │
└────────────────────────────────────────────────────┘
```

**Mobile Layout (<768px):**

```
┌──────────────────────────────┐
│ [Hamburger] Laundry-IN [Dark]│  ← Top bar
├──────────────────────────────┤
│                              │
│  Page Content (full width)   │
│                              │
└──────────────────────────────┘
│  Sidebar slides in from left │
│  as overlay on hamburger tap │
```

**Tablet Layout (768px–1023px):**

```
┌──────────────────────────────────────┐
│ [Icon] Laundry-IN         [Dark Mode]│  ← Top bar (sidebar collapsed to icons)
├──────────────────────────────────────┤
│ [Sidebar: icon-only, 64px] │ Content │
└──────────────────────────────────────┘
```

### 7.7 Micro-Interactions & Transitions

All interactive elements must have smooth, purposeful transitions:

```css
/* Button hover — applied to ALL .btn elements */
transition:
  background-color 150ms ease,
  transform 100ms ease,
  box-shadow 150ms ease;

/* On hover: */
transform: translateY(-1px);
box-shadow: var(--shadow-md);

/* Table row hover */
transition: background-color 120ms ease;
background-color: var(--color-bg-elevated);

/* Sidebar nav item hover */
transition:
  background-color 150ms ease,
  color 150ms ease,
  padding-left 150ms ease;
padding-left: 1.25rem; /* slight indent on hover */

/* Dark mode toggle icon spin */
transition: transform 400ms cubic-bezier(0.34, 1.56, 0.64, 1);
transform: rotate(360deg);

/* Card on dashboard hover */
transition:
  transform 200ms ease,
  box-shadow 200ms ease;
transform: translateY(-3px);
box-shadow: var(--shadow-lg);

/* Flash message (alert) fade-in */
animation: fadeSlideDown 300ms ease forwards;

/* Modal overlay fade-in */
transition: opacity 200ms ease;
```

### 7.8 Dark Mode Implementation

Dark mode is toggled by adding/removing the class `dark` on the `<html>` element.

```javascript
// toggle-theme.js — included in layout footer
const toggle = document.getElementById("theme-toggle");
const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

// On load: check localStorage, fall back to system preference
if (
  localStorage.getItem("theme") === "dark" ||
  (!localStorage.getItem("theme") && prefersDark)
) {
  document.documentElement.classList.add("dark");
}

toggle.addEventListener("click", () => {
  const isDark = document.documentElement.classList.toggle("dark");
  localStorage.setItem("theme", isDark ? "dark" : "light");
  // Update icon visibility
});
```

CSS variables switch via `:root` and `.dark` selectors:

```css
:root {
  --color-bg-base: #f7f8fa; /* light defaults */
}
.dark {
  --color-bg-base: #0f1117; /* dark overrides */
}
```

### 7.9 Summary Cards Design

Each dashboard summary card must follow this structure — NOT a generic Bootstrap card:

```
┌─────────────────────────────────────┐
│  [Icon — 40px, teal bg circle]      │
│                                     │
│  24    ← large number (--text-3xl)  │
│  Total Layanan Aktif ← label        │
│                                     │
│  ── thin line ──                    │
│  +2 minggu ini  ← micro stat        │
└─────────────────────────────────────┘
```

Cards are arranged in a 4-column grid on desktop, 2-column on tablet, 1-column on mobile.

---

## 8. Database Specification

**Database:** `kampusin_db` (existing MariaDB instance — DO NOT create a new database)

### Table: `admins`

| Column       | Type         | Constraint                  | Notes                 |
| ------------ | ------------ | --------------------------- | --------------------- |
| `id`         | INT(11)      | PRIMARY KEY, AUTO_INCREMENT | —                     |
| `username`   | VARCHAR(50)  | UNIQUE, NOT NULL            | Used for login        |
| `password`   | VARCHAR(255) | NOT NULL                    | Stored as bcrypt hash |
| `created_at` | DATETIME     | DEFAULT CURRENT_TIMESTAMP   | —                     |

**Seed Data (run once via SQL or seeder script):**

```sql
INSERT INTO admins (username, password) VALUES (
    'admin',
    '$2y$12$...'  -- bcrypt hash generated via password_hash('admin123', PASSWORD_BCRYPT)
);
```

### Table: `jenis_layanan`

| Column            | Type                      | Constraint                            | Notes                                  |
| ----------------- | ------------------------- | ------------------------------------- | -------------------------------------- |
| `id`              | INT(11)                   | PRIMARY KEY, AUTO_INCREMENT           | —                                      |
| `nama_layanan`    | VARCHAR(100)              | NOT NULL                              | Service name                           |
| `kategori`        | ENUM('express','reguler') | NOT NULL                              | Service tier                           |
| `harga`           | INT(11)                   | NOT NULL                              | Price in IDR (integer)                 |
| `satuan_harga`    | ENUM('kg','item','paket') | NOT NULL, DEFAULT 'kg'                | Price unit                             |
| `estimasi_durasi` | VARCHAR(50)               | NOT NULL                              | e.g., "2-3 Jam", "1 Hari"              |
| `deskripsi`       | TEXT                      | NULLABLE                              | Optional description                   |
| `created_at`      | DATETIME                  | DEFAULT CURRENT_TIMESTAMP             | —                                      |
| `updated_at`      | DATETIME                  | ON UPDATE CURRENT_TIMESTAMP, NULLABLE | Set by MariaDB on UPDATE               |
| `deleted_at`      | DATETIME                  | NULLABLE, DEFAULT NULL                | NULL = active; not NULL = soft-deleted |

### Soft Delete Logic Explanation

The `deleted_at` field is the core of the soft delete mechanism:

| State        | `deleted_at` value    | Appears in active list | Appears in archive |
| ------------ | --------------------- | ---------------------- | ------------------ |
| Active       | `NULL`                | YES                    | NO                 |
| Soft Deleted | `2026-06-15 10:30:00` | NO                     | YES                |

**All queries on the active list MUST include:** `WHERE deleted_at IS NULL`
**Soft delete execution:** `UPDATE jenis_layanan SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL`
**Restore execution:** `UPDATE jenis_layanan SET deleted_at = NULL WHERE id = :id`

---

## 9. Tech Stack & Architecture

### 9.1 Technology Stack

| Layer               | Technology                                 | Version  | Rationale                                   |
| ------------------- | ------------------------------------------ | -------- | ------------------------------------------- |
| **Language**        | PHP                                        | 8.1+     | University requirement; native MVC patterns |
| **Database**        | MariaDB                                    | 10.6+    | Existing `kampusin_db` instance             |
| **DB Driver**       | PDO (PHP Data Objects)                     | Built-in | Prepared statements, DB-agnostic            |
| **CSS Framework**   | Custom CSS (CSS Variables + Flexbox/Grid)  | —        | Full design control, no Bootstrap lock-in   |
| **Icon Library**    | Phosphor Icons                             | 2.1.1    | CDN integration, clean SVG icons            |
| **Typography**      | Inter + Poppins                            | —        | Google Fonts CDN                            |
| **JS**              | Vanilla JavaScript                         | ES6+     | No framework dependency; lightweight        |
| **Web Server**      | Apache (XAMPP/WAMP) or built-in PHP server | —        | Local development                           |
| **Version Control** | Git + GitHub                               | —        | Assignment submission                       |

### 9.2 Architecture: Native PHP MVC

The project uses a **native PHP MVC pattern** — no framework required, giving full control and demonstrating understanding of core PHP architecture.

```
MVC Flow:
Browser Request → index.php (Front Controller) → Router → Controller → Model → View
                                                                    ↕
                                                              kampusin_db (MariaDB)
```

**Why not CodeIgniter 4?**
For a university CRUD assignment with a single table and simple auth, native PHP MVC is preferred because:

1. Zero framework setup/configuration overhead
2. Evaluators can see all code — no "magic" from a framework
3. Demonstrates deeper understanding of PHP fundamentals
4. Faster to implement for a constrained assignment scope

However, the architecture **mirrors CI4 conventions** so knowledge is directly transferable.

### 9.3 Directory Structure

```
laundry-in/
│
├── index.php                    # Front Controller — routes all requests
├── .htaccess                    # URL rewriting (remove index.php from URLs)
├── .env                         # Database credentials (gitignored)
├── .gitignore
├── README.md
│
├── app/
│   ├── config/
│   │   └── Database.php         # PDO connection singleton
│   │
│   ├── controllers/
│   │   ├── AuthController.php   # login, logout
│   │   ├── DashboardController.php
│   │   └── LayananController.php # index, create, store, edit, update, delete, archive, restore
│   │
│   ├── models/
│   │   ├── AdminModel.php       # findByUsername()
│   │   └── LayananModel.php     # all(), findById(), create(), update(), softDelete(), restore(), archived()
│   │
│   └── views/
│       ├── layouts/
│       │   ├── main.php         # Full dashboard layout (sidebar + topbar + content slot)
│       │   └── auth.php         # Minimal centered layout for login page
│       │
│       ├── auth/
│       │   └── login.php
│       │
│       ├── dashboard/
│       │   └── index.php
│       │
│       └── layanan/
│           ├── index.php        # List view
│           ├── create.php       # Add form
│           ├── edit.php         # Edit form
│           └── archive.php      # Archived records
│
├── public/
│   └── assets/
│       ├── css/
│       │   ├── variables.css    # All CSS custom properties
│       │   ├── reset.css        # Box model reset, font reset
│       │   ├── layout.css       # Sidebar, topbar, main grid
│       │   ├── components.css   # Cards, buttons, badges, tables, modals
│       │   └── utilities.css    # Spacing, text, display helpers
│       │
│       └── js/
│           ├── theme.js         # Dark/light mode toggle
│           ├── sidebar.js       # Mobile sidebar toggle
│           └── modal.js         # Delete confirmation modal
│
└── docs/
    ├── PRD.md
    └── Planning.md
```

---

## 10. Page & Route Map

| Route                   | Method | Controller          | Action               | Auth Required |
| ----------------------- | ------ | ------------------- | -------------------- | ------------- |
| `/`                     | GET    | AuthController      | Redirect to `/login` | No            |
| `/login`                | GET    | AuthController      | `showLogin()`        | No            |
| `/login`                | POST   | AuthController      | `processLogin()`     | No            |
| `/logout`               | GET    | AuthController      | `logout()`           | Yes           |
| `/dashboard`            | GET    | DashboardController | `index()`            | Yes           |
| `/layanan`              | GET    | LayananController   | `index()`            | Yes           |
| `/layanan/create`       | GET    | LayananController   | `create()`           | Yes           |
| `/layanan/store`        | POST   | LayananController   | `store()`            | Yes           |
| `/layanan/edit/{id}`    | GET    | LayananController   | `edit($id)`          | Yes           |
| `/layanan/update/{id}`  | POST   | LayananController   | `update($id)`        | Yes           |
| `/layanan/delete/{id}`  | POST   | LayananController   | `delete($id)`        | Yes           |
| `/layanan/archive`      | GET    | LayananController   | `archive()`          | Yes           |
| `/layanan/restore/{id}` | POST   | LayananController   | `restore($id)`       | Yes           |

> **Note:** Since browsers do not support `PUT`/`DELETE` methods natively in HTML forms, all mutations use `POST`. A hidden `_method` field may be used for semantic clarity but is not required.

---

## 11. Security Requirements

| Threat                 | Mitigation                                                                        |
| ---------------------- | --------------------------------------------------------------------------------- |
| SQL Injection          | 100% PDO prepared statements with named parameters                                |
| XSS (Reflected)        | All output via `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`                      |
| CSRF                   | POST forms include a session-based CSRF token                                     |
| Session Hijacking      | `session_regenerate_id(true)` on login                                            |
| Password Exposure      | Passwords stored only as bcrypt hash; never logged                                |
| Direct File Access     | `index.php` is the only public entry point; all `app/` files are above or blocked |
| Unauthenticated Access | Auth middleware check at the top of every protected controller method             |

---

## 12. Acceptance Criteria

The project is considered complete when all of the following are true:

- [ ] Admin can log in with seeded credentials and access the dashboard
- [ ] Dashboard displays correct counts for active, express, reguler, and archived services
- [ ] Admin can view all active services in a formatted table
- [ ] Admin can add a new service; record appears in the list
- [ ] Admin can edit an existing service; changes are reflected immediately
- [ ] Clicking "Hapus" triggers a confirmation modal before any action
- [ ] After confirming delete, the record has `deleted_at` set to a non-null timestamp and disappears from the active list
- [ ] The record appears in the `/layanan/archive` page after soft delete
- [ ] Admin can restore an archived record; it reappears in the active list
- [ ] No emoji characters appear anywhere in the UI
- [ ] All icons are rendered from Phosphor Icons
- [ ] Dark mode toggle works and preference persists after page refresh
- [ ] Layout is fully functional on mobile (320px), tablet (768px), and desktop (1280px)
- [ ] Font used is Inter/Poppins — Times New Roman does not appear anywhere
- [ ] All SQL queries use prepared statements
- [ ] Code is pushed to a public GitHub repository

---

## 13. Glossary

| Term              | Definition                                                                                   |
| ----------------- | -------------------------------------------------------------------------------------------- |
| **Jenis Layanan** | Service Type — a named laundry service with price and duration                               |
| **Soft Delete**   | Marking a record as deleted by setting `deleted_at` instead of removing it from the database |
| **Restore**       | Reversing a soft delete by setting `deleted_at = NULL`                                       |
| **Arsip**         | Archive — the collection of soft-deleted records                                             |
| **Express**       | Service category indicating priority/fast turnaround                                         |
| **Reguler**       | Service category indicating standard turnaround                                              |
| **Satuan Harga**  | Price unit — how the service price is charged (per kg, per item, or per package)             |
| **Flash Message** | A one-time session message shown after a redirect (success/error notification)               |
| **CSRF Token**    | Cross-Site Request Forgery token — a hidden form field to prevent malicious form submissions |
| **PDO**           | PHP Data Objects — PHP's database abstraction layer used for secure query execution          |
