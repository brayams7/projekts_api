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
    public function up():void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('username');
            $table->string('email')->unique();
            $table->text('picture_url')->nullable();
            $table->string("color",12)->nullable();

            $table->string('password');
            $table->rememberToken();

            $table->uuid('role_id');
            $table->foreign("role_id")
                ->references('id')
                ->on('roles')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            
            $table->integer('status',false)->default(1);    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down():void
    {
        Schema::dropIfExists('users');
    }
};
