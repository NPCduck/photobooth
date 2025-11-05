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
            $table->uuid('public_token')->unique()->nullable();
            $table->boolean('qr_active')->default(false);
            $table->timestamps();
        });

        Schema::create('event_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->date('date');
            $table->time('time_start');
            $table->time('time_end')->nullable();
            $table->string('status')->default('upcoming');
            $table->integer('hosts');
            $table->string('loc_venue');
            $table->text('loc_address');
            $table->timestamps();
        });

        Schema::create('event_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 6, 2);
            $table->integer('photo_limit_total');
            $table->integer('photo_limit_person')->nullable();
            $table->timestamps();
        });

        Schema::create('event_overlays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->boolean('landing_img');
            $table->boolean('frame_img');
            $table->timestamps();
        });

        Schema::create('event_client', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->timestamps();
        });

        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('action_type');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::dropIfExists('actions');
        Schema::dropIfExists('event_client');
        Schema::dropIfExists('event_overlays');
        Schema::dropIfExists('event_packages');
        Schema::dropIfExists('event_details');
        Schema::dropIfExists('events');
    }
};
