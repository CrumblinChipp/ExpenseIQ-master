<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'nickname',
        'email',
        'password',
        'monthly_budget',
        'is_onboarded',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function budget()
    {
        return $this->hasMany(Budget::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}