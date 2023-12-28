<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\text;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('image')->nullable();
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->text('about')->nullable();
            $table->string('old_limit')->nullable();
            $table->string('new_limit')->nullable();
            $table->string('boosting_price')->nullable();
            $table->string('boosting_discount_price')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('support_policy')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->string('light_color')->nullable();
            $table->string('dark_color')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('wtsapp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
