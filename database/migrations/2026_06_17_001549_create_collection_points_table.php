<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the waste collection points table.
     */
    public function up(): void
    {
        Schema::create('collection_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // name of the place
            $table->string('address');              // free-text address
            $table->decimal('latitude', 10, 7);     // coordinate
            $table->decimal('longitude', 10, 7);    // coordinate
            $table->json('waste_types');            // list of waste types (array)
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('status')->default('pending'); // 'pending' or 'approved'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_points');
    }
};
