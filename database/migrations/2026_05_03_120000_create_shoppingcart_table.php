<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shoppingcart', function (Blueprint $table) {
            $table->string('identifier',100);
            $table->string('instance',100);
            $table->longText('content');
            $table->nullableTimestamps();

            $table->unique(['identifier', 'instance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoppingcart');
    }
};
