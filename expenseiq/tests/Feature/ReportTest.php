<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function aunthenticated_user_can_view(): void
    {
        $user = USER::create([
            'first_name' => 'nicole',
            'last_name' => 'rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => bcrypt('Nicoletest123_'),
            'is_onboarded' => true,
        ]);
        
        $this->actingAs($user);

        Livewire::test(\App\Livewire\Reports\Index::class)
            ->assertStatus(200);

    }
}
