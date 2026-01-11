# Klinik Backend API

REST API buat sistem antrian klinik. Backend ini yang handle semua logic bisnis, autentikasi, sama manajemen data buat aplikasi mobile-nya.

## Tech Stack

### Core Framework
**Laravel 12** - Framework PHP modern buat bikin REST API. Dipilih karena udah mature, dokumentasi lengkap, sama ekosistemnya gede banget.

### Database
**Mysql** - Database ringan yang file-based. Cocok buat development sama deployment yang simple. Bisa gampang diganti ke Sqlite atau PostgreSQL kalo udah production.

### Authentication
**Laravel Sanctum** - Package bawaan Laravel buat token-based authentication. Lebih ringan dari Passport, cocok buat SPA sama mobile app.

### PDF Generation
**DomPDF (barryvdh/laravel-dompdf)** - Library buat generate PDF dari HTML. Dipake buat export laporan antrian.

### HTTP Client
**Guzzle** - HTTP client buat kirim request ke external API, terutama Firebase Cloud Messaging buat push notification.

### Development Tools
- **Laravel Pint** - Code formatter biar code style konsisten
- **Pest** - Testing framework yang lebih modern dari PHPUnit
- **Laravel Sail** - Docker environment buat development (optional)

## Gimana Cara Kerjanya

Backend dibuat pake Laravel 12 dengan arsitektur RESTful API. Database-nya pake SQLite biar gampang development sama deployment. Autentikasi pake Laravel Sanctum dengan token-based authentication.

## Fitur Utama

### Sistem Login
Handle registrasi pasien baru, login buat admin sama pasien, plus manajemen session pake token. Setiap request yang butuh autentikasi harus sertain Bearer token di header.

### Manajemen Antrian
Ini core feature-nya. Handle pengambilan nomor antrian, ngitung estimasi waktu tunggu, status antrian real-time, sama notifikasi waktu antrian dipanggil. Sistem bisa handle banyak poli dengan nomor antrian yang terpisah per hari.

### Dashboard Admin
Nyediain statistik real-time tentang jumlah antrian hari ini, yang lagi dilayani, yang nunggu, sama yang udah selesai. Data dikelompokkin per poli biar gampang monitoring.

### Kontrol Antrian
Admin bisa panggil antrian berikutnya, skip antrian yang ga dateng, tandain antrian selesai, atau batal antrian dengan alasan. Setiap aksi tercatat di audit log.

### Kelola Pasien
CRUD lengkap buat data pasien termasuk info kontak, history kunjungan, sama history antrian. Ada fitur search buat gampangin admin cari data pasien.

### Setting Operasional
Tiap poli punya setting sendiri buat jam buka, jam tutup, max antrian per hari, sama rata-rata waktu pelayanan. Setting ini yang dipake buat ngitung estimasi waktu tunggu.

### Laporan dan Export
Generate laporan antrian berdasarkan periode tertentu dengan statistik lengkap. Laporan bisa di-export ke PDF buat keperluan dokumentasi atau presentasi.

### Audit Log
Semua aktivitas admin tercatat otomatis termasuk siapa yang lakuin, kapan, IP address, sama perubahan data (old value sama new value). Berguna banget buat tracking sama compliance.

### Aksi Massal
Admin bisa lakuin aksi massal kayak batal banyak antrian sekaligus atau reset semua antrian buat tanggal tertentu. Setiap bulk action juga tercatat di audit log.

### API Display Antrian
Endpoint public tanpa autentikasi buat nampilin status antrian di layar TV atau monitor. Data auto-refresh dan nampilin nomor antrian yang lagi dipanggil per poli.

### Push Notification
Integrasi sama Firebase Cloud Messaging buat kirim notifikasi ke pasien waktu antrian mereka dipanggil atau ada update penting lainnya.

## Database Structure

Database pake relasi yang jelas antara users, patients, doctors, departments, queues, sama audit logs. Setiap queue terhubung sama patient, doctor, sama department. Queue notes nyimpen diagnosis sama resep dari dokter.

## Format Response API

Semua endpoint ngembaliin response dalam format JSON yang konsisten dengan struktur success boolean, message string, sama data object. Error handling pake HTTP status code standar.

## Keamanan

Pake middleware buat role-based access control. Admin routes dilindungi dengan middleware role:admin. Token disimpen dengan aman pake Laravel Sanctum. Semua input divalidasi sebelum diproses.

## Performa

Query optimization dengan eager loading buat hindarin N+1 problem. Pagination buat list data yang banyak. Index di kolom yang sering di-query buat mempercepat pencarian.

## Scalability

Arsitektur stateless yang memungkinkan horizontal scaling. Database bisa gampang diganti dari SQLite ke MySQL atau PostgreSQL buat production. Queue system bisa diintegrasiin sama Redis buat performa lebih baik.
