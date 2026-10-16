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
        Schema::table('validation_requests', function (Blueprint $table) {
            // "ai" or "manual": the review the dentist chose (manual when AI was unavailable)
            $table->string('review_method')->default('ai')->after('id_document_path');
            // Proof of the data-processing authorization given with the upload (Ley 1581 de 2012)
            $table->timestamp('data_consent_at')->nullable()->after('review_method');
            $table->string('data_policy_version')->nullable()->after('data_consent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('validation_requests', function (Blueprint $table) {
            $table->dropColumn(['review_method', 'data_consent_at', 'data_policy_version']);
        });
    }
};
