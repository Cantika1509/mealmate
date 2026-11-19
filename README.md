# MealMate

Aplikasi web pengelolaan dan pemesanan katering harian berbasis paket langganan untuk mahasiswa dan pekerja.

## Tech Stack
- **Backend:** PHP 8+ (Procedural MVC)
- **Database:** MySQL / MariaDB (PDO)
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.3 & Bootstrap Icons

## Struktur Direktori
```text
mealmate/
├── app/
│   ├── config/      # Konfigurasi koneksi database PDO
│   ├── controllers/ # Logika pemrosesan request per halaman
│   ├── helpers/     # Fungsi bantu (session/auth, flash, view render, upload)
│   ├── models/      # Fungsi query database (PDO Prepared Statements)
│   └── views/       # Template tampilan HTML (customer, admin, auth, layouts)
├── database/
│   ├── schema.sql   # Skema tabel database
│   ├── seed.sql     # Data awal (demo)
│   └── setup_database.php
└── public/          # DocumentRoot web server (hanya file di sini yang bisa diakses browser)
    ├── assets/      # File statis (CSS, JS, gambar, dan ikon SVG)
    └── index.php    # Front controller & routing (?page=...)
```

## Cara Menjalankan Proyek

1. **Konfigurasi Database**
   Salin file contoh kredensial dan sesuaikan dengan user MySQL lokal Anda:
   ```bash
   cp app/config/credential.example.php app/config/credentials.php
   ```

2. **Setup Tabel & Data Demo**
   Jalankan skrip migrasi melalui terminal:
   ```bash
   # Membuat database & tabel saja
   php database/setup_database.php

   # Membuat database, tabel, sekaligus mengisi data demo
   php database/setup_database.php --demo

   # Reset ulang seluruh isi tabel lalu isi data demo
   php database/setup_database.php --reset --demo
   ```

3. **Jalankan Server Lokal**
   Arahkan *document root* (`-t`) ke folder `public/`:
   ```bash
   php -S localhost:8000 -t public
   ```
   Buka `http://localhost:8000` di browser.

## Akun Demo (`--demo`)

| Peran | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@mealmate.id` | `admin12345` |
| **Pelanggan** | `angga@mealmate.id` | `pelanggan123` |
| **Pelanggan** | `budi@mealmate.id` | `pelanggan123` |
| **Pelanggan** | `dewi@mealmate.id` | `pelanggan123` |
