<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

            $this->call(MonthSeeder::class);
            User::updateOrCreate(
                ['email' => 'adminT@lvtn.vn'],
                [
                    'name' => 'Admin tổng',
                    'mobile' => '0900000000',
                    'position' => 'Admin tổng',
                    'utype' => 'ADMM',
                    'password' => Hash::make('123456789'),
                ]
            );
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
