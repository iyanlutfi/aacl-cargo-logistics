# AACL Cargo & Logistics (TALL Stack Edition)

Sistem Operasional Pengiriman, Tracking 5 Tahap, Digital POD, dan Manajemen Armada B2B untuk **PT. AACL Cargo & Logistics**, dibangun menggunakan **TALL Stack**:
- **Tailwind CSS v4** (via Vite)
- **Alpine.js** (Kalkulator Dimensi P×L×T, Kalkulator Tarif, Canvas Tanda Tangan Digital, Print Helpers)
- **Laravel 13** (Eloquent ORM, RBAC Middleware, Native OOXML `.xlsx` & Multiline CSV Service)
- **Livewire 4** (Full-page Reactive Components & `wire:navigate`)
- **MySQL / MariaDB** (`aacl_cargo`)

## Fitur Utama
1. **Portal Cek Resi Publik (`/`)**: Pelacakan nomor CN 5 tahap + bukti POD (foto & tanda tangan) tanpa membocorkan harga kirim & status pembayaran.
2. **Spreadsheet Operasional (`/operasional`)**:
   - 5 Kartu KPI (Total CN, Total Muatan, Dalam Proses, Selesai & POD, Total Harga Kirim + Lunas/Piutang).
   - 6 Filter Stage Pipeline, Pencarian Debounce, Filter Rentang Tanggal, dan Filter Status Pembayaran.
   - Tabel 19 Kolom interaktif (klik sel Status 1–5, POD, atau Harga & Status Bayar untuk update instan).
   - Modal Input/Edit CN dengan **Kalkulator Dimensi (`P×L×T` cm → m³, m², berat volumetrik `/4000`)** dan **Kalkulator Tarif**.
   - **Export Excel (`.xlsx`)** siap cetak (border tabel rapi, merge header, format Rupiah, rumus `SUM`, Landscape Fit-to-1-Page) & **Import/Export CSV**.
   - **Cetak Tabel (A4 Landscape)** tanpa terpotong & **Cetak Surat Jalan / Resi CN (A4 Portrait)**.
3. **Panel Kurir Lapangan (`/kurir`)**: Pencarian cepat, filter Belum Selesai vs Selesai, update cepat Status 1–4, dan upload Foto POD + Tanda Tangan Digital penerima.
4. **Master Customer & Armada (`/master`)** & **Manajemen Karyawan (`/owner`)**.

## Instalasi & Menjalankan Lokal
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Pengujian Otomatis
```bash
php artisan test
```
