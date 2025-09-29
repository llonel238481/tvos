<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_lists', function (Blueprint $table) {
            $table->id();
            $table->date('travel_date');
            $table->string('purpose');
            $table->string('destination');
            $table->enum('conditionalities', ['On Official Business','On Official Time','On Official Business and Time'])->nullable(); 
            
            // Foreign keys
            $table->foreignId('transportation_id')->constrained('transportations')->onDelete('cascade');
            $table->foreignId('faculty_id')->constrained('faculties')->onDelete('cascade');

            $table->string('status')->default('Pending'); // For dashboard stats
            
            $table->timestamps(); // Needed for chart monthly counts
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_lists');
    }
};
