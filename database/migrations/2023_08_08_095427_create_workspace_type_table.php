<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        Schema::create('workspace_type', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name',128);
            // $table->timestamps();
        });

        // Agregar tipos de workspace por defecto
        if(Schema::hasTable('workspace_type')){

            DB::table('workspace_type')->insert([
                [
                    'id'=>Uuid::uuid4(),
                    'name' => 'Cliente'
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name' => 'Sigel'
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
        Schema::dropIfExists('workspace_type');
    }
};
