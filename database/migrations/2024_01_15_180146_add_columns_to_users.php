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
        Schema::table('users', function (Blueprint $table) {

            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->bigInteger("email_verified_at")->nullable()->default(null);
            }

            if (!Schema::hasColumn('users', 'verification_code')) {
                $table->string("verification_code", 4)->nullable()->default(null);
            }

            if(!Schema::hasColumn("users", "color")){
                $table->string("color",12)->nullable()->default(null);
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
