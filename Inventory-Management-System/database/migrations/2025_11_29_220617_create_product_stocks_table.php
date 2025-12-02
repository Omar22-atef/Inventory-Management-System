<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductStocksTable extends Migration
{
    public function up()
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('quantity', 14, 4)->default(0);
            $table->decimal('reorder_threshold', 14, 4)->default(0);
            $table->decimal('reorder_qty', 14, 4)->default(0);
            $table->timestamps();

            $table->unique(['product_id','warehouse_id']);
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stocks');
    }
}
