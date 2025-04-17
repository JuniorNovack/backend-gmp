<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('group_id')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->nullable();
            $table->string('resolution')->nullable();
            $table->string('volume')->nullable();
            $table->string('brightness')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('screens');
    }
};
