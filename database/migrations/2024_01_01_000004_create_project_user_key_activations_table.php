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
        Schema::create('project_user_key_activations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_user_key_id')->constrained()->cascadeOnDelete();
            $table->string('device_id')->index();
            $table->text('device_info'); // Device information (JSON stored as text)
            $table->dateTime('activated_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['project_user_key_id', 'device_id'], 'puk_activations_key_device_idx');
            $table->unique(['project_user_key_id', 'device_id'], 'puk_activations_key_device_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user_key_activations');
    }
};
