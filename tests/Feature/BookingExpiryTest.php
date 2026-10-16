<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard as AdminDashboard;
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

class BookingExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_unchecked_in_booking_is_cancelled_after_cluster_grace_period(): void
    {
        [$cluster, $inventory, $user, $now] = $this->createScenario();
        $lateBooking = $this->createBooking($cluster, $inventory, $user, $now->copy()->subMinutes(15));
        $withinGraceBooking = $this->createBooking($cluster, $inventory, $user, $now->copy()->subMinutes(14));
        $checkedInBooking = $this->createBooking(
            $cluster,
            $inventory,
            $user,
            $now->copy()->subMinutes(30),
            'active',
            $now->copy()->subMinutes(29),
        );

        $cancelledCount = app(BookingService::class)->expireUnattendedBookings($now);

        $this->assertSame(1, $cancelledCount);
        $this->assertSame('cancelled', $lateBooking->fresh()->status);
        $this->assertSame('Terlambat Check-in', $lateBooking->fresh()->cancel_reason);
        $this->assertSame('booked', $withinGraceBooking->fresh()->status);
        $this->assertSame('active', $checkedInBooking->fresh()->status);
    }

    public function test_user_booking_list_expires_no_show_and_releases_the_unit(): void
    {
        [$cluster, $inventory, $user, $now] = $this->createScenario();
        $booking = $this->createBooking($cluster, $inventory, $user, $now->copy()->subMinutes(16));

        Livewire::actingAs($user)
            ->test(BookingPage::class)
            ->assertDontSee('Terbooking')
            ->call('clickSeat', $inventory->id)
            ->assertSet('showBookModal', true);

        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_admin_recent_activity_shows_cancelled_no_show_booking(): void
    {
        [$cluster, $inventory, $user, $now] = $this->createScenario();
        $booking = $this->createBooking($cluster, $inventory, $user, $now->copy()->subMinutes(16));

        Livewire::actingAs($user)
            ->test(AdminDashboard::class)
            ->assertSee('Cancelled');

        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_unchecked_in_booking_cannot_be_finished_manually(): void
    {
        [$cluster, $inventory, $user, $now] = $this->createScenario();
        $booking = $this->createBooking($cluster, $inventory, $user, $now->copy()->subMinutes(5));

        Livewire::actingAs($user)
            ->test(BookingPage::class)
            ->call('clickSeat', $inventory->id)
            ->assertSet('showFinishModal', false)
            ->assertSet('showCancelModal', false)
            ->set('selectedBookingId', $booking->id)
            ->call('finishBooking')
            ->assertSet('showFinishModal', false);

        $this->assertSame('booked', $booking->fresh()->status);
        $this->assertNull($booking->fresh()->check_in_time);
    }

    public function test_expired_checked_in_session_completes_and_unit_becomes_available(): void
    {
        [$cluster, $inventory, $user, $now] = $this->createScenario();
        $booking = $this->createBooking(
            $cluster,
            $inventory,
            $user,
            $now->copy()->subMinutes(61),
            'active',
            $now->copy()->subMinutes(60),
        );

        Livewire::actingAs($user)
            ->test(BookingPage::class)
            ->assertSee('Tersedia');

        $this->assertSame('completed', $booking->fresh()->status);
    }

    public function test_admin_recent_activity_shows_completed_after_session_end(): void
    {
        [$cluster, $inventory, $user, $now] = $this->createScenario();
        $booking = $this->createBooking(
            $cluster,
            $inventory,
            $user,
            $now->copy()->subMinutes(61),
            'active',
            $now->copy()->subMinutes(60),
        );

        Livewire::actingAs($user)
            ->test(AdminDashboard::class)
            ->assertSee('Completed');

        $this->assertSame('completed', $booking->fresh()->status);
    }

    public function test_admin_recent_activity_distinguishes_booked_waiting_and_active(): void
    {
        [$cluster, $inventory, $user, $now] = $this->createScenario();
        $this->createBooking($cluster, $inventory, $user, $now->copy()->addMinutes(20));
        $this->createBooking($cluster, $inventory, $user, $now->copy()->subMinutes(2));
        $this->createBooking(
            $cluster,
            $inventory,
            $user,
            $now->copy()->subMinutes(5),
            'active',
            $now->copy()->subMinutes(4),
        );

        Livewire::actingAs($user)
            ->test(AdminDashboard::class)
            ->assertSee('Booked')
            ->assertSee('Menunggu Check-in')
            ->assertSee('Active');
    }

    private function createScenario(): array
    {
        $now = Carbon::now('Asia/Jakarta')->startOfMinute();

        $cluster = Cluster::create([
            'name' => 'Expiry Test Cluster',
            'type' => 'public',
            'is_active' => true,
            'delay_time' => 15,
            'min_duration_minutes' => 30,
            'max_duration_minutes' => 120,
        ]);
        $inventory = Inventory::create([
            'cluster_id' => $cluster->id,
            'name' => 'Expiry Test Unit',
            'status' => 'available',
        ]);
        $user = User::create([
            'name' => 'Expiry Test User',
            'nomor_induk' => 'expiry-test-001',
            'email' => 'expiry-test@example.test',
            'password' => Hash::make('password'),
        ]);

        return [$cluster, $inventory, $user, $now];
    }

    private function createBooking(Cluster $cluster, Inventory $inventory, User $user, Carbon $startTime, string $status = 'booked', ?Carbon $checkInTime = null): Booking
    {
        return Booking::create([
            'user_id' => $user->id,
            'cluster_id' => $cluster->id,
            'inventory_id' => $inventory->id,
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addHour(),
            'check_in_time' => $checkInTime,
            'status' => $status,
        ]);
    }
}