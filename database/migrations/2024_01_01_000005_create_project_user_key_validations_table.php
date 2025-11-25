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
        Schema::create('project_user_key_validations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('activation_id'); // Foreign key to activations
            $table->string('validation_type'); // activate, validate
            $table->text('device_info')->nullable(); // Device info from request
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('request_data')->nullable(); // Full request details
            $table->string('response_status'); // success, error
            $table->string('error_code')->nullable();
            $table->dateTime('validated_at');
            $table->timestamps();

            // Foreign key with custom name
            $table->foreign('activation_id', 'key_validations_activation_fk')
                ->references('id')
                ->on('project_user_key_activations')
                ->cascadeOnDelete();

            $table->index(['activation_id', 'validated_at']);
            $table->index('validation_type');
            $table->index('validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user_key_validations');
    }
};
