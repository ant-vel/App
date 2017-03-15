<?php

/**
 * Antvel - Data Base
 * Products Offers Table.
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->dateTime('day_start');
            $table->dateTime('day_end');
            $table->double('percentage', 10, 2);
            $table->double('price')->default(0);
            $table->integer('quantity')->default(0);
            $table->foreign('product_id')->references('id')->on('products');
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
        Schema::drop('product_offers');
    }
}
