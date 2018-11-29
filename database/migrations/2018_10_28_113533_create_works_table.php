<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::create('works', function (Blueprint $table) {
            $table->increments('finalworkID'); //finalworkID
            $table->string('finalworkTitle');
            $table->string('finalworkDescription');
            $table->string('finalworkAuthor');
            $table->string('departement');
            $table->string('finalworkField');
            $table->integer('finalworkYear');
            $table->string('finalworkPromoter');

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
        Schema::dropIfExists('works');
    }
}
