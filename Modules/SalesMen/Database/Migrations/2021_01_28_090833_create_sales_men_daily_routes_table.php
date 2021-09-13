<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesMenDailyRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_men_daily_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salesmen_id');
            $table->foreign('salesmen_id')->references('id')->on('salesmen');
            $table->enum('day',['1','2','3','4','5','6'])->comment="1=Sat,2=Sun,3=Mon,4=Tue,5=Wed,6=Thu";
            $table->unsignedBigInteger('route_id');
            $table->foreign('route_id')->references('id')->on('locations');
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
        Schema::dropIfExists('sales_men_daily_routes');
    }
}
