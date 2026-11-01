<?php

namespace Tests\Feature;

use App\Livewire\PublicDisplay;
use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_without_check_in_does_not_mark_the_unit_as_occupied(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $cluster = Cluster::create(['name' => 'Cluster Test']);
        $inventory = Inventory::create([
            'cluster_id' => $cluster->id,
            'name' => 'PC-01',
            'status' => 'available',
        ]);
        $user = User::create([
            'name' => 'Mahasiswa Booking',
            'nomor_induk' => '210710001',
            'email' => 'booking@example.test',
            'password' => 'password',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'cluster_id' => $cluster->id,
            'inventory_id' => $inventory->id,
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addHour(),
            'status' => 'booked',
        ]);

        Livewire::test(PublicDisplay::class)
            ->assertSee('READY')
            ->assertSee('Available')
            ->assertDontSee('DIPAKAI')
            ->assertDontSee($user->name);
    }

    public function test_checked_in_booking_shows_user_and_live_countdown(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $cluster = Cluster::create(['name' => 'Cluster Test']);
        $inventory = Inventory::create([
            'cluster_id' => $cluster->id,
            'name' => 'PC-01',
            'status' => 'available',
        ]);
        $user = User::create([
            'name' => 'Mahasiswa Pemakai',
            'nomor_induk' => '210710002',
            'email' => 'occupant@example.test',
            'password' => 'password',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'cluster_id' => $cluster->id,
            'inventory_id' => $inventory->id,
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addHour(),
            'check_in_time' => now()->subMinutes(5),
            'status' => 'active',
        ]);

        Livewire::test(PublicDisplay::class)
            ->assertSee('DIPAKAI')
            ->assertSee('Mahasiswa Pemakai')
            ->assertSee('01:00:00')
            ->assertSee('bookingCountdown')
            ->assertSee((now()->addHour()->getTimestamp() * 1000));
    }
}
