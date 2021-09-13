<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_id');
            $table->foreign('production_id')->references('id')->on('productions');
            $table->string('chalan_no')->unique();
            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->string('item')->nullable();
            $table->string('total_unit_qty')->nullable();
            $table->string('total_base_unit_qty')->nullable();
            $table->double('total_tax')->nullable();
            $table->double('total')->nullable();
            $table->double('shipping_cost')->nullable();
            $table->double('labor_cost')->nullable();
            $table->double('grand_total')->nullable();
            $table->string('received_by')->nullable();
            $table->string('carried_by')->nullable();
            $table->date('transfer_date')->nullable();
            $table->text('remarks')->nullable();
            $table->string('created_by')->nullable();
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
        Schema::dropIfExists('transfers');
    }
}
