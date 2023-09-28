<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('create_tests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->boolean('gender')->comment('0為女性，1為男性');
            $table->text('remark');
            $table->timestamps();
        });

        DB::table('create_tests')->update([
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('create_tests');
    }
};
