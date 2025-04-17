<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->string('screen_id')->nullable();
            $table->string('views')->nullable();
            $table->string('engagement')->nullable();
            $table->string('duration')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('statistics');
    }
};
