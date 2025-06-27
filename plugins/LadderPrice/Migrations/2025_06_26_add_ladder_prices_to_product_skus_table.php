<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->json('ladder_prices')->nullable()->comment('Ladder Prices for SKU');
        });
    }

    public function down(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->dropColumn('ladder_prices');
        });
    }
};
