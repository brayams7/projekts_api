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
        Schema::create('feature_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->longText('comment')->nullable()->default(null);

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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_comments');
    }
};
