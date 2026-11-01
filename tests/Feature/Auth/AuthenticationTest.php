<?php

namespace Tests\Feature\Auth;

use App\Livewire\Pages\Auth\Login;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('nomor_induk', $user->nomor_induk)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('nomor_induk', $user->nomor_induk)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('nomor_induk');

        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        $component = Livewire::test(Login::class)->set('nomor_induk', $user->nomor_induk);
        for ($i = 0; $i < 5; $i++) {
            $component->set('password', 'wrong-password')->call('login');
        }

        $component->set('password', 'password')
            ->call('login')
            ->assertHasErrors('nomor_induk')
            ->assertSee('Terlalu banyak percobaan login');

        $this->assertGuest();
    }

    public function test_admin_dashboard_redirects_admin_to_admin_dashboard(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'nomor_induk' => 'admin-redirect-test',
            'email' => 'admin-redirect@example.test',
            'password' => 'password',
        ]);
        $adminRole = Role::create(['role' => 'admin']);
        $user->roles()->attach($adminRole);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_dashboard_redirects_non_admin_to_booking_page(): void
    {
        $user = User::create([
            'name' => 'Student Test',
            'nomor_induk' => 'student-redirect-test',
            'email' => 'student-redirect@example.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('booking.index'));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
