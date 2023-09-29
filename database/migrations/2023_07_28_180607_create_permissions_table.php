<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Ramsey\Uuid\Uuid;
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
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name',50);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        if(Schema::hasTable("permissions")){
            DB::table("permissions")->insert([
                [
                    'id'=>Uuid::uuid4(),
                    'name'=>'dashboard',
                    'description'=>'Ver el dashboard de los boards'
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=>'usuarios',
                    'description'=>'ver los usuarios'
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=>'workspace',
                    'description'=>'ver los espacios de trabajo'
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=>'board',
                    'description'=>'ver los tableros'
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=>'tasks',
                    'description'=>'ver las tareas'
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=>'settings',
                    'description'=>'configuraciones'
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=>'members',
                    'description'=>'ver los miembros de los espacios de trabajo'
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=>'views',
                    'description'=>'ver los modos de vista de trableros'
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
        Schema::dropIfExists('permissions');
    }
};
