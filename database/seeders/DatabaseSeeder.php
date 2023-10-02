<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();



        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'company_name' => 'ABC',
            'company_email' => 'user@gmail.com',
            'phone' => '0881234567',
            'address' => 'Willis Rosebowl, 5 Dickinson Lower, South Australia',
        ]);

        DB::table('categories')->insert([
            [
                'name' => 'Default Category',
                'user_id' => User::query()->first()->id,
            ]
        ]);

        DB::table('customers')->insert([
            [
                'name' => 'Default Customer',
                'user_id' => User::query()->first()->id,
            ]
        ]);
        DB::table('vendors')->insert([
            [
                'name' => 'Default Vendor',
                'user_id' => User::query()->first()->id,
            ]
        ]);

    }
}
