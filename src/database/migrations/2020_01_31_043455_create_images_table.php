<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('confidence')->nullable();
            $table->unsignedbigInteger('forum_id')->nullable();
            $table->unsignedbigInteger('response_id')->nullable();
            $table->timestamps();

            $table->foreign('forum_id')
                ->references('id')
                ->on('forums')->onDelete('cascade');
            $table->foreign('response_id')
                ->references('id')
                ->on('responses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
