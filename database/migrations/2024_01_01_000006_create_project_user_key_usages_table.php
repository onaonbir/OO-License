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
        Schema::create('project_user_key_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_user_key_id')->constrained()->cascadeOnDelete();
            $table->string('event_type')->index(); // app_opened, feature_used, button_clicked, error_occurred, custom
            $table->string('event_name'); // Descriptive name: "Export PDF", "Premium Feature Used"
            $table->json('event_data')->nullable(); // Custom event data
            $table->json('metadata')->nullable(); // ip, user_agent, device_info, app_version, etc.
            $table->timestamp('created_at')->index();

            $table->index(['project_user_key_id', 'event_type'], 'puk_usages_key_type_idx');
            $table->index(['project_user_key_id', 'created_at'], 'puk_usages_key_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user_key_usages');
    }
};
