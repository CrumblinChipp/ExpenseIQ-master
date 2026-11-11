<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) New table: one row per item on an expense (receipt line)
        Schema::create('expense_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expense_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('description');

            $table->decimal('amount', 10, 2);

            // How the category was chosen: 'manual', 'history' or 'ai'
            $table->string('category_source', 10)->nullable();

            // What the AI suggested (kept even if the user changed it)
            $table->foreignId('ai_suggested_category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->float('ai_confidence')->nullable();

            $table->timestamps();
        });

        // 2) Backfill: every existing expense becomes an expense with ONE item
        DB::statement('
            INSERT INTO expense_items
                (expense_id, category_id, description, amount,
                 category_source, ai_suggested_category_id, ai_confidence,
                 created_at, updated_at)
            SELECT
                id, category_id, description, amount,
                category_source, ai_suggested_category_id, ai_confidence,
                created_at, updated_at
            FROM expenses
            WHERE category_id IS NOT NULL
        ');

        // 3) An expense no longer has a single category (its items do), so allow NULL.
        //    The old category_id / AI columns on expenses are left in place for now;
        //    drop them in a later migration once every query reads expense_items.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE expenses MODIFY category_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('expenses', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Items are dropped. expenses.category_id is left nullable because
        // restoring NOT NULL would fail for expenses created after this migration.
        Schema::dropIfExists('expense_items');
    }
};