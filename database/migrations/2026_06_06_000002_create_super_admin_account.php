<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'adminT@lvtn.vn'],
            [
                'name' => 'Admin tổng',
                'mobile' => '0900000000',
                'position' => 'Admin tổng',
                'utype' => 'ADMM',
                'password' => Hash::make('123456789'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'adminT@lvtn.vn')->where('utype', 'ADMM')->delete();
    }
};
