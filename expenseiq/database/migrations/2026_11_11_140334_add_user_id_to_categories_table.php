<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Categories the app ships with keep user_id = NULL (everyone sees them).
     * Categories a user creates get their user_id, so only that user sees them.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // "Food" may now exist once per user, so the name is no longer unique on its own.
            $table->dropUnique(['category_name']);

            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            // A user cannot have two categories with the same name.
            $table->unique(['user_id', 'category_name']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'category_name']);
            $table->dropConstrainedForeignId('user_id');

            // Fails if two users created the same name; remove those rows first.
            $table->unique('category_name');
        });
    }
};