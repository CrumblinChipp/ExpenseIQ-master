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
        Schema::dropIfExists('budgets');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('monthly_budget'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Budget feature was removed on purpose; nothing to restore.
    }
};
