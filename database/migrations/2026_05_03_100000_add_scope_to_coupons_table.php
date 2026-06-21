<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('scope')->default('order')->after('cart_value');
            $table->foreignId('category_id')->nullable()->after('scope')->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['scope', 'category_id']);
        });
    }
};
