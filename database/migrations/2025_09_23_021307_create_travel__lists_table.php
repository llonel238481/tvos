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
        Schema::create('travel__lists', function (Blueprint $table) {
            $table->id();
            $table->date('travel_date');
            $table->string('request');       // requesting party
            $table->string('purpose');
            $table->string('destination');
            $table->enum('conditionalities', ['On Official Business','On Official Time','On Official Business and Time']); // ✅ added conditionalities column

            // Transportation - Foreign Keys
            $table->foreignId('transportation_id')
              ->constrained('transportations')
              ->onDelete('cascade');

            // Faculty - Foreign Keys
            $table->foreignId('faculty_id')
              ->constrained('faculties')
              ->onDelete('cascade');
              
            $table->string('status')->default('Pending'); // ✅ added status column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel__lists');
    }
};
