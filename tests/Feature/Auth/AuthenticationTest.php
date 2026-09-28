<?php

namespace Tests\Feature\Auth;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'teacher',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('inventory.dashboard', absolute: false));
    }

    public function test_login_does_not_redirect_to_dashboard_api_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $this->get('/inventory/dashboard/realtime')->assertRedirect(route('login'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('inventory.dashboard', absolute: false));
    }

    public function test_login_returns_to_the_item_page_opened_from_a_qr_code(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create();
        $itemUrl = route('inventory.qr.item', ['id' => $item->id], false);

        $this->get($itemUrl)->assertRedirect(route('login'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'teacher',
        ]);

        $response->assertRedirect($itemUrl);
    }

    public function test_users_can_only_authenticate_from_their_own_role_tab(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'role' => 'teacher',
        ]);

        $this->assertGuest();
    }

    public function test_unsupported_roles_cannot_authenticate(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->assertGuest();
    }

    public function test_database_seed_creates_admin_user(): void
    {
        $this->artisan('db:seed')->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@vbis.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertTrue(
            User::where('email', 'admin@vbis.test')->where('role', 'admin')->exists()
        );
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
