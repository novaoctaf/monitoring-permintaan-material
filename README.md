# PT Maruwa Indonesia Admin Panel

## Tentang Aplikasi
Aplikasi admin panel untuk PT Maruwa Indonesia.

## Persyaratan Sistem
- PHP >= 8.2
- Composer
- MySQL atau database lainnya yang didukung Laravel
- Node.js dan NPM (untuk pengembangan frontend)

## Petunjuk Instalasi

### 1. Clone Repositori
```bash
git clone https://github.com/vonsofh/ptmaruwa-admin.git
cd ptmaruwa-admin
```

### 2. Instalasi Dependensi
```bash
composer install
npm install
npm run dev
```

### 3. Konfigurasi Lingkungan
```bash
cp .env.example .env
php artisan key:generate
```

Lalu, edit file `.env` dan sesuaikan konfigurasi database:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ptmaruwa_admin
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi Database
Jalankan migrasi untuk membuat struktur database:
```bash
php artisan migrate
```

### 5. Menjalankan Seeder (Opsional)
Jika ingin mengisi database dengan data contoh:
```bash
php artisan db:seed
```

Untuk menjalankan seeder tertentu:
```bash
php artisan db:seed --class=NamaSeeder
```

### 6. Konfigurasi Storage (Opsional)
```bash
php artisan storage:link
```

### 7. Menjalankan Aplikasi
```bash
php artisan serve
```
Aplikasi akan berjalan di `http://localhost:8000`

## Troubleshooting

### Masalah pada Migrasi
Jika terjadi masalah saat migrasi, coba reset database:
```bash
php artisan migrate:fresh
```

### Masalah pada Cache
Jika aplikasi tidak berjalan dengan benar setelah perubahan, coba hapus cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```
