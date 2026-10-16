<?php

namespace Tests\Feature;

use App\Livewire\BookingPage;
use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class BookingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_another_user_can_book_the_same_unit_in_a_different_time_slot(): void
    {
        [$cluster, $inventory, $firstUser, $secondUser, $bookingDate] = $this->createBookingScenario();

        Booking::create([
            'user_id' => $firstUser->id,
            'cluster_id' => $cluster->id,
            'inventory_id' => $inventory->id,
            'start_time' => "{$bookingDate} 09:00:00",
            'end_time' => "{$bookingDate} 10:00:00",
            'status' => 'booked',
        ]);

        $component = Livewire::actingAs($secondUser)
            ->test(BookingPage::class)
            ->set('filterDate', $bookingDate);

        $component
            ->assertSee('Terbooking')
            ->assertSeeHtml('wire:click="clickSeat(' . $inventory->id . ')"')
            ->call('clickSeat', $inventory->id)
            ->assertSet('showBookModal', true)
            ->set('start_hour', '11')
            ->set('start_minute', '00')
            ->set('end_hour', '12')
            ->set('end_minute', '00')
            ->call('createBooking')
            ->assertHasNoErrors()
            ->assertSet('showBookModal', false)
            ->assertSee('Milik Saya: 11:00 - 12:00');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $secondUser->id,
            'inventory_id' => $inventory->id,
            'start_time' => "{$bookingDate} 11:00:00",
            'end_time' => "{$bookingDate} 12:00:00",
            'status' => 'booked',
        ]);
    }

    public function test_another_user_cannot_book_an_overlapping_time_slot(): void
    {
        [$cluster, $inventory, $firstUser, $secondUser, $bookingDate] = $this->createBookingScenario();

        Booking::create([
            'user_id' => $firstUser->id,
            'cluster_id' => $cluster->id,
            'inventory_id' => $inventory->id,
            'start_time' => "{$bookingDate} 09:00:00",
            'end_time' => "{$bookingDate} 10:00:00",
            'status' => 'booked',
        ]);

        Livewire::actingAs($secondUser)
            ->test(BookingPage::class)
            ->set('filterDate', $bookingDate)
            ->call('clickSeat', $inventory->id)
            ->set('start_hour', '09')
            ->set('start_minute', '30')
            ->set('end_hour', '10')
            ->set('end_minute', '30')
            ->call('createBooking')
            ->assertHasErrors('start_time')
            ->assertSet('showBookModal', true);

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_booking_starting_when_another_booking_ends_is_available(): void
    {
        [$cluster, $inventory, $firstUser, , $bookingDate] = $this->createBookingScenario();
        $this->createBookingRecord($firstUser, $cluster, $inventory, "{$bookingDate} 10:00:00", "{$bookingDate} 12:00:00");

        $this->assertTrue(app(BookingService::class)->isSlotAvailable(
            $inventory->id,
            $cluster->id,
            "{$bookingDate} 12:00:00",
            "{$bookingDate} 14:00:00",
        ));
    }

    public function test_partially_overlapping_booking_is_unavailable(): void
    {
        [$cluster, $inventory, $firstUser, , $bookingDate] = $this->createBookingScenario();
        $this->createBookingRecord($firstUser, $cluster, $inventory, "{$bookingDate} 10:00:00", "{$bookingDate} 12:00:00");

        $this->assertFalse(app(BookingService::class)->isSlotAvailable(
            $inventory->id,
            $cluster->id,
            "{$bookingDate} 11:30:00",
            "{$bookingDate} 13:00:00",
        ));
    }

    public function test_full_room_booking_conflicts_with_pc_booking(): void
    {
        [$cluster, $inventory, $firstUser, , $bookingDate] = $this->createBookingScenario();
        $this->createBookingRecord($firstUser, $cluster, null, "{$bookingDate} 10:00:00", "{$bookingDate} 12:00:00");

        $this->assertFalse(app(BookingService::class)->isSlotAvailable(
            $inventory->id,
            $cluster->id,
            "{$bookingDate} 11:00:00",
            "{$bookingDate} 13:00:00",
        ));
    }

    public function test_pc_booking_conflicts_with_full_room_booking(): void
    {
        [$cluster, $inventory, $firstUser, , $bookingDate] = $this->createBookingScenario();
        $this->createBookingRecord($firstUser, $cluster, $inventory, "{$bookingDate} 10:00:00", "{$bookingDate} 12:00:00");

        $this->assertFalse(app(BookingService::class)->isSlotAvailable(
            null,
            $cluster->id,
            "{$bookingDate} 11:00:00",
            "{$bookingDate} 13:00:00",
        ));
    }

    public function test_cancelled_and_completed_bookings_do_not_block_a_slot(): void
    {
        [$cluster, $inventory, $firstUser, , $bookingDate] = $this->createBookingScenario();
        $this->createBookingRecord($firstUser, $cluster, $inventory, "{$bookingDate} 10:00:00", "{$bookingDate} 12:00:00", 'cancelled');
        $this->createBookingRecord($firstUser, $cluster, $inventory, "{$bookingDate} 10:00:00", "{$bookingDate} 12:00:00", 'completed');

        $this->assertTrue(app(BookingService::class)->isSlotAvailable(
            $inventory->id,
            $cluster->id,
            "{$bookingDate} 11:00:00",
            "{$bookingDate} 13:00:00",
        ));
    }

    public function test_booking_can_finish_after_midnight_within_the_duration_limit(): void
    {
        [$cluster, $inventory, , $user, $bookingDate] = $this->createBookingScenario();

        $component = Livewire::actingAs($user)
            ->test(BookingPage::class)
            ->set('filterDate', $bookingDate)
            ->call('clickSeat', $inventory->id)
            ->set('start_hour', '23')
            ->set('start_minute', '30')
            ->set('end_hour', '00')
            ->set('end_minute', '00');

        $this->assertFalse($component->instance()->isTimeOptionDisabled('end', '00', '00'));
        $this->assertTrue($component->instance()->isTimeOptionDisabled('end', '02', '00'));

        $component->call('createBooking')
            ->assertHasNoErrors()
            ->assertSet('showBookModal', false);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'inventory_id' => $inventory->id,
            'start_time' => "{$bookingDate} 23:30:00",
            'end_time' => Carbon::parse($bookingDate, 'Asia/Jakarta')->addDay()->setTime(0, 0)->format('Y-m-d H:i:s'),
            'status' => 'booked',
        ]);
    }

    private function createBookingScenario(): array
    {
        $bookingDate = Carbon::now('Asia/Jakarta')->addDay()->toDateString();
        $cluster = Cluster::create([
            'name' => 'Test Cluster',
            'type' => 'public',
            'is_active' => true,
            'delay_time' => 15,
            'min_duration_minutes' => 30,
            'max_duration_minutes' => 120,
        ]);
        $inventory = Inventory::create([
            'cluster_id' => $cluster->id,
            'name' => 'Test Unit',
            'status' => 'available',
        ]);
        $firstUser = User::create([
            'name' => 'First User',
            'nomor_induk' => 'booking-test-001',
            'email' => 'booking-test-1@example.test',
            'password' => Hash::make('password'),
        ]);
        $secondUser = User::create([
            'name' => 'Second User',
            'nomor_induk' => 'booking-test-002',
            'email' => 'booking-test-2@example.test',
            'password' => Hash::make('password'),
        ]);

        return [$cluster, $inventory, $firstUser, $secondUser, $bookingDate];
    }

    private function createBookingRecord(
        User $user,
        Cluster $cluster,
        ?Inventory $inventory,
        string $startTime,
        string $endTime,
        string $status = 'booked',
    ): Booking {
        return Booking::create([
            'user_id' => $user->id,
            'cluster_id' => $cluster->id,
            'inventory_id' => $inventory?->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
        ]);
    }
}