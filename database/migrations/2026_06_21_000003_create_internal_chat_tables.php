<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['admin_id', 'staff_id']);
        });

        Schema::create('internal_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internal_conversation_id')->constrained('internal_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['internal_conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_messages');
        Schema::dropIfExists('internal_conversations');
    }
};
