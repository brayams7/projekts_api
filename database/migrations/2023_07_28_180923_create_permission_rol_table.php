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
    public function up()
    {
        Schema::create('permission_rol', function (Blueprint $table) {
            //$table->uuid('id')->primary();

            $table->uuid('permission_id');
            $table->uuid('role_id');

            $table->foreign("permission_id")
                ->references('id')
                ->on('permissions')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->foreign("role_id")
                ->references('id')
                ->on('roles')
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->unique(['permission_id', 'role_id']);

            /*$table->foreignId("permission_id")
                ->constrained("permissions")
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->foreignId("role_id")
                ->constrained("roles")
                ->onDelete("cascade")
                ->onUpdate("cascade");*/
            //$table->unsignedBigInteger('permission_id');
            //$table->unsignedBigInteger('role_id');

            //agregando las restricciones de llave foranea.
            //$table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            //$table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_rol');
    }
};
