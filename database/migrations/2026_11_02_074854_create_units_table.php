<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('short_name', 20);
            $table->boolean('allow_decimal')->default(false);
            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique('short_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
