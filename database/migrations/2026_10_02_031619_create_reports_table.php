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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();


            $table->foreignId('reporter_id')->constrained('users')->onDelete('restrict');        
            $table->foreignId('reported_user_id')->constrained('users')->onDelete('restrict');   


            $table->foreignId('exchange_post_id')->nullable()->constrained()->onDelete('set null');

            $table->text('reason');                                                               
            $table->enum('status', ['pending', 'resolved', 'dismissed'])->default('pending');    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
