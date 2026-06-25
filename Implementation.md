# 🏥 Master Implementation Plan: Sistem E-Procurement (Lemari Arsip Pintar RKZ)

**Project Code:** RKZ-EPROC-2026  
**Document Type:** Technical & SDLC Implementation Guide  
**Status:** Active Draft  

---

## 1. 🏗️ Arsitektur & Teknologi (Tech Stack)
Mengingat kebutuhan sistem rumah sakit yang membutuhkan relasi data yang kuat, keamanan (RBAC), dan skalabilitas, berikut adalah arsitektur yang ditetapkan:
*   **Backend Framework:** PHP (Laravel) - Sangat andal untuk ORM (*Eloquent*) dan arsitektur *Multi-tier Approval*.
*   **Database:** MySQL / PostgreSQL - Relasional murni untuk menjamin *Audit Trail* keuangan.
*   **Frontend/UI:** Blade Templating / Vue.js / Alpine.js + Tailwind CSS (Fokus pada desain responsif untuk Dasbor Suster di *mobile*).
*   **Dokumen & Export:** PDF Generator (misal: `barryvdh/laravel-dompdf`).
*   **Otentikasi TTE:** Internal QR Code Generator berbasis verifikasi PIN/Password.

---

## 2. 🔄 SDLC Phase 1: Development Strategy (Agile Sprints)
Pengembangan akan dibagi menjadi 3 *Sprints* (masing-masing 1-2 minggu) untuk memastikan fungsionalitas dapat ditinjau secara berkala.

### Sprint 1: Fondasi & Modul Awal
*   [ ] Inisialisasi Repositori (Git) & Setup Environment lokal.
*   [ ] Eksekusi Skema Database (Migration) & Relasi Model (User, Requisition, Vendor).
*   [ ] Seeder & Factory (Membuat data *dummy* pengguna, vendor, dan departemen).
*   [ ] **Fitur:** Modul Autentikasi & RBAC (Role-Based Access Control).
*   [ ] **Fitur:** Modul Pengajuan (Input form Surat Permohonan oleh Administrasi).

### Sprint 2: Core Business Logic (Bidding & PO)
*   [ ] **Fitur:** Modul Bidding (Purchasing menginput penawaran vendor).
*   [ ] **Fitur:** Antarmuka komparasi harga.
*   [ ] **Fitur:** Modul Draf Purchase Order (PO).
*   [ ] **Fitur:** *Hierarchical Approval* (Notifikasi & TTE Kepala Bagian -> TTE Suster).
*   [ ] **Fitur:** Sistem pembuatan QR Code dan tempel Stempel Digital pada PDF PO.

### Sprint 3: Penyelesaian & Modul Keuangan
*   [ ] **Fitur:** Modul Penerimaan (Upload Surat Jalan Vendor).
*   [ ] **Fitur:** Generator Berita Acara & Laporan Kerja.
*   [ ] **Fitur:** Dashboard Keuangan (Validasi 3-arah: PO + Bukti Terima + Invoice Vendor).
*   [ ] UI/UX Polishing & Optimasi *Query* Database (Eager Loading untuk menghindari N+1 problem).

---

## 3. 🚀 SDLC Phase 2: CI/CD Pipeline (Continuous Integration / Continuous Deployment)
Untuk meminimalisir *downtime* dan *human error* saat perilisan fitur, proyek ini menggunakan pipeline CI/CD standar industri.

### Branching Strategy (Git Flow)
*   `main`: Kode *production-ready* (Stabil).
*   `staging`: Lingkungan pengujian untuk UAT klien (Replika produksi).
*   `develop`: Cabang integrasi utama dari *developer*.
*   `feature/*`: Cabang untuk pengerjaan modul spesifik (misal: `feature/po-approval`).

### CI Pipeline (Automated Testing - GitHub Actions / GitLab CI)
Berjalan otomatis ketika *Push/Pull Request* ke branch `staging` atau `develop`:
1.  **Code Sniffer:** Memeriksa standar penulisan kode (PSR-12).
2.  **Unit & Feature Testing:** Menjalankan PHPUnit. 
    *   *Test Case Wajib:* Memastikan Suster tidak bisa ACC jika Kabag belum ACC.
    *   *Test Case Wajib:* Kalkulasi diskon dan harga final di PO harus akurat.
3.  **Security Scan:** Cek dependensi yang rentan (misal: *composer audit*).

### CD Pipeline (Automated Deployment)
Berjalan otomatis ketika *Merge* ke branch `staging` atau `main`:
1.  Koneksi via SSH ke server rumah sakit.
2.  Eksekusi `git pull`.
3.  Eksekusi `composer install --optimize-autoloader --no-dev`.
4.  Eksekusi `php artisan migrate --force`.
5.  Eksekusi `php artisan config:cache` & `route:cache`.

---

## 4. 🛡️ SDLC Phase 3: Production & Security Guidelines
Rumah sakit memiliki protokol keamanan data yang ketat. Konfigurasi *Production* harus mematuhi standar berikut:

### Server & Aksesibilitas
*   **Deployment:** Di-*host* pada server lokal/intranet RKZ (On-Premise) atau Private Cloud. Akses dari luar rumah sakit wajib menggunakan VPN rumah sakit demi keamanan dokumen keuangan.
*   **SSL/TLS:** Seluruh *traffic* aplikasi harus menggunakan HTTPS.

### Keamanan Aplikasi
*   **Environment Variables:** File `.env` tidak boleh di-*commit* ke Git.
*   **Data Validation:** Seluruh *input* pengguna harus divalidasi dengan ketat di *backend* (mencegah SQL Injection & XSS).
*   **Rate Limiting:** Terapkan pembatasan *request* pada form *login* dan otentikasi PIN TTE untuk mencegah *brute-force*.

### Audit & Logs
*   **Audit Trail System:** Setiap aksi krusial (Buat PO, Edit Harga, TTE ACC Suster) harus disimpan di tabel `audit_logs` (User ID, IP Address, Timestamp, Action, Old Data, New Data).
*   **Error Logging:** Integrasi dengan *tools* seperti Sentry atau Telescope untuk memantau *error* atau aplikasi yang *crash* di *production*.

---

## 5. ♻️ SDLC Phase 4: Maintenance & Kelangsungan Sistem
Agar sistem bisa digunakan bertahun-tahun tanpa penurunan performa:
*   **Automated Backup:** *Cron job* harian (pada pukul 01:00 AM) untuk mem-*backup database* SQL ke *server* terpisah atau *storage* eksternal.
*   **Soft Deletes:** Dokumen tidak pernah benar-benar dihapus dari *database* (menggunakan fitur *SoftDeletes* Laravel) agar data historis audit keuangan tidak rusak.
*   **File Storage Management:** Dokumen Surat Jalan yang di-*upload* vendor harus dikompresi sebelum disimpan untuk menghemat kapasitas *disk server*.

---
**Document Sign-off:**  
*(Project Manager / Lead Developer)*