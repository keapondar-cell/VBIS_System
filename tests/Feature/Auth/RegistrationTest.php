<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\AccountApprovalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_register_as_pending_until_admin_approval(): void
    {
        $response = $this->withSession(['_token' => 'test'])
            ->post('/register', [
                '_token' => 'test',
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'teacher',
            ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'teacher',
            'is_active' => false,
        ]);
    }

    public function test_property_custodian_registration_stays_pending_until_approved(): void
    {
        $response = $this->withSession(['_token' => 'test'])
            ->post('/register', [
                '_token' => 'test',
                'name' => 'Property Custodian',
                'email' => 'custodian@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'property_custodian',
            ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'custodian@example.com',
            'role' => 'property_custodian',
            'is_active' => false,
        ]);
    }

    public function test_new_registrations_require_admin_approval_before_login(): void
    {
        $response = $this->withSession(['_token' => 'test'])
            ->post('/register', [
                '_token' => 'test',
                'name' => 'Pending Teacher',
                'email' => 'pending@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'teacher',
            ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'pending@example.com',
            'role' => 'teacher',
            'is_active' => false,
        ]);

        $this->withSession(['_token' => 'test'])
            ->post('/login', [
                '_token' => 'test',
                'email' => 'pending@example.com',
                'password' => 'password',
                'role' => 'teacher',
            ])->assertSessionHasErrors(['email']);
    }

    public function test_new_registration_sends_admin_approval_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->withSession(['_token' => 'test'])
            ->post('/register', [
                '_token' => 'test',
                'name' => 'New Applicant',
                'email' => 'applicant@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'teacher',
            ]);

        $response->assertRedirect(route('login', absolute: false));

        $user = User::where('email', 'applicant@example.com')->firstOrFail();

        Notification::assertSentTo($admin, AccountApprovalNotification::class, function ($notification, $channels) use ($user) {
            return $notification->user->id === $user->id
                && $notification->status === 'pending';
        });
    }

    public function test_approving_a_user_sends_account_status_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'teacher',
            'is_active' => false,
        ]);

        $this->actingAs($admin);

        $this->withSession(['_token' => 'test'])
            ->post(route('admin.users.approve', $user->id), ['_token' => 'test'])
            ->assertRedirect();

        Notification::assertSentTo($user, AccountApprovalNotification::class, function ($notification, $channels) {
            return $notification->status === 'approved';
        });
    }
}
