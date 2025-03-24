<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::table('categories')->delete();
        // DB::table('categories')->truncate();

        DB::table('categories')->insert([
            ['name' => 'Sữa rửa mặt', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kem dưỡng da', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Serum', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Son môi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dầu dưỡng tóc', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
