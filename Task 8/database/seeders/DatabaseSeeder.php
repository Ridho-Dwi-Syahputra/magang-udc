<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@inventaris.test'],
            ['name' => 'Admin Gudang', 'password' => 'password123'],
        );

        $kategori = [
            'Sembako' => 'Kebutuhan pokok sehari-hari.',
            'Minuman' => 'Minuman kemasan dan bahan minuman.',
            'Kebersihan' => 'Sabun, deterjen, dan alat kebersihan.',
            'Snack' => 'Makanan ringan.',
        ];

        foreach ($kategori as $nama => $keterangan) {
            Category::firstOrCreate(['name' => $nama], ['description' => $keterangan]);
        }

        $barang = [
            ['Sembako', 'Beras Premium 5kg', 40, 75000],
            ['Sembako', 'Minyak Goreng 2L', 25, 36000],
            ['Sembako', 'Gula Pasir 1kg', 60, 18500],
            ['Sembako', 'Tepung Terigu 1kg', 8, 13000],
            ['Sembako', 'Telur Ayam 1kg', 30, 29000],
            ['Minuman', 'Air Mineral 600ml', 120, 3500],
            ['Minuman', 'Teh Kotak 250ml', 48, 5000],
            ['Minuman', 'Kopi Sachet 1 Renceng', 35, 12000],
            ['Minuman', 'Susu UHT 1L', 6, 19500],
            ['Kebersihan', 'Sabun Batang', 90, 8000],
            ['Kebersihan', 'Deterjen Bubuk 800g', 22, 24000],
            ['Kebersihan', 'Pembersih Lantai 800ml', 18, 17000],
            ['Kebersihan', 'Sikat Cuci Piring', 4, 9500],
            ['Snack', 'Keripik Singkong 100g', 55, 9000],
            ['Snack', 'Biskuit Kaleng', 12, 42000],
            ['Snack', 'Wafer Cokelat', 70, 6500],
            ['Snack', 'Kacang Kulit 200g', 3, 15000],
            ['Sembako', 'Mie Instan 1 Dus', 15, 115000],
            ['Sembako', 'Garam Halus 500g', 80, 4000],
            ['Minuman', 'Sirup Marjan 460ml', 20, 26000],
        ];

        foreach ($barang as [$namaKategori, $nama, $stok, $harga]) {
            $kat = Category::where('name', $namaKategori)->first();

            Item::firstOrCreate(['name' => $nama], [
                'category_id' => $kat->id,
                'name' => $nama,
                'stock' => $stok,
                'price' => $harga,
                'description' => null,
            ]);
        }
    }
}
