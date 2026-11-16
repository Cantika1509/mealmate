# MealMate

Aplikasi web pengelolaan dan pemesanan katering harian berbasis paket langganan untuk mahasiswa dan pekerja.

## Tech Stack
- **Backend:** PHP 8+ (Procedural MVC)
- **Database:** MySQL / MariaDB (PDO)
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.3 & Bootstrap Icons

## Struktur Direktori
```text
mealmate/
├── assets/          # File statis (CSS, JS, gambar, dan ikon SVG)
├── config/          # Konfigurasi koneksi database PDO
├── controllers/     # Logika pemrosesan request per halaman
├── helpers/         # Fungsi bantu (session/auth, flash message, view render, upload)
├── models/          # Fungsi query database (PDO Prepared Statements)
├── views/           # Template tampilan HTML (customer, admin, auth, layouts)
├── index.php        # Front controller & routing (?page=...)
├── schema.sql       # Skema tabel database
├── seed.sql         # Data awal (demo)
└── setup_database.php
```

## Cara Menjalankan Proyek

1. **Konfigurasi Database**
   Salin file contoh kredensial dan sesuaikan dengan user MySQL lokal Anda:
   ```bash
   cp config/credential.example.php config/credentials.php
   ```

2. **Setup Tabel & Data Demo**
   Jalankan skrip migrasi melalui terminal:
   ```bash
   # Membuat database & tabel saja
   php setup_database.php

   # Membuat database, tabel, sekaligus mengisi data demo
   php setup_database.php --demo

   # Reset ulang seluruh isi tabel lalu isi data demo
   php setup_database.php --reset --demo
   ```

3. **Jalankan Server Lokal**
   ```bash
   php -S localhost:8000
   ```
   Buka `http://localhost:8000` di browser.

## Akun Demo (`--demo`)

| Peran | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@mealmate.id` | `admin12345` |
| **Pelanggan** | `angga@mealmate.id` | `pelanggan123` |
| **Pelanggan** | `budi@mealmate.id` | `pelanggan123` |
| **Pelanggan** | `dewi@mealmate.id` | `pelanggan123` |
