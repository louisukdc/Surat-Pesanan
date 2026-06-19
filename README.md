# Sistem Askes RKZ - Arsitektur & Panduan Implementasi Penuh

Dokumen ini merangkum seluruh kerangka kerja (*framework*), arsitektur, dan rencana implementasi yang digunakan untuk membangun Sistem Askes RKZ dari awal. Dokumen ini sangat berguna sebagai panduan *Developer* (*Developer Handbook*).

![Arsitektur Sistem](./non-essential/prd_sp_pembelian.svg)

## 1. Stack Teknologi (Tech Stack)
*   **Backend:** PHP Native (Minimal PHP 5.6+).
*   **Database:** MySQL / MariaDB (Driver menggunakan `mysqli` secara eksklusif).
*   **Frontend:** HTML5, CSS3 (Vanilla dengan CSS Variables), JavaScript (Vanilla & jQuery untuk AJAX).
*   **UI/UX:** Desain khusus dengan efek *Glassmorphism*, *Micro-animations*, dan gaya responsif ala *Enterprise Dashboard*.
*   **Keamanan:** Password Hashing (MD5 - *Legacy Support*), SQL Injection Prevention (menggunakan `prepare()` dan `bind_param()`), Role-Based Access Control (RBAC).

## 2. Struktur Folder (Clean Architecture)
Aplikasi memisahkan antara antarmuka (UI), logika backend (API), dan komponen *reusable*:
```text
/
├── api/                  # Backend RESTful API endpoints (users.php, orders.php, supplier.php, kwitansi.php)
├── assets/               # File statis
│   └── css/style.css     # Sistem Desain Utama (CSS Variables, Utilities)
├── components/           # Potongan antarmuka (sidebar.php, topbar.php)
├── pages/                # Halaman UI Dashboard (home.php, list_pesanan.php, master_supplier.php, dll)
├── img/                  # Aset gambar (logo.svg)
├── auth.php              # Logika Login/Logout & pengecekan Session (checkAuth)
├── config.php            # Koneksi Database MySQLi ($conn)
├── dashboard.php         # Kerangka utama aplikasi (Memuat Sidebar, Topbar, dan Router Halaman)
├── detail_kwitansi.php   # Halaman khusus pratinjau dan cetak dokumen (Mode Print)
└── index.php             # Halaman Login
```

## 3. Fitur Utama & Fungsionalitas
### A. Autentikasi & RBAC (Role-Based Access Control)
*   Terdapat 2 Role: **Admin** dan **Umum**.
*   Frontend akan menyembunyikan menu manajemen berdasarkan Session Role.
*   Backend API (khususnya `api/users.php` dan endpoint hapus lainnya) memvalidasi ulang Session Role sebelum merespons dengan HTTP Status 403 (Forbidden) jika tidak memiliki akses.

### B. RESTful API murni dengan AJAX
*   Semua pengolahan data tidak memuat ulang (*reload*) halaman.
*   Standar Method HTTP: `GET` (Tarik Data), `POST` (Simpan Data Baru), `PUT/PATCH` (Ubah Data), `DELETE` (Hapus Data).
*   API merespons murni dengan format `application/json`.

### C. Server-Side Pagination & Filtering
*   Tabel tidak menarik ribuan data sekaligus (Mencegah *Browser Freeze*).
*   API menerima parameter `?page=1&limit=50&search=keyword&start_date=xxx&end_date=xxx`.
*   Sistem menghitung total data menggunakan `COUNT()` dan mengembalikan format:
    `{ "data": [...], "total": 10500, "page": 1, "total_pages": 210 }`

### D. Pencegahan Error Mode Ketat (Strict Mode DB)
*   Sistem menolak kolom *null* atau format tanggal `0000-00-00`. Sistem diprogram untuk menyuntikkan *default string* `''` atau tanggal *safe* `1970-01-01` di belakang layar sebelum masuk ke *Database*.

## 4. Desain Antarmuka (CSS Design System)
*   **Color Palette:** Menggunakan variabel CSS di `:root` (contoh: `--primary: #2e7d32`, `--bg-color: #f4f7f6`).
*   **Komponen UI:** 
    *   `.card` (Kotak konten putih dengan *shadow* lembut).
    *   `.btn-primary`, `.btn-outline` (Tombol modern dengan efek *hover*).
    *   `.form-control` (Kotak input modern).
    *   `.data-table` (Tabel bersih dengan *padding* lebar dan *border-bottom*).
*   **Modal Form:** Popup statis di atas halaman utama untuk proses *Tambah* dan *Edit* data tanpa berpindah halaman.
