<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // How the category was chosen: 'manual', 'history' or 'ai'
            $table->string('category_source', 10)->nullable()->after('category_id');

            // What the AI suggested (kept even if the user changed it), so you can measure accuracy later
            $table->foreignId('ai_suggested_category_id')
                ->nullable()
                ->after('category_source')
                ->constrained('categories')
                ->nullOnDelete();

            $table->float('ai_confidence')->nullable()->after('ai_suggested_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ai_suggested_category_id');
            $table->dropColumn(['category_source', 'ai_confidence']);
        });
    }
};