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
        Schema::table('purchases', function (Blueprint $table) {
            // Our unique sale reference sent to Bold, e.g. AV-15-20261016165219
            $table->string('bold_reference', 60)->nullable()->unique()->after('bold_payment_id');
            // Bold's payment link id (LNK_...): webhooks for links identify the sale by it
            $table->string('bold_payment_link')->nullable()->index()->after('bold_reference');
            // Checkout URL, reused while the purchase is pending so retries don't create new links
            $table->string('bold_payment_url')->nullable()->after('bold_payment_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique(['bold_reference']);
            $table->dropIndex(['bold_payment_link']);
            $table->dropColumn(['bold_reference', 'bold_payment_link', 'bold_payment_url']);
        });
    }
};
