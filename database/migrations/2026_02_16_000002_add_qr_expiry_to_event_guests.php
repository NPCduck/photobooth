<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add QR expiry field to event guests for time-limited capture
     */
    public function up(): void
    {
        Schema::table('event_guests', function (Blueprint $table) {
            $table->timestamp('qr_expires_at')->nullable()->after('photos_uploaded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_guests', function (Blueprint $table) {
            $table->dropColumn('qr_expires_at');
        });
    }
};
