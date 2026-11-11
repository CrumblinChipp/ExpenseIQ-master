<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'icon',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeVisibleTo($query, $userId)
    {
        return $query->where(
            fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $userId)
        );
    }
}

