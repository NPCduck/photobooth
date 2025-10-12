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
        Schema::table('event_details', function(Blueprint $table) {
            $table->text('loc_address')->change();
        });

        Schema::table('event_packages', function(Blueprint $table) {
            $table->integer('photo_limit_total');
            $table->integer('photo_limit_person')->nullable();
        });

        Schema::table('event_packages', function(Blueprint $table) {
            $table->dropColumn('photo_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
