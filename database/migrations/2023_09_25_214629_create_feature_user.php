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
        Schema::create('feature_user', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('feature_id');


            $table->foreign("user_id")
                ->references('id')
                ->on('users')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->foreign("feature_id")
                ->references('id')
                ->on('features')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->tinyInteger('is_watcher');

            $table->unique(['feature_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_user');
    }
};
