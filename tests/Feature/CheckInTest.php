<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    public function test_matching_qr_check_in_activates_the_booking(): void
    {
        [$user, $inventory, $booking] = $this->createBooking();

        $response = $this->actingAs($user)->get(route('scan.qr', $inventory->qr_token));

        $response->assertRedirect(route('booking.index'));
        $response->assertSessionHas('success', 'Check-in berhasil. PC terbuka.');
        $this->assertSame('active', $booking->fresh()->status);
        $this->assertNotNull($booking->fresh()->check_in_time);
    }

    public function test_check_in_within_early_checkin_window_succeeds_and_reports_unlock_time(): void
    {
        [$user, $inventory, $booking] = $this->createBooking(startOffsetMinutes: 5);

        $this->actingAs($user)
            ->get(route('scan.qr', $inventory->qr_token))
            ->assertRedirect(route('booking.index'))
            ->assertSessionHas('success', 'Check-in berhasil. PC akan terbuka pukul ' . $booking->start_time->format('H:i') . '.');

        $this->assertSame('active', $booking->fresh()->status);
    }

    public function test_full_room_check_in_keeps_full_room_access_message(): void
    {
        [$user, $inventory, $booking] = $this->createBooking();
        $booking->update(['inventory_id' => null]);

        $this->actingAs($user)
            ->get(route('scan.qr', $inventory->qr_token))
            ->assertRedirect(route('booking.index'))
            ->assertSessionHas('success', 'Check-in berhasil. PC terbuka. (Akses Full Ruangan)');
    }

    public function test_authenticated_user_can_open_the_qr_scanner_page(): void
    {
        [$user] = $this->createBooking();

        $this->actingAs($user)
            ->get(route('booking.qr-scanner'))
            ->assertOk()
            ->assertSee('QR Check-in')
            ->assertSee('Mulai pemindaian');
    }

    public function test_qr_from_a_different_unit_is_rejected(): void
    {
        [$user, , $booking, $otherInventory] = $this->createBooking(withOtherInventory: true);

        $response = $this->actingAs($user)->get(route('scan.qr', $otherInventory->qr_token));

        $response->assertRedirect(route('booking.index'));
        $response->assertSessionHas('error');
        $this->assertSame('booked', $booking->fresh()->status);
    }

    public function test_qr_check_in_outside_early_checkin_window_is_rejected(): void
    {
        [$user, $inventory, $booking] = $this->createBooking(startOffsetMinutes: 11);

        $response = $this->actingAs($user)->get(route('scan.qr', $inventory->qr_token));

        $response->assertRedirect(route('booking.index'));
        $response->assertSessionHas('error');
        $this->assertSame('booked', $booking->fresh()->status);
    }

    public function test_booked_reservation_cannot_be_finished_through_booking_service(): void
    {
        [$user, , $booking] = $this->createBooking();

        $this->expectException(\Exception::class);

        app(BookingService::class)->finishBooking($booking->id, $user->id);
    }

    private function createBooking(int $startOffsetMinutes = -5, bool $withOtherInventory = false): array
    {
        $now = Carbon::now('Asia/Jakarta')->startOfMinute();
        $cluster = Cluster::create([
            'name' => 'QR Check-in Test Cluster',
            'type' => 'public',
            'is_active' => true,
            'delay_time' => 15,
            'min_duration_minutes' => 30,
            'max_duration_minutes' => 120,
        ]);
        $inventory = Inventory::create([
            'cluster_id' => $cluster->id,
            'name' => 'QR Check-in Test Unit',
            'status' => 'available',
        ]);
        $otherInventory = $withOtherInventory ? Inventory::create([
            'cluster_id' => $cluster->id,
            'name' => 'Wrong QR Test Unit',
            'status' => 'available',
        ]) : null;
        $user = User::create([
            'name' => 'QR Check-in Test User',
            'nomor_induk' => 'qr-checkin-test-001',
            'email' => 'qr-checkin-test@example.test',
            'password' => Hash::make('password'),
        ]);

        $startTime = $now->copy()->addMinutes($startOffsetMinutes);
        $booking = Booking::create([
            'user_id' => $user->id,
            'cluster_id' => $cluster->id,
            'inventory_id' => $inventory->id,
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addHour(),
            'status' => 'booked',
        ]);

        return [$user, $inventory, $booking, $otherInventory];
    }
}