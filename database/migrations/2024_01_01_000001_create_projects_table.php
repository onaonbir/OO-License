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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version', 50);
            $table->text('description')->nullable();
            $table->text('encryption_key'); // Encrypted storage
            $table->string('encryption_method')->default('AES-256-CBC');
            $table->string('key_generator_class'); // e.g., 'bfb.v1', 'bfb.v2'
            $table->string('key_version', 50); // e.g., 'v1', 'v2'
            $table->text('secret_key'); // Encrypted storage
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // max_devices, features, etc.
            $table->json('attributes')->nullable();
            $table->json('extras')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
