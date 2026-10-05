<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {


        DB::statement("ALTER TABLE exchange_posts MODIFY COLUMN status ENUM('pending', 'open', 'closed', 'hidden') NOT NULL DEFAULT 'pending'");


        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exchange_post_id')->constrained()->onDelete('cascade');
            $table->timestamps();


            $table->unique(['user_id', 'exchange_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_likes');

        DB::statement("UPDATE exchange_posts SET status = 'open' WHERE status = 'pending'");
        DB::statement("ALTER TABLE exchange_posts MODIFY COLUMN status ENUM('open', 'closed', 'hidden') NOT NULL DEFAULT 'open'");
    }
};
