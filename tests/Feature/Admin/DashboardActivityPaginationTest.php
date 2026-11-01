<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Dashboard;
use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardActivityPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_activity_shows_ten_rows_and_next_page_at_the_bottom(): void
    {
        $cluster = Cluster::create([
            'name' => 'Dashboard Test Lab',
            'type' => 'public',
            'is_active' => true,
        ]);
        $inventory = Inventory::create([
            'cluster_id' => $cluster->id,
            'name' => 'Dashboard Test PC',
            'status' => 'available',
        ]);

        foreach (range(1, 12) as $number) {
            $user = User::create([
                'name' => "Activity User {$number}",
                'nomor_induk' => "activity-{$number}",
                'email' => "activity-{$number}@example.test",
                'password' => 'password',
            ]);

            $booking = Booking::create([
                'user_id' => $user->id,
                'cluster_id' => $cluster->id,
                'inventory_id' => $inventory->id,
                'start_time' => now()->addDays($number),
                'end_time' => now()->addDays($number)->addHour(),
                'status' => 'booked',
            ]);

            DB::table('bookings')
                ->where('id', $booking->id)
                ->update(['created_at' => now()->addMinutes($number)]);
        }

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee('Activity User 12')
            ->assertSee('Activity User 3')
            ->assertDontSee('Activity User 2')
            ->assertSee('Menampilkan 1-10')
            ->assertSee('dari 12 aktivitas')
            ->assertSee('Next')
            ->call('nextPage')
            ->assertSee('Activity User 2')
            ->assertSee('Activity User 1')
            ->assertDontSee('Activity User 12')
            ->assertSee('Menampilkan 11-12')
            ->assertSee('dari 12 aktivitas');
    }
}
