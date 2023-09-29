<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

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
            $table->uuid('id')->primary();
            $table->string('name',50);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        if(Schema::hasTable("roles")){
            DB::table("roles")->insert([
                [
                    'id'=>Uuid::uuid4(),
                    'name'=>'ADMINISTRADOR',
                    'description'=>'administrador del sistema'
                ],
                [
                    'id'=>Uuid::uuid4(),
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
