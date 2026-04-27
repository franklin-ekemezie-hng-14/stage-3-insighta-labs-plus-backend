<?php

use App\Enums\AgeGroup;
use App\Enums\Gender;
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
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();

            $table->enum('gender', Gender::values());
            $table->float('gender_probability');

            $table->integer('age');
            $table->enum('age_group', AgeGroup::values());

            $table->string('country_id', 2);
            $table->string('country_name');
            $table->float('country_probability');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
