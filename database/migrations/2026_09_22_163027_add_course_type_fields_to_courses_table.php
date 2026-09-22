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
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('type', ['virtual', 'presencial'])->default('virtual')->after('title');
            $table->string('category')->nullable()->after('type');
            $table->decimal('rating', 2, 1)->nullable()->after('category');

            // Presencial-only
            $table->date('event_date')->nullable()->after('rating');
            $table->string('location')->nullable()->after('event_date');
            $table->unsignedInteger('capacity')->nullable()->after('location');
            $table->unsignedInteger('seats_taken')->default(0)->after('capacity');
            $table->decimal('deposit_amount', 10, 2)->nullable()->after('seats_taken');

            // Virtual-only
            $table->unsignedInteger('duration_hours')->nullable()->after('deposit_amount');
            $table->string('level')->nullable()->after('duration_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'category', 'rating', 'event_date', 'location',
                'capacity', 'seats_taken', 'deposit_amount', 'duration_hours', 'level',
            ]);
        });
    }
};
