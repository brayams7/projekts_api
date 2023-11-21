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
        Schema::create('feature_comment_attachment', function (Blueprint $table) {
//            $table->id();
//            $table->timestamps();
            $table->uuid('feature_comment_id');
            $table->uuid('attachment_id');

            $table->foreign('feature_comment_id')
                ->references('id')
                ->on('feature_comments')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('attachment_id')
                ->references('id')
                ->on('attachments')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_comment_attachment');
    }
};
