<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Transaction;
use App\Models\SavingsGoal;
use App\Models\Budget;
use App\Models\Bill;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    
public function subjects(): HasMany
{
    return $this->hasMany(Subject::class);
}

public function tasks(): HasMany
{
    return $this->hasMany(Task::class);
}

public function transactions()
{
    return $this->hasMany(Transaction::class);
}

public function savingsGoals()
{
    return $this->hasMany(SavingsGoal::class);
}

public function budgets()
{
    return $this->hasMany(Budget::class);
}

public function bills()
{
    return $this->hasMany(Bill::class);
}

public function calendarEvents(): HasMany
{
    return $this->hasMany(CalendarEvent::class);
}

}
