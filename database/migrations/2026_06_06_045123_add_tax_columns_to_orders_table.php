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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tax_name')->nullable()->after('status');
            $table->enum('tax_type', ['percent', 'fixed'])->nullable()->after('tax_name');
            $table->decimal('tax_amount', 8, 2)->nullable()->after('tax_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tax_name', 'tax_type', 'tax_amount']);
        });
    }
};
