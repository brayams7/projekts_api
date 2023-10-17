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
        Schema::create('trakings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('description',512)->nullable();
            $table->bigInteger('created_at');
            $table->bigInteger('hours');
            $table->bigInteger('minutes');
            $table->bigInteger('full_minutes');
            $table->string('date', 45)->nullable();
            $table->smallInteger('day')->nullable();
            $table->smallInteger('month')->nullable();
            $table->smallInteger('year')->nullable();

            $table->uuid('task_id');
            $table->uuid('user_id');

            $table->foreign("task_id")
                ->references('id')
                ->on('tasks')
                ->onDelete("CASCADE")
                ->onUpdate("CASCADE");

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete("CASCADE")
                ->onUpdate("CASCADE");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('trakings');
    }
};
