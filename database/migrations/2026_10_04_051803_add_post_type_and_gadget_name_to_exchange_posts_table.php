<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exchange_posts', function (Blueprint $table) {
            $table->enum('post_type', ['exchange', 'discussion'])->default('exchange')->after('id');
            $table->string('gadget_name')->nullable()->after('description');
            $table->unsignedTinyInteger('condition_percent')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exchange_posts', function (Blueprint $table) {
            $table->dropColumn(['post_type', 'gadget_name']);
        });
        DB::table('exchange_posts')->whereNull('condition_percent')->update(['condition_percent' => 100]);
        Schema::table('exchange_posts', function (Blueprint $table) {
            $table->unsignedTinyInteger('condition_percent')->nullable(false)->change();
        });
    }
};
