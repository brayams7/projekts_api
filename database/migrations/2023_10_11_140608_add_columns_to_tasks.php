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
        Schema::table('tasks', function (Blueprint $table) {
            $table->uuid('task_id')->nullable()->default(null);
            $table->uuid('task_before')->nullable()->default(null);
            $table->uuid('task_after')->nullable()->default(null);

            $table->foreign("task_id")
                ->references('id')
                ->on('tasks');
//                ->onDelete("no action")
//                ->onUpdate("no action")

            $table->foreign("task_before")
                ->references('id')
                ->on('tasks');
//                ->onDelete("no action")
//                ->onUpdate("no action");

            $table->foreign("task_after")
                ->references('id')
                ->on('tasks');
//                ->onDelete("no action")
//                ->onUpdate("no action");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            //
        });
    }
};
