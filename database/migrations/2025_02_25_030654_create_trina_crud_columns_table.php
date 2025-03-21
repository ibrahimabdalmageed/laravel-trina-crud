<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trina_crud_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trina_crud_model_id')->constrained('trina_crud_models')->onDelete('cascade');
            $table->string('column_name');
            $table->string('column_label')->nullable();
            $table->timestamps();

            $table->unique(['trina_crud_model_id', 'column_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trina_crud_columns');
    }
};
