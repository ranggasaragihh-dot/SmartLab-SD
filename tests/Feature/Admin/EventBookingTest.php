<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Booking\Manager;
use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Buku Panduan 4.5 / SKPL UC-04: reservasi ruangan penuh (Mass Booking / Event) oleh admin.
 */
class EventBookingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $lecturer;
    private Cluster $cluster;
    private Inventory $pc;
    private string $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::create(['role' => 'admin']));
        $this->lecturer = User::factory()->create(['name' => "Dr. Ma'ruf"]);
        $this->lecturer->roles()->attach(Role::create(['role' => 'lecturer']));
        $this->cluster = Cluster::create(['name' => 'Lab TA', 'type' => 'private', 'requires_ta' => true]);
        $this->pc = Inventory::create(['cluster_id' => $this->cluster->id, 'name' => 'PC-01']);
        $this->date = Carbon::tomorrow()->toDateString();
    }

    private function manager()
    {
        return Livewire::actingAs($this->admin)
            ->test(Manager::class, ['clusterId' => $this->cluster->id])
            ->set('dateFilter', $this->date);
    }

    private function fillEvent($component, string $start = '09:00', string $end = '12:00')
    {
        return $component->call('openEventModal')
            ->set('inputStartTime', $start)
            ->set('inputEndTime', $end)
            ->call('selectUser', $this->lecturer->id)
            ->set('purpose', 'Praktikum Struktur Data');
    }

    public function test_event_blocks_the_whole_room_for_a_lecturer_even_in_a_ta_lab(): void
    {
        $this->fillEvent($this->manager())
            ->assertSet('selectedTargetUserName', "Dr. Ma'ruf")
            ->call('storeEventBooking')
            ->assertHasNoErrors()
            ->assertSet('showClusterBookingModal', false);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->lecturer->id,
            'cluster_id' => $this->cluster->id,
            'inventory_id' => null,
            'purpose' => 'Praktikum Struktur Data',
            'start_time' => "{$this->date} 09:00:00",
        ]);
    }

    public function test_opening_a_pc_booking_first_does_not_turn_the_event_into_a_pc_booking(): void
    {
        $component = $this->manager()
            ->call('openCreateUnitModal', $this->pc->id)
            ->set('showCreateModal', false);

        $this->fillEvent($component)->call('storeEventBooking')->assertHasNoErrors();

        $this->assertDatabaseHas('bookings', ['inventory_id' => null, 'cluster_id' => $this->cluster->id]);
        $this->assertDatabaseMissing('bookings', ['inventory_id' => $this->pc->id]);
    }

    public function test_conflicting_individual_bookings_are_shown_first_and_cancelled_only_after_confirmation(): void
    {
        $student = User::factory()->create(['name' => 'Gilang']);
        $conflict = Booking::create([
            'user_id' => $student->id,
            'cluster_id' => $this->cluster->id,
            'inventory_id' => $this->pc->id,
            'start_time' => "{$this->date} 10:00:00",
            'end_time' => "{$this->date} 11:00:00",
            'status' => 'booked',
        ]);

        $component = $this->fillEvent($this->manager())
            ->call('storeEventBooking')
            ->assertSee('Konflik jadwal')
            ->assertSee('Gilang')
            ->assertSet('showClusterBookingModal', true);

        $this->assertSame('booked', $conflict->fresh()->status);
        $this->assertDatabaseMissing('bookings', ['inventory_id' => null]);

        $component->call('storeEventBooking', true)->assertSet('showClusterBookingModal', false);

        $this->assertSame('cancelled', $conflict->fresh()->status);
        $this->assertStringContainsString('Praktikum Struktur Data', $conflict->fresh()->cancel_reason);
        $this->assertDatabaseHas('bookings', ['inventory_id' => null, 'cluster_id' => $this->cluster->id]);
    }

    public function test_event_requires_purpose_and_a_future_time(): void
    {
        $this->fillEvent($this->manager())
            ->set('purpose', '')
            ->call('storeEventBooking')
            ->assertHasErrors('purpose');

        $this->fillEvent($this->manager()->set('dateFilter', Carbon::yesterday()->toDateString()))
            ->call('storeEventBooking')
            ->assertHasErrors('inputEndTime');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_admin_can_only_finish_checked_in_sessions(): void
    {
        $booking = Booking::create([
            'user_id' => $this->lecturer->id,
            'cluster_id' => $this->cluster->id,
            'inventory_id' => $this->pc->id,
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addHour(),
            'status' => 'booked',
        ]);

        $this->manager()->call('openFinishModal', $booking->id)->call('confirmFinish');
        $this->assertSame('booked', $booking->fresh()->status);

        $booking->update(['status' => 'active', 'check_in_time' => now()]);
        $this->manager()->call('openFinishModal', $booking->id)->call('confirmFinish');
        $this->assertSame('completed', $booking->fresh()->status);
    }
}
