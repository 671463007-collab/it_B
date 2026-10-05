<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('avatar');             
            $table->string('line_id', 50)->nullable()->after('phone');            
            $table->string('facebook_contact')->nullable()->after('line_id');     
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'line_id', 'facebook_contact']);
        });
    }
};
