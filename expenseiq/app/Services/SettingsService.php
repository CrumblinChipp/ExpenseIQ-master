<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SettingsService
{
    public function updateProfile(User $user, array $data): void
    {
        $user->update([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'nickname'   => $data['nickname'],
            'email'      => $data['email'],
        ]);
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update([
            'password' => Hash::make($password),
        ]);
    }
}