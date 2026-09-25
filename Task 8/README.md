# Inventaris API - Laravel + Sanctum

Task 8 magang Udacoding Batch 21. API sistem inventaris sederhana dengan autentikasi token. Semua endpoint data dikunci, cuma bisa diakses kalau bawa token yang valid.

Ini yang jadi backend buat Task 9.

## Stack

| Bagian | Dipakai |
|---|---|
| Framework | Laravel 12 |
| PHP | 8.2 |
| Database | MySQL / MariaDB |
| Auth | Laravel Sanctum 4, token based |

Catatan versi: brief nyebut Laravel 11, tapi semua rilis 11.x kena security advisory dan diblokir Composer. Dipakai Laravel 12 yang masih didukung penuh. Cara pakai Sanctum, Eloquent, dan routing-nya sama persis.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Bikin database dulu:

```sql
CREATE DATABASE inventaris_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sesuaikan `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventaris_api
DB_USERNAME=root
DB_PASSWORD=

FRONTEND_URL=http://localhost:5173
```

Terakhir:

```bash
php artisan migrate --seed
php artisan serve
```

Akun hasil seeder:

```
email    : admin@inventaris.test
password : password123
```

Seeder juga ngisi 4 kategori dan 20 barang.

## Struktur database

```
users              categories             items
-----              ----------             -----
id                 id                     id
name               name (unique)          category_id  ->  categories.id
email (unique)     slug (unique)          name
password           description            sku (unique)
                                          description
                                          stock
                                          price
```

Relasinya one-to-many. Satu kategori punya banyak barang, satu barang cuma punya satu kategori.

Foreign key-nya pakai `restrictOnDelete`, bukan `cascade`. Jadi kategori yang masih dipakai barang nggak bisa dihapus. Kalau `cascade`, hapus satu kategori berarti seluruh stok di dalamnya ikut hilang tanpa peringatan.

## Endpoint

Publik:

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/api/register` | Daftar user baru, langsung dapat token |
| POST | `/api/login` | Login, balikin token |

Butuh header `Authorization: Bearer <token>`:

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/api/me` | Data user pemilik token |
| POST | `/api/logout` | Cabut token yang lagi dipakai |
| GET | `/api/categories` | List kategori plus jumlah barangnya |
| GET | `/api/categories/{id}` | Detail kategori beserta daftar barang |
| POST | `/api/categories` | Tambah kategori |
| PUT | `/api/categories/{id}` | Update kategori |
| DELETE | `/api/categories/{id}` | Hapus kategori |
| GET | `/api/items` | List barang, ada paginasi |
| GET | `/api/items/{id}` | Detail barang |
| POST | `/api/items` | Tambah barang |
| PUT | `/api/items/{id}` | Update barang |
| DELETE | `/api/items/{id}` | Hapus barang |

`GET /api/items` nerima query opsional:

| Query | Contoh | Fungsi |
|---|---|---|
| `q` | `?q=beras` | Cari di nama atau SKU |
| `category_id` | `?category_id=1` | Filter per kategori |
| `low_stock` | `?low_stock=1` | Cuma barang dengan stok di bawah 10 |
| `per_page` | `?per_page=25` | Jumlah baris per halaman, default 15 |

## Testing pakai Postman

Koleksinya sudah disiapkan di [postman/Inventaris API.postman_collection.json](postman/Inventaris%20API.postman_collection.json). Import ke Postman, lalu:

1. Jalankan request **Login**. Tokennya otomatis kesimpen ke variable `{{token}}` lewat script di tab Tests, jadi nggak perlu copy paste manual.
2. Request lain tinggal dijalanin, header Authorization-nya sudah kepasang.

Kalau `base_url` beda, ubah di tab Variables koleksinya.

## Kode status yang dipakai

| Status | Kapan |
|---|---|
| 200 | Permintaan berhasil |
| 201 | Data baru berhasil dibuat |
| 401 | Belum login, token salah, atau token sudah dicabut |
| 404 | ID yang dicari nggak ada |
| 409 | Hapus kategori yang masih dipakai barang |
| 422 | Validasi gagal |

## Catatan implementasi

Password nggak di-`Hash::make` manual. Model `User` bawaan Laravel punya cast `'password' => 'hashed'`, jadi hashing-nya jalan otomatis waktu disimpan. Nge-hash manual di atas cast itu bikin password ke-hash dua kali dan login-nya nggak akan pernah cocok.

Pesan error login sengaja disamain buat "email nggak ada" dan "password salah". Kalau dibedain, orang bisa nebak email mana yang terdaftar cuma dari respon API.

`logout` cuma nyabut token yang lagi dipakai, bukan semua token user. Jadi kalau login di dua perangkat, logout di satu nggak nendang yang lain.

CORS dibatasi ke `http://localhost:5173` lewat [config/cors.php](config/cors.php), yang jadi alamat dev server React di Task 9. Bintang sengaja nggak dipakai.

---

Ridho Dwi Syahputra, Web Developer Intern Udacoding Batch 21
