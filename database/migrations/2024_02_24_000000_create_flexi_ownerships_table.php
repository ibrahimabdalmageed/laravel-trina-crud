<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flexi_ownerships', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('guard_name')->index();
            $table->timestamps();

            $table->unique(['table_name', 'record_id', 'user_id', 'guard_name'], 'ownership_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flexi_ownerships');
    }
};
