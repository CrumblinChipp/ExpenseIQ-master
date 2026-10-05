<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;    
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    // user can view dashboard
    #[Test]
    public function user_can_view_dashboard(): void
    {

        $user = User::create([
            'first_name' => 'nicole',
            'last_name' => 'rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => bcrypt('Nicoletest123_'),
            'is_onboarded' => true,
        ]);

        $this->actingAs($user);
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // guest cannot access dashboard
    #[Test]
    public function guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

}
