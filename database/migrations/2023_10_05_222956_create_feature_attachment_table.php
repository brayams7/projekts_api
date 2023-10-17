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
        Schema::create('feature_attachment', function (Blueprint $table) {
            $table->uuid('feature_id');
            $table->uuid('attachment_id');

            $table->foreign("feature_id")
                ->references('id')
                ->on('features')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->foreign("attachment_id")
                ->references('id')
                ->on('attachments')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->unique(['feature_id','attachment_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_attachment');
    }
};
