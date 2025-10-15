<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('extensionname')->nullable();
            $table->enum('sex', ['Male', 'Female']);
            $table->string('email')->unique(); // ✅ Added email

            // Department foreign key
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->onDelete('set null');

            // User foreign key
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
