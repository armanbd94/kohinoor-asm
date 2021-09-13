<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index('name');
            $table->string('code')->index('code')->unique();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->enum('product_type',['1','2'])->comment="1=Can,2=Foil";
            $table->string('barcode_symbology')->nullable();
            $table->unsignedBigInteger('base_unit_id')->nullable();
            $table->foreign('base_unit_id')->references('id')->on('units');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id')->references('id')->on('units');
            $table->string('cost')->nullable()->comment('Base Unit wise cost');
            $table->string('base_unit_mrp')->nullable()->comment('Base Unit wise MRP');
            $table->string('base_unit_price')->nullable()->comment('Base Unit wise Price');
            $table->string('unit_mrp')->nullable()->comment('Base Unit wise MRP');
            $table->string('unit_price')->nullable()->comment('Base Unit wise Price');
            $table->double('base_unit_qty')->nullable();
            $table->double('unit_qty')->nullable();
            $table->double('alert_quantity')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->foreign('tax_id')->references('id')->on('taxes');
            $table->enum('tax_method',['1','2'])->comment = "1=Exclusive, 2=Inclusive";
            $table->enum('status',['1','2'])->default('1')->comment = "1=Active, 2=Inactive";
            $table->text('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
