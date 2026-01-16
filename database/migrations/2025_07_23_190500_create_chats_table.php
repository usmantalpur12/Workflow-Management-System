<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->boolean('read')->default(false);
            $table->timestamps();
        });

        // Create index for faster message retrieval
        Schema::table('chats', function (Blueprint $table) {
            $table->index(['sender_id', 'recipient_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats');
    }
};
