<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_stage', function (Blueprint $table) {
            //$table->id();
            $table->uuid('board_id');
            $table->uuid('stage_id');

            $table->foreign("board_id")
                ->references('id')
                ->on('boards')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->foreign("stage_id")
                ->references('id')
                ->on('stages')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            /*$table->foreignId("board_id")
              ->constrained("boards")
              ->onDelete("cascade")
              ->onUpdate("cascade");

            $table->foreignId("stage_id")
              ->constrained("stages")
              ->onDelete("cascade")
              ->onUpdate("cascade");*/

            $table->integer("order");
            $table->unique(['board_id', 'stage_id']);
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('board_stage');
    }
};
