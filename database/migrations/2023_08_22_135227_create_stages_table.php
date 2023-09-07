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
        Schema::create('stages', function (Blueprint $table) {
          $table->id();
          $table->string("name",64);
          $table->tinyText("description")->nullable()->default(null);
          $table->string("color",12)->nullable()->default(null);
          $table->tinyInteger("is_default")->default(0);
          $table->tinyInteger("is_final")->default(0);
          //$table->timestamps();
        });

        if(Schema::hasTable("stages")){
          DB::table("stages")->insert([
            [

              'name'=> 'Backlog',
              'description'=>'Lista priorizada de funcionalidades que debe contener un producto',
              'color'=>'#04a9f4',
              'is_default'=>1,
              'is_final' => 0
            ],
            [

              'name'=> 'Trabajando',
              'description'=>'Funcionalidades que se estan trabajando',
              'color'=>'#668d14',
              'is_default'=>1,
              'is_final' => 0
            ],
            [

              'name'=> 'En Pausa',
              'description'=>'Funcionalidades que se estan en pausa',
              'color'=>'#e50000',
              'is_default'=>1,
              'is_final' => 0
            ],
            [

                'name'=> 'En QA',
                'description'=>'Funcionalidades que estan en revisión',
                'color'=>"#04a9f4",
                'is_default'=>1,
                'is_final' => 0
            ],
            [

              'name'=> 'Aceptada',
              'description'=>'Funcionalidades que han sido aceptados por el cliente',
              'color'=>'#ff7800',
              'is_default'=>1,
              'is_final' => 0
            ],
            [

              'name'=> 'Finalizada',
              'description'=>'Funcionalidades finalizados',
              'color'=>'#6bc950',
              'is_default'=>1,
              'is_final' => 1
            ],
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
        Schema::dropIfExists('stages');
    }
};
