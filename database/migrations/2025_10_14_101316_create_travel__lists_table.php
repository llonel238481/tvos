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

            // Employee Foreign Key
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

            $table->date('travel_from');
            $table->date('travel_to');
            $table->string('purpose');
            $table->string('destination');
            $table->enum('conditionalities', ['On Official Business','On Official Time','On Official Business and Time'])->nullable(); 
            
            // Transportation Foreign keys
            $table->foreignId('transportation_id')->constrained('transportations')->onDelete('cascade');

            // Faculty Foreign Key
            $table->foreignId('faculty_id')->constrained('faculties')->onDelete('cascade');

            // CEO Foreign Key
            $table->foreignId('ceo_id')->nullable()->constrained('c_e_o_s')->onDelete('set null')->after('faculty_id');

            $table->string('supervisor_signature')->nullable();
            $table->string('ceo_signature')->nullable();

            $table->string('status')->default('Pending'); // For dashboard stats
            
            $table->timestamps(); // Needed for chart monthly counts
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_lists');
    }
};
