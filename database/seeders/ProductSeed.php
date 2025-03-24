<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::table('categories')->truncate();
        DB::table('products')->insert([
            ['name' => 'Kem dưỡng',  'image' => 'storage/products/electronics.jpg', 'price' => 12000, 'status' => true, 'discount' => 0, 'description' => 'Kem dưỡng cho làn da mịn màng.', 'created_at' => now(), 'updated_at' => now(), 'category_id' => 1, 'origin' => 'Việt Nam', 'quantity' => 100],
            ['name' => 'Son môi', 'image' => 'storage/products/son-moi.jpg', 'price' => 1009077, 'status' => true, 'discount' => 0, 'description' => 'Chiếc son môi mơ ước của hội nhà giàu.', 'created_at' => now(), 'updated_at' => now(), 'category_id' => 3, 'origin' => 'TQ', 'quantity' => 100],
            ['name' => 'Sữa rửa mặt', 'image' => 'storage/products/sua-rua-mat.jpg', 'price' => 10000, 'status' => false, 'discount' => 0, 'description' => 'Sản phẩm này không được bán trên thị trường 🤫.', 'created_at' => now(), 'updated_at' => now(), 'category_id' => 4, 'origin' => 'Mỹ', 'quantity' => 100],
            ['name' => 'Serum', 'image' => 'storage/products/serum.jpg', 'price' => 10000, 'status' => false, 'discount' => 0, 'description' => 'Sản phẩm serum dưỡng da.', 'created_at' => now(), 'updated_at' => now(), 'category_id' => 2, 'origin' => 'Taiwan', 'quantity' => 100],
            ['name' => 'Dầu dưỡng tóc', 'image' => 'storage/products/dau-duong-toc.jpg', 'price' => 10000900, 'status' => true, 'discount' => 0, 'description' => 'Dầu dưỡng tóc làm mềm tóc.', 'created_at' => now(), 'updated_at' => now(), 'category_id' => 3, 'origin' => 'Việt Nam', 'quantity' => 100],
        ]);
    }
}
