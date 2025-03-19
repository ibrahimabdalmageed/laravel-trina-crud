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
            $table->string('column_db_type');
            $table->string('column_user_type')->nullable();
            $table->string('column_label')->nullable();
            $table->boolean('required')->default(false);
            $table->string('default_value')->nullable();
            $table->integer('grid_order')->nullable();
            $table->integer('edit_order')->nullable();
            $table->integer('size')->nullable(); // For VARCHAR length
            $table->boolean('hide')->default(false);
            $table->timestamps();

            $table->unique(['trina_crud_model_id', 'column_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trina_crud_columns');
    }
};
