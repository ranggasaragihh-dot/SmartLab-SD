<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_roles_and_is_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertEqualsCanonicalizing(['admin', 'student', 'lecturer'], Role::pluck('role')->all());
    }

    public function test_create_admin_command_creates_a_user_that_can_log_in_as_admin(): void
    {
        $this->seed();

        $this->artisan('app:create-admin', ['--nomor-induk' => 'admin01', '--name' => 'Admin Lab', '--email' => ''])
            ->expectsQuestion('Password (minimal 8 karakter)', 'rahasia123')
            ->expectsQuestion('Ulangi password', 'rahasia123')
            ->assertSuccessful();

        $admin = User::where('nomor_induk', 'admin01')->firstOrFail();
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertNull($admin->email);

        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    }

    public function test_create_admin_rejects_short_password(): void
    {
        $this->artisan('app:create-admin', ['--nomor-induk' => 'admin02', '--name' => 'Admin Lab', '--email' => ''])
            ->expectsQuestion('Password (minimal 8 karakter)', 'pendek')
            ->expectsQuestion('Ulangi password', 'pendek')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['nomor_induk' => 'admin02']);
    }
}
