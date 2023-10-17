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
    public function up(): void
    {
        Schema::create('task_user', function (Blueprint $table) {
            $table->uuid('task_id');
            $table->uuid('user_id');

            $table->tinyInteger('is_watcher');


            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete("CASCADE")
                ->onUpdate("CASCADE");

            $table->foreign("task_id")
                ->references('id')
                ->on('tasks')
                ->onDelete("CASCADE")
                ->onUpdate("CASCADE");

            $table->unique(['task_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('task_user');
    }
};
