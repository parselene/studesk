
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type')->default('personal');

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();

            // Optional link to a task or transaction.
            $table->nullableMorphs('eventable');

            $table->timestamps();

            $table->index(['user_id', 'starts_at']);
            $table->index(['user_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
