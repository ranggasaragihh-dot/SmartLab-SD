<?php

namespace Tests\Feature;

use App\Livewire\BookingPage;
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
 * Alur booking mahasiswa sesuai SKPL (FR_401, FR_402, FR_501) dan Buku Panduan bab 2.
 */
class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private Cluster $cluster;
    private Inventory $pc1;
    private Inventory $pc2;
    private User $student;
    private User $otherStudent;
    private string $date;

    protected function setUp(): void
    {
        parent::setUp();

        $studentRole = Role::create(['role' => 'student']);
        $this->cluster = Cluster::create(['name' => 'Lab A', 'type' => 'public', 'is_active' => true]);
        $this->pc1 = Inventory::create(['cluster_id' => $this->cluster->id, 'name' => 'PC-01']);
        $this->pc2 = Inventory::create(['cluster_id' => $this->cluster->id, 'name' => 'PC-02']);
        $this->student = $this->makeStudent('210710001', $studentRole);
        $this->otherStudent = $this->makeStudent('210710002', $studentRole);
        $this->date = Carbon::tomorrow()->toDateString();
    }

    private function makeStudent(string $npm, Role $role): User
    {
        $user = User::create(['name' => "Mhs {$npm}", 'nomor_induk' => $npm, 'email' => "{$npm}@example.test", 'password' => 'password']);
        $user->roles()->attach($role);

        return $user;
    }

    private function book(User $user, ?Inventory $pc, string|Carbon $start, string|Carbon $end, array $extra = []): Booking
    {
        return Booking::create(array_merge([
            'user_id' => $user->id,
            'cluster_id' => $this->cluster->id,
            'inventory_id' => $pc?->id,
            'start_time' => $start instanceof Carbon ? $start : "{$this->date} {$start}:00",
            'end_time' => $end instanceof Carbon ? $end : "{$this->date} {$end}:00",
            'status' => 'booked',
        ], $extra));
    }

    private function selectOptions(string $html, string $model): array
    {
        preg_match('/<select wire:model(?:\.live)?="' . $model . '"[^>]*>(.*?)<\/select>/s', $html, $select);
        preg_match_all('/<option value="([^"]+)"/', $select[1] ?? '', $options);

        return $options[1];
    }

    public function test_time_options_hide_slots_already_booked_on_the_same_pc(): void
    {
        $this->book($this->otherStudent, $this->pc1, '11:00', '12:00');

        $component = Livewire::actingAs($this->student)
            ->test(BookingPage::class)
            ->set('filterDate', $this->date)
            ->call('clickSeat', $this->pc1->id);

        $startHours = $this->selectOptions($component->html(), 'start_hour');
        $this->assertNotContains('11', $startHours);
        $this->assertContains('10', $startHours);
        $this->assertContains('12', $startHours);

        $component->set('start_hour', '10')->set('start_minute', '00');
        $this->assertSame(['10', '11'], $this->selectOptions($component->html(), 'end_hour'));

        $component->set('end_hour', '11');
        $this->assertSame(['00'], $this->selectOptions($component->html(), 'end_minute'));
    }

    public function test_student_cannot_hold_two_pcs_at_the_same_time(): void
    {
        $this->book($this->student, $this->pc1, '09:00', '10:00');

        Livewire::actingAs($this->student)
            ->test(BookingPage::class)
            ->set('filterDate', $this->date)
            ->call('clickSeat', $this->pc2->id)
            ->set('start_hour', '09')
            ->set('start_minute', '30')
            ->set('end_hour', '10')
            ->set('end_minute', '30')
            ->call('createBooking')
            ->assertHasErrors('start_time');

        $this->assertSame(1, Booking::where('user_id', $this->student->id)->count());
    }

    public function test_room_event_only_blocks_its_own_hours_and_cluster(): void
    {
        $this->book($this->otherStudent, null, '09:00', '12:00', ['purpose' => 'Praktikum SD']);
        $otherCluster = Cluster::create(['name' => 'Lab B', 'type' => 'public', 'is_active' => true]);
        $otherPc = Inventory::create(['cluster_id' => $otherCluster->id, 'name' => 'PC-B1']);

        $component = Livewire::actingAs($this->student)
            ->test(BookingPage::class, ['clusterId' => $this->cluster->id])
            ->set('filterDate', $this->date)
            ->assertSee('Praktikum SD')
            ->assertSee('09:00 - 12:00');

        $component->call('clickSeat', $this->pc1->id)
            ->set('start_hour', '13')->set('start_minute', '00')
            ->set('end_hour', '14')->set('end_minute', '00')
            ->call('createBooking')
            ->assertHasNoErrors();

        Livewire::actingAs($this->student)
            ->test(BookingPage::class, ['clusterId' => $this->cluster->id])
            ->set('filterDate', $this->date)
            ->call('clickSeat', $this->pc2->id)
            ->set('start_hour', '10')->set('start_minute', '00')
            ->set('end_hour', '11')->set('end_minute', '00')
            ->call('createBooking')
            ->assertHasErrors('start_time');

        Livewire::actingAs($this->otherStudent)
            ->test(BookingPage::class, ['clusterId' => $otherCluster->id])
            ->set('filterDate', $this->date)
            ->assertDontSee('Praktikum SD')
            ->call('clickSeat', $otherPc->id)
            ->assertSet('showBookModal', true);

        $this->assertDatabaseHas('bookings', ['user_id' => $this->student->id, 'inventory_id' => $this->pc1->id, 'start_time' => "{$this->date} 13:00:00"]);
        $this->assertDatabaseMissing('bookings', ['inventory_id' => $this->pc2->id]);
    }

    public function test_hidden_pc_is_not_listed_and_cannot_be_booked(): void
    {
        $this->pc2->update(['is_bookable' => false]);

        Livewire::actingAs($this->student)
            ->test(BookingPage::class)
            ->set('filterDate', $this->date)
            ->assertSee('PC-01')
            ->assertDontSee('PC-02')
            ->call('clickSeat', $this->pc2->id)
            ->assertSet('showBookModal', false);
    }

    public function test_student_can_cancel_a_late_booking_before_check_in(): void
    {
        $booking = $this->book(
            $this->student,
            $this->pc1,
            now()->subMinutes(5),
            now()->addMinutes(55),
        );

        Livewire::actingAs($this->student)
            ->test(BookingPage::class)
            ->call('clickSeat', $this->pc1->id)
            ->assertSet('showCancelModal', true)
            ->call('cancelBooking');

        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame('Dibatalkan oleh pengguna', $booking->fresh()->cancel_reason);
    }

    public function test_full_booking_journey_from_booking_to_check_in_to_finish(): void
    {
        $booking = $this->book(
            $this->student,
            $this->pc1,
            now()->subMinute(),
            now()->addHour(),
        );

        $this->getJson('/api/pc-status/' . $this->pc1->id)->assertJsonPath('command', 'LOCK');

        $this->actingAs($this->student)
            ->get(route('scan.qr', $this->pc1->qr_token))
            ->assertRedirect(route('booking.index'));
        $this->assertSame('active', $booking->fresh()->status);

        $this->getJson('/api/pc-status/' . $this->pc1->id)
            ->assertJsonPath('command', 'UNLOCK')
            ->assertJsonPath('user', $this->student->name);

        Livewire::actingAs($this->student)
            ->test(BookingPage::class)
            ->call('clickSeat', $this->pc1->id)
            ->assertSet('showFinishModal', true)
            ->call('finishBooking');

        $this->assertSame('completed', $booking->fresh()->status);
        $this->getJson('/api/pc-status/' . $this->pc1->id)->assertJsonPath('command', 'LOCK');
    }
}
