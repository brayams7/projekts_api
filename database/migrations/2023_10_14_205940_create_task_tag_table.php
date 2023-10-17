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
        Schema::create('task_tag', function (Blueprint $table) {
            $table->uuid('task_id');
            $table->uuid('tag_id');

            $table->foreign("task_id")
                ->references('id')
                ->on('tasks')
                ->onDelete("CASCADE")
                ->onUpdate("CASCADE");

            $table->foreign("tag_id")
                ->references('id')
                ->on('tags')
                ->onDelete("CASCADE")
                ->onUpdate("CASCADE");

            $table->unique(['task_id','tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('task_tag');
    }
};
