<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('user_id');
            $table->unsignedbigInteger('forum_id');
            $table->unsignedbigInteger('response_id')->nullable();
            $table->text('content');
            $table->string('image', 200)->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')->onUpdate('cascade');
            $table->foreign('forum_id')
                ->references('id')
                ->on('forums')->onUpdate('cascade')
                ->on('forums')->onDelete('cascade');
            $table->foreign('response_id')
                ->references('id')
                ->on('responses')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('responses');
    }
}
