<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        if(Schema::hasTable("roles")){
            DB::table("roles")->insert([
                [
                    'name'=>'ADMINISTRADOR',
                    'description'=>'administrador del sistema'
                ],
                [
                    'name'=>'MIEMBRO',
                    'description'=>'miembro asociado a un espacio de trabajo'
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
