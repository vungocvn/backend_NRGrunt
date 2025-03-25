<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => env('USER_Admin_ID'),
                'hash_code' => env('USER_Admin_UUID'),
                'name' => 'Hồ Trung',
                'email' => env('USER_Admin_EMAIL'),
                'avatar' => env('USER_Admin_AVATAR'),
                'password' => bcrypt(env('USER_Admin_PASSWORD')),
                'status' => 1,
                'email_verified_at' => now(),
                'is_enabled_2fa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
