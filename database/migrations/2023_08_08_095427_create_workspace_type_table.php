<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('workspace_type', function (Blueprint $table) {
            $table->id();
            $table->string('name',128);
            // $table->timestamps();
        });

        // Agregar tipos de workspace por defecto
        if(Schema::hasTable('workspace_type')){

            DB::table('workspace_type')->insert([
                ['name' => 'Cliente'],
                ['name' => 'Sigel'],
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
        Schema::dropIfExists('workspace_type');
    }
};
