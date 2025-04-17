<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('base_template_id')->nullable();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('duration')->nullable();
            $table->string('customizable')->nullable();
            $table->string('status')->nullable();
            $table->json('custom_data');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('templates');
    }
};
