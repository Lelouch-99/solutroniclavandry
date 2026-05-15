<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activations', function (Blueprint $table) {
            $table->id();

            $table->string('activation_id')->unique();
            $table->string('controller_id')->index();

            $table->string('machine_id');
            $table->string('activation_type');

            $table->string('reference')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('admin_user')->nullable();
            $table->integer('amount')->nullable();

            $table->string('status')->default('pending')->index();
            $table->text('message')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activations');
    }
};