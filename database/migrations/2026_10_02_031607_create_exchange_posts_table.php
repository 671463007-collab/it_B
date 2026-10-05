<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exchange_posts', function (Blueprint $table) {
            $table->id();


            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');

            $table->string('title');                            
            $table->text('description');                        
            $table->unsignedTinyInteger('condition_percent');   
            $table->text('looking_for')->nullable();            
            $table->enum('status', ['open', 'closed', 'hidden'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_posts');
    }
};
