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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('event_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->date('date');
            $table->time('time_start');
            $table->time('time_end')->nullable();
            $table->string('status');
            $table->string('loc_venue');
            $table->string('loc_address');
            $table->timestamps();
        });

        Schema::create('event_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 6, 2);
            $table->integer('photo_limit')->nullable();
            $table->timestamps();
        });

        Schema::create('event_overlays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('landing_img')->nullable();
            $table->string('frame_img')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::dropIfExists('event_overlays');
        Schema::dropIfExists('event_packages');
        Schema::dropIfExists('event_details');
        Schema::dropIfExists('events');
    }
};
