<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trina_crud_models', function (Blueprint $table) {
            $table->id();
            $table->string('class_name', 100)->unique();
            $table->string('caption', 100)->nullable();
            $table->string('multi_caption', 100)->nullable();
            $table->integer('page_size')->default(20);
            $table->boolean('public_model')->default(false);
            $table->string('order_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trina_crud_models');
    }
};
