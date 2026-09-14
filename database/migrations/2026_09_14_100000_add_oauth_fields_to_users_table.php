<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('auth_provider')->nullable()->after('phone');
            $table->string('auth_provider_id')->nullable()->after('auth_provider');
            $table->string('avatar')->nullable()->after('auth_provider_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['auth_provider', 'auth_provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['auth_provider', 'auth_provider_id']);
            $table->dropColumn(['auth_provider', 'auth_provider_id', 'avatar']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });
    }
};
