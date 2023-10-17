<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;
use \Ramsey\Uuid\Uuid;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachment_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("name",45);
            $table->string("extension",45);
            $table->string("mimetype",128);
        });

        if(Schema::hasTable("attachment_types")){
            DB::table("attachment_types")->insert([
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'Texto plano',
                    'extension'=>'.txt',
                    'mimetype'=>'text/plain',

                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'Archivo csv',
                    'extension'=>'.csv',
                    'mimetype'=>'text/csv',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'Microsoft Word',
                    'extension'=>'.doc',
                    'mimetype'=>'application/msword',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'Microsoft Word (OpenXML)',
                    'extension'=>'.docx',
                    'mimetype'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'Graphics Interchange Format (GIF)',
                    'extension'=>'.gif',
                    'mimetype'=>'image/gif',
                ],

                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'JPG images',
                    'extension'=>'.jpg',
                    'mimetype'=>'image/jpg',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'JPEG images',
                    'extension'=>'.jpeg',
                    'mimetype'=>'image/jpeg',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'WEBP image',
                    'extension'=>'.webp',
                    'mimetype'=>'image/webp',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'Portable Network Graphics',
                    'extension'=>'.png',
                    'mimetype'=>'image/png',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'PDF',
                    'extension'=>'.pdf',
                    'mimetype'=>'application/pdf',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'Microsoft PowerPoint',
                    'extension'=>'.ppt',
                    'mimetype'=>'application/vnd.ms-powerpoint',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'Microsoft PowerPoint (OpenXML)',
                    'extension'=>'.pptx',
                    'mimetype'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'Microsoft PowerPoint (OpenXML)',
                    'extension'=>'.xls',
                    'mimetype'=>'application/vnd.ms-excel',
                ],
                [
                    'id'=>Uuid::uuid4(),
                    'name'=> 'Microsoft Excel (OpenXML)',
                    'extension'=>'.xlsx',
                    'mimetype'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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
        Schema::dropIfExists('attachment_types');
    }
};
