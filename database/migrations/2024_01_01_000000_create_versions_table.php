<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('model-versions.table_name', 'versions');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('versionable_type');
            $table->unsignedBigInteger('versionable_id');
            $table->unsignedInteger('version_number');
            $table->json('data');
            $table->string('created_by')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('created_at');

            $table->index(['versionable_type', 'versionable_id']);
            $table->index(['versionable_type', 'versionable_id', 'version_number']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        $tableName = config('model-versions.table_name', 'versions');

        Schema::dropIfExists($tableName);
    }
};