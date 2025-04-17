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
        Schema::create('share_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('media_file_id');
            $table->unsignedBigInteger('shared_with_id');
            $table->enum('permission_type', ['view', 'edit']);
            $table->unsignedBigInteger('shared_by_id');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['media_file_id', 'shared_with_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_permissions');
    }
};
