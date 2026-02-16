<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add soft deletes and audit trail fields to core tables
     */
    public function up(): void
    {
        // Events - soft delete + audit
        Schema::table('events', function (Blueprint $table) {
            $table->softDeletes()->after('qr_active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->after('updated_by');
        });

        // Event Details - soft delete
        Schema::table('event_details', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // Event Packages - soft delete
        Schema::table('event_packages', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // Event Guests - soft delete
        Schema::table('event_guests', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // Event Photos - soft delete
        Schema::table('event_photos', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // Orders - soft delete + status audit
        Schema::table('orders', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
            $table->string('cancelled_reason')->nullable()->after('status');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('cancelled_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('cancelled_reason');
            $table->dropForeignKeyIfExists(['cancelled_by']);
            $table->dropColumn('cancelled_by');
        });

        Schema::table('event_photos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('event_guests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('event_packages', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('event_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeignKeyIfExists(['created_by']);
            $table->dropColumn('created_by');
            $table->dropForeignKeyIfExists(['updated_by']);
            $table->dropColumn('updated_by');
            $table->dropForeignKeyIfExists(['deleted_by']);
            $table->dropColumn('deleted_by');
        });
    }
};
