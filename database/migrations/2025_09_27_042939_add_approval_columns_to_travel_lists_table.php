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
        Schema::table('travel_lists', function (Blueprint $table) {
            $table->string('supervisor_signature')->nullable()->after('status');
            $table->string('ceo_signature')->nullable()->after('supervisor_signature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_lists', function (Blueprint $table) {
            $table->dropColumn(['supervisor_signature', 'ceo_signature']);
        });
    }
};
