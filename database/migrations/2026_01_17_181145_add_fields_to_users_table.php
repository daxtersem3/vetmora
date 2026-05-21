<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('nivel_id')->nullable()->constrained('niveles')->nullOnDelete();
            $table->foreignId('datos_personales_id')->nullable()->constrained('datos_personales')->nullOnDelete();
            // User 'active' status can be inferred or added if needed, but 'level' is enough for now for auth.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['nivel_id']);
            $table->dropForeign(['datos_personales_id']);
            $table->dropColumn(['nivel_id', 'datos_personales_id']);
        });
    }
};
