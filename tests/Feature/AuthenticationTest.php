<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/app')->assertRedirect('/login');
    }

    public function test_operator_can_sign_in_and_out(): void
    {
        $user = $this->user();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/app');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = $this->user();

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'incorrect'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    private function user(): User
    {
        $workspace = Workspace::query()->create([
            'name' => 'Authentication Lab', 'slug' => 'auth-lab', 'timezone' => 'UTC', 'currency' => 'USD',
        ]);

        return User::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Test Operator',
            'email' => 'operator@example.test',
            'password' => Hash::make('password'),
            'role' => 'Revenue Operations',
        ]);
    }
}
