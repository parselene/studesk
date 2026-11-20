<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('bills', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('title');
        $table->string('category')->nullable();
        $table->decimal('amount', 12, 2);
        $table->date('due_date');

        $table->enum('status', ['pending', 'paid', 'overdue'])
            ->default('pending');

        $table->boolean('is_recurring')->default(false);
        $table->string('recurrence_frequency')->nullable();
        $table->date('paid_at')->nullable();
        $table->text('notes')->nullable();

        $table->timestamps();

        $table->index(['user_id', 'due_date', 'status']);
    });
}

public function down(): void
{
    Schema::dropIfExists('bills');
}
};
