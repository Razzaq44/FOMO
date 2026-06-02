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
        Schema::table('stock_columns', function (Blueprint $table) {
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_stock_check CHECK (stock >= 0)');
            DB::statement('ALTER TABLE flash_sales ADD CONSTRAINT flash_sales_stock_check CHECK (flash_sale_stock >= 0)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_columns', function (Blueprint $table) {
            DB::statement('ALTER TABLE products DROP CONSTRAINT products_stock_check');
            DB::statement('ALTER TABLE flash_sales DROP CONSTRAINT flash_sales_stock_check');
        });
    }
};