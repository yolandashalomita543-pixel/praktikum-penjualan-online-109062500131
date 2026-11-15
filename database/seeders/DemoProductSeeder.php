<?php

namespace Database\Seeders;

use App\Models\Category;use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoProductSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // [SKU, nama, harga, stok awal]
        $catalog = [
            'Elektronik' => [
                ['ELK-001', 'Headphone Bluetooth', 350000, 25],
                ['ELK-002', 'Speaker Mini Portabel', 275000, 18],
                ['ELK-003', 'Power Bank 10000 mAh', 185000, 30],
                ['ELK-004', 'Webcam Full HD', 420000, 12],
                ['ELK-005', 'Mouse Nirkabel', 120000, 40],
            ],
            'Aksesoris' => [
                ['AKS-001', 'Casing Ponsel Transparan', 45000, 60],
                ['AKS-002', 'Kabel USB-C 1 Meter', 35000, 80],
                ['AKS-003', 'Tempat Ponsel Meja', 55000, 35],
                ['AKS-004', 'Tas Laptop 14 Inci', 210000, 15],
                ['AKS-005', 'Stiker Laptop Set', 25000, 0],
            ],
            'Peralatan Rumah' => [
                ['RMH-001', 'Lampu Meja LED', 95000, 22],
                ['RMH-002', 'Botol Minum Stainless', 75000, 50],
                ['RMH-003', 'Kotak Penyimpanan Lipat', 85000, 28],
                ['RMH-004', 'Set Pisau Dapur', 160000, 10],
                ['RMH-005', 'Jam Dinding Minimalis', 130000, 8],
            ],
        ];

        foreach ($catalog as $categoryName => $products) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName]
            );

            foreach ($products as [$sku, $name, $price, $stock]) {
                $slug = Str::slug($name);

                // Aman dijalankan berulang: lewati produk yang sudah ada.
                if (Product::where('sku', $sku)->orWhere('slug', $slug)->exists()) {
                    continue;
                }

                DB::transaction(function () use ($category, $admin, $sku, $name, $slug, $price, $stock) {
                    $product = Product::create([
                        'category_id' => $category->id,
                        'sku' => $sku,
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $name.' adalah produk contoh untuk keperluan pengujian aplikasi.',
                        'price' => $price,
                        'stock' => $stock,
                        'is_active' => true,
                    ]);

                    if ($stock > 0) {
                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => $admin?->id,
                            'type' => 'IN',
                            'quantity' => $stock,
                            'unit_cost' => round($price * 0.7),
                            'reference' => 'SEED-'.$sku,
                            'note' => 'Stok awal data uji',
                            'movement_at' => now(),
                        ]);
                    }
                });
            }
        }
    }
}
