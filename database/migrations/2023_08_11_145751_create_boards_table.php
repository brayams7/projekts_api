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
        Schema::create('boards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("name",128);
            $table->text("description")->nullable();
            $table->string("bg_color",12)->nullable();
            $table->tinyText("bg_img")->nullable();
            $table->tinyInteger("status");

            $table->uuid('user_id');
            $table->uuid('workspace_id');

            $table->foreign("user_id")
                ->references('id')
                ->on('users')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->foreign("workspace_id")
                ->references('id')
                ->on('workspaces')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            /*$table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->foreignId('workspace_id')
                ->constrained('workspaces')
                ->onDelete('cascade')
                ->onUpdate('cascade');*/

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
        Schema::dropIfExists('boards');
    }
};
