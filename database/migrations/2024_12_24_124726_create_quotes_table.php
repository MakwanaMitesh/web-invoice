<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('quotes', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id');
      $table->string('quote_code')->unique();
      $table->date('quote_date')->nullable();
      $table->date('due_date')->nullable();
      $table->string('status')->default('draft'); // Default to 'Draft'
      $table->unsignedBigInteger('template_id')->nullable();
      $table->decimal('discount', 8, 2)->nullable();
      $table->string('discount_type')->nullable(); // Options: 'flat', 'percentage'
      $table->decimal('final_amount', 12, 2)->nullable();
      $table->text('note')->nullable();
      $table->text('term')->nullable();
      $table->timestamps();

      // Foreign keys
      $table
        ->foreign('user_id')
        ->references('id')
        ->on('users')
        ->onDelete('cascade');
      $table
        ->foreign('template_id')
        ->references('id')
        ->on('templates')
        ->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('quotes');
  }
};
