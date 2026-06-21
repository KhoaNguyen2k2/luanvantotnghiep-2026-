<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('slides', 'placement')) {
            Schema::table('slides', function (Blueprint $table) {
                $table->string('placement', 20)->default('home')->after('id');
            });
        }

        DB::table('slides')
            ->where('link', 'like', '%/shop%')
            ->orWhere('link', 'like', '%shop%')
            ->update(['placement' => 'shop']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('slides', 'placement')) {
            Schema::table('slides', function (Blueprint $table) {
                $table->dropColumn('placement');
            });
        }
    }
};
