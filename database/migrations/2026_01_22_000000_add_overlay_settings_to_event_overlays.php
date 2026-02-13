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
        Schema::table('event_overlays', function (Blueprint $table) {
            // Pozícia overlay: 'stretch', 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'
            $table->string('frame_position')->default('center')->after('frame_img');
            // Či sa má overlay rozťahovať: true = roztiahne sa, false = zachová si veľkosť
            $table->boolean('frame_stretch')->default(true)->after('frame_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_overlays', function (Blueprint $table) {
            $table->dropColumn('frame_position');
            $table->dropColumn('frame_stretch');
        });
    }
};
