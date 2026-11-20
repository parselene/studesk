
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goal_contributions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('savings_goal_id')
                ->constrained('savings_goals')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->date('contribution_date');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'contribution_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goal_contributions');
    }
};
