<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // user can login successfully
    #[Test]
    public function user_can_login_successfully(): void
    {
    
        $user = User::create([
            'first_name' => 'Nicole',
            'last_name' => 'Rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => Hash::make('Nicoletest123_'),
            'is_onboarded' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'nicoletest@gmail.com',
            'password' => 'Nicoletest123_',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    // user cannot login with wrong password
    #[Test]
    public function user_cannot_login_with_wrong_password(): void
    {
        $user = User::create([
            'first_name' => 'Nicole',
            'last_name' => 'Rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => Hash::make('Nicoletest123_'),
            'is_onboarded' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'nicoletest@gmail.com',
            'password' => 'WrongPassword123_',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    // email unknown
    #[Test]
    public function user_cannot_login_with_unknown_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'unknown@gmail.com',
            'password' => 'Nicoletest123_',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }
}