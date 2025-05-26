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
        Schema::table('shopping_carts', function (Blueprint $table) {
            // Add indexes for frequently queried columns
            if (!Schema::hasIndex('shopping_carts', 'shopping_carts_user_id_index')) {
                $table->index('user_id');
            }
            
            if (!Schema::hasIndex('shopping_carts', 'shopping_carts_product_id_index')) {
                $table->index('product_id');
            }
            
            if (!Schema::hasIndex('shopping_carts', 'shopping_carts_user_id_product_id_index')) {
                $table->index(['user_id', 'product_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopping_carts', function (Blueprint $table) {
            $table->dropIndex('shopping_carts_user_id_index');
            $table->dropIndex('shopping_carts_product_id_index');
            $table->dropIndex('shopping_carts_user_id_product_id_index');
        });
    }
};
