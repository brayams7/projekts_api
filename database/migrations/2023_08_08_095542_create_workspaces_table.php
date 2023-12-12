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
        Schema::create('workspaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name',128);
            $table->string('initials',3);
            $table->text("description");
            $table->string("color",12);
            $table->tinyInteger("status");

            $table->uuid('workspace_type_id');
            $table->uuid('user_id');

            $table->foreign("workspace_type_id")
                ->references('id')
                ->on('workspace_type')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->foreign("user_id")
                ->references('id')
                ->on('users')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            /*$table->foreignId('workspace_type_id')
                ->constrained('workspace_type')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
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
        Schema::dropIfExists('workspaces');
    }
};
