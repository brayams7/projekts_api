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

            $table->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->foreignId("workspace_id")
                ->constrained("workspaces")
                ->onDelete("cascade")
                ->onUpdate("cascade");
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
