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
        Schema::create('project_user_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_user_id')->constrained()->cascadeOnDelete();
            $table->longText('key'); // License key
            $table->string('key_version', 50); // e.g., 'v1', 'v2'
            $table->string('key_format'); // e.g., 'BFB-XXXX-XXXX-XXXX'
            $table->json('key_metadata')->nullable(); // Additional key info
            $table->dateTime('start_date')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->json('device_info')->nullable(); // Allowed device info
            $table->integer('max_devices')->default(1);
            $table->json('features')->nullable(); // Enabled features
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_validated_at')->nullable();
            $table->integer('validation_count')->default(0);
            $table->json('attributes')->nullable();
            $table->json('extras')->nullable();
            $table->timestamps();

            $table->index(['project_user_id', 'is_active']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user_keys');
    }
};
