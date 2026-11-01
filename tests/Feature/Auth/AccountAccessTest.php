<?php

namespace Tests\Feature\Auth;

use App\Livewire\Admin\UserManagement;
use App\Livewire\Pages\Auth\Login;
use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivated_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['is_banned' => true]);

        Livewire::test(Login::class)
            ->set('nomor_induk', $user->nomor_induk)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors('nomor_induk')
            ->assertSee('Akun Anda dinonaktifkan');

        $this->assertGuest();
    }

    public function test_user_deactivated_during_a_session_is_logged_out(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/booking')->assertOk();

        $user->update(['is_banned' => true]);

        $this->get('/booking')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_can_deactivate_and_reactivate_a_user_and_pending_bookings_are_released(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['role' => 'admin']));
        $student = User::factory()->create();
        $cluster = Cluster::create(['name' => 'Lab A']);
        $pc = Inventory::create(['cluster_id' => $cluster->id, 'name' => 'PC-01']);
        $booking = Booking::create([
            'user_id' => $student->id,
            'cluster_id' => $cluster->id,
            'inventory_id' => $pc->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'status' => 'booked',
        ]);

        Livewire::actingAs($admin)->test(UserManagement::class)->call('toggleActive', $student->id);
        $this->assertTrue($student->fresh()->is_banned);
        $this->assertSame('cancelled', $booking->fresh()->status);

        Livewire::actingAs($admin)->test(UserManagement::class)->call('toggleActive', $student->id);
        $this->assertFalse($student->fresh()->is_banned);

        Livewire::actingAs($admin)->test(UserManagement::class)->call('toggleActive', $admin->id);
        $this->assertFalse($admin->fresh()->is_banned);
    }

    public function test_scanning_qr_before_login_checks_in_right_after_login(): void
    {
        $student = User::factory()->create();
        $cluster = Cluster::create(['name' => 'Lab A']);
        $pc = Inventory::create(['cluster_id' => $cluster->id, 'name' => 'PC-01']);
        $booking = Booking::create([
            'user_id' => $student->id,
            'cluster_id' => $cluster->id,
            'inventory_id' => $pc->id,
            'start_time' => now()->subMinute(),
            'end_time' => now()->addHour(),
            'status' => 'booked',
        ]);
        $scanUrl = route('scan.qr', $pc->qr_token);

        $this->get($scanUrl)->assertRedirect(route('login'));

        Livewire::test(Login::class)
            ->set('nomor_induk', $student->nomor_induk)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect($scanUrl);

        $this->get($scanUrl)->assertRedirect(route('booking.index'));
        $this->assertSame('active', $booking->fresh()->status);
    }

    public function test_non_admin_gets_forbidden_on_admin_pages(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/admin/manajemen-user')->assertForbidden();
        $this->actingAs($student)->get('/admin/booking')->assertForbidden();
    }
}
