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
        Schema::create('user_workspace', function (Blueprint $table) {


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

            /*$table->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->foreignId("workspace_id")
                ->constrained("workspaces")
                ->onDelete("cascade")
                ->onUpdate("cascade");*/

            $table->unique(['user_id', 'workspace_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_workspace');
    }
};
