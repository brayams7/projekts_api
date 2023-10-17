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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("title", 256);
            $table->text("description")->nullable()->default(null);
            $table->bigInteger('created_at');
            $table->bigInteger('due_date')->nullable()->default(null);
            $table->bigInteger('calculated_time')->nullable()->default(null);
            $table->bigInteger('starts_at')->nullable()->default(null);

            $table->uuid('feature_id')->nullable()->default(null);
            //$table->uuid('task_id')->nullable()->default(null);
            //$table->uuid('task_before')->nullable()->default(null);
            //$table->uuid('task_after')->nullable()->default(null);

            $table->foreign("feature_id")
                ->references('id')
                ->on('features')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            /*$table->foreign("task_id")
                ->references('id')
                ->on('tasks')
                ->onDelete("cascade")
                ->onUpdate("cascade");
             */
            /*$table->foreign("task_before")
                ->references('id')
                ->on('tasks')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->foreign("task_after")
                ->references('id')
                ->on('tasks')
                ->onDelete("cascade")
                ->onUpdate("cascade");*/
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
