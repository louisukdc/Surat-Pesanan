# Dokumentasi Proyek: Hospital Procurement ERP (Sistem Askes)

Dokumen ini berisi rangkuman seluruh *workflow*, *implementation plan*, arsitektur, dan pedoman instruksi (AI Prompt) yang digunakan untuk mengembangkan aplikasi manajemen pengadaan rumah sakit ini. Dokumen ini dapat digunakan sebagai referensi untuk pengembangan lebih lanjut atau jika Anda ingin melanjutkan *coding* dengan AI di masa depan.

---

## 🤖 1. AI Prompt & Pedoman Pengembangan Utama (Guidelines)
Jika Anda menggunakan AI lain atau membuka sesi baru untuk melanjutkan proyek ini, berikan *prompt* berikut kepada AI tersebut:

> **[PROMPT AI DEVELOPMENT]**
> Anda bertindak sebagai Full-Stack Developer spesialis Sistem Informasi Rumah Sakit (SIMRS). Proyek ini adalah modul **Pengadaan (Procurement)** yang dibangun menggunakan spesifikasi berikut:
> 
> **Lingkungan Teknis:**
> 1. **PHP 5.6 (Legacy Mode):** Server menggunakan XAMPP lawas. **SANGAT PENTING:** Dilarang keras menggunakan sintaks PHP 7+ seperti Null Coalescing Operator (`??`), tipe data (*type hinting*), atau *arrow functions*. Selalu gunakan `isset()` atau `empty()` dipadu dengan *Ternary Operator* konvensional.
> 2. **Database:** MySQL/MariaDB konvensional menggunakan koneksi `mysqli`.
> 3. **UI/UX:** Dilarang menggunakan Bootstrap atau kerangka berat lainnya. Wajib menggunakan **Vanilla CSS (Material Detail UI)** yang sangat padat (*compact*) agar user tidak perlu banyak *scroll*. Gunakan AJAX murni (atau jQuery) untuk semua interaksi *form* agar halaman tidak *loading* ulang (SPA-like feel).
> 4. **Arsitektur:** Menggunakan sistem *flat-file routing* di mana file utama adalah `dashboard.php` dan file *view* diambil dari folder `pages/`. Semua proses *backend* ada di folder `api/`.
> 
> **Karakteristik UI:**
> - Panel (*card*) harus memiliki `box-shadow` halus, *border-top/bottom* tebal 15px.
> - Form input (`.form-control`) berukuran kecil (`height: 30px; font-size: 12px;`) dengan jarak (*gap*) antar kolom yang minimal.

---

## 🏗️ 2. Workflow & Fase Implementasi

Pengembangan sistem dibagi menjadi tiga fase utama untuk mereplikasi proses *supply chain* nyata di rumah sakit.

### FASE 1: Dashboard Analitik & Master Pesanan (PO)
- **Tujuan:** Membuat kerangka utama aplikasi, integrasi menu, dan laporan visual.
- **Workflow:**
  - Membuat *layout* `dashboard.php` dengan *sidebar* modern.
  - Halaman `pages/home.php` menampilkan ringkasan SP (Surat Pesanan).
  - Menyediakan filter berdasarkan bulan yang langsung merender ulang data dari *database*.
  - Modul pembuatan pesanan langsung (`pages/order_form.php`) yang memiliki validasi format nomor SP (contoh: `PO/001/01/26`).

### FASE 2: Permintaan Barang (PR) & Persetujuan (Approval)
- **Tujuan:** Mendokumentasikan permintaan dari berbagai unit (Gizi, Farmasi, Umum) ke pihak Pembelian (*Purchasing*).
- **Workflow:**
  - Unit membuat dokumen PR di menu **Permintaan Barang**. Nomor dokumen otomatis (misal: `PR/001/01/26`).
  - Atasan melakukan pengecekan di menu **Persetujuan SP**.
  - **Fitur Khusus:** Terdapat tombol *Detail* (*pop-up modal*) untuk melihat rincian barang sebelum disetujui.
  - Setelah disetujui, terdapat tombol **Buat SP**. Saat diklik, sistem *melempar* data tersebut ke `order_form.php`, mengubah prefix `PR` menjadi `PO`, dan mengisi detail barang ke tabel secara otomatis.
  - Tabel rincian barang pesanan diubah menjadi **Editable Table** (harga, diskon, qty berupa *input box*) agar pihak *purchasing* bisa langsung mengetik *deal* harga tanpa menghapus baris.

### FASE 3: Logistik Gudang (Goods Receipt & Retur)
- **Tujuan:** Pengecekan silang antara dokumen PO dengan fisik barang yang datang.
- **Workflow:**
  - **Penerimaan Barang (`pages/gr_form.php`):** Petugas gudang memanggil Nomor SP, lalu memasukkan Qty Terima faktual, Nomor Batch, dan Tanggal Kedaluwarsa (*Expired Date* - sangat vital untuk RS).
  - **Retur Pembelian (`pages/retur_form.php`):** Petugas memanggil Nomor SP, lalu memasukkan Qty Retur dan Alasan Retur jika barang cacat atau tidak sesuai spek.

---

## 🗄️ 3. Skema Database Utama

Berikut adalah tabel-tabel utama yang dirancang untuk mendukung ekosistem aplikasi ini:

1. **`sp_pesanan`** (Master Order / Surat Pesanan)
   Menyimpan header dan detail pesanan yang diajukan ke *supplier*.
   *(Kolom Penting: no_sp, tgl_sp, namasup, barang, qty, harga, potongan, total, unit, ppn, flag (grand total))*
   
2. **`sp_permintaan`** (Purchase Request)
   *(Kolom Penting: no_pr, tgl_pr, unit, keterangan, barang, qty, satuan, status, alasan_tolak)*

3. **`sp_penerimaan`** (Goods Receipt)
   *(Kolom Penting: no_gr, tgl_gr, no_sp, barang, qty_pesan, qty_terima, batch_no, exp_date)*

4. **`sp_retur`** (Return to Vendor)
   *(Kolom Penting: no_retur, tgl_retur, no_sp, barang, qty_retur, alasan)*

5. **`m_supplier`** (Master Supplier)
   *(Kolom Penting: KodeSupplier, NamaSupplier, dsb)*

---

## 🛠️ 4. Struktur Folder (Direktori)
- `index.php` : Login
- `auth.php` : Cek session login
- `config.php` : Koneksi Database MySQLi
- `setup_db.php` : Script auto-installer untuk mengeksekusi file `.sql` jika database kosong.
- `dashboard.php` : Rangka utama antarmuka pengguna (Routing system).
- `api/` : Berisi *script backend logic* (AJAX endpoints)
  - `pr.php` (Logic Permintaan & Persetujuan)
  - `orders.php` (Logic Master SP)
  - `gr.php` (Logic Penerimaan Barang)
  - `retur.php` (Logic Retur Barang)
- `pages/` : Berisi komponen UI (*view*)
  - `home.php`, `pr_form.php`, `approval.php`, `order_form.php`, `gr_form.php`, `retur_form.php`
- `Database/` : Folder tempat *backup schema* dan data SQL.

---

> **Dokumen ini dibuat otomatis pada: Juni 2026**
> Jaga file ini tetap berada di direktori *root* agar AI di masa mendatang dapat langsung membaca *blueprint* sistem ini.
