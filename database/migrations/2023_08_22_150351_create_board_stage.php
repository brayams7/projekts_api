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
            $table->foreignId("board_id")
              ->constrained("boards")
              ->onDelete("cascade")
              ->onUpdate("cascade");

            $table->foreignId("stage_id")
              ->constrained("stages")
              ->onDelete("cascade")
              ->onUpdate("cascade");

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
