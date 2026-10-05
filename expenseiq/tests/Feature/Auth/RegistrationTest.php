<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register_successfully()
    {
        $response = $this->post('/register', [
            'first_name' => 'nicole',
            'last_name' => 'rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => 'Nicoletest123_',
            'password_confirmation' => 'Nicoletest123_',
        ]);

        $response->assertRedirect(route('welcome.step1'));

        $this->assertDatabaseHas('users', [
            'email' => 'nicoletest@gmail.com',
            'first_name' => 'nicole',
            'last_name' => 'rafols',
        ]);

        $this->assertAuthenticated();
    }

    // unique email
    #[Test]
    public function user_cannot_register_with_existing_email()
    {
        User::create([
            'first_name' => 'nicole',
            'last_name' => 'rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => bcrypt('Nicoletest123_'),
        ]);

        $response = $this->post('/register', [
            'first_name' => 'nicole',
            'last_name' => 'rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => 'Nicoletest123_',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // password confirmation
    #[Test]
    public function password_confirmation_must_match():void
    {
        $response = $this->post('/register', [
            'first_name' => 'nicole',
            'last_name' => 'rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => 'Nicoletest123_',
            'password_confirmation' => 'WrongPassword123_',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // requires firstname
    #[Test]
    public function first_name_is_required():void
    {
        $response = $this->post('/register', [
            'first_name' => '',
            'last_name' => 'rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => 'Nicoletest123_',
        ]);
        $response->assertSessionHasErrors('first_name');
    }
}
