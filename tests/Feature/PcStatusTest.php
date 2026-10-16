<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PcStatusTest extends TestCase
{
    use RefreshDatabase;

    private Cluster $cluster;

    private Inventory $firstPc;

    private Inventory $secondPc;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-17 10:00:00', 'Asia/Jakarta'));

        $this->cluster = Cluster::create([
            'name' => 'PC Status Test Cluster',
            'type' => 'public',
            'is_active' => true,
            'delay_time' => 15,
            'min_duration_minutes' => 30,
            'max_duration_minutes' => 120,
        ]);
        $this->firstPc = $this->createPc('PC Status Test Unit A');
        $this->secondPc = $this->createPc('PC Status Test Unit B');
        $this->user = User::create([
            'name' => 'PC Status Test User',
            'nomor_induk' => 'pc-status-test-001',
            'email' => 'pc-status-test@example.test',
            'password' => Hash::make('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_pc_without_a_booking_is_locked_and_includes_server_time(): void
    {
        $this->getJson("/api/pc-status/{$this->firstPc->id}")
            ->assertOk()
            ->assertExactJson([
                'command' => 'LOCK',
                'user' => null,
                'end_time' => null,
                'server_time' => '2026-10-17 10:00:00',
            ]);
    }

    public function test_booked_but_not_checked_in_booking_does_not_unlock_pc(): void
    {
        $this->createBooking($this->firstPc->id, 'booked');

        $this->getJson("/api/pc-status/{$this->firstPc->id}")
            ->assertOk()
            ->assertJsonPath('command', 'LOCK');
    }

    public function test_active_booking_unlocks_pc_and_returns_user_and_end_time(): void
    {
        $this->createBooking($this->firstPc->id, 'active');

        $this->getJson("/api/pc-status/{$this->firstPc->id}")
            ->assertOk()
            ->assertExactJson([
                'command' => 'UNLOCK',
                'user' => $this->user->name,
                'end_time' => '2026-10-17 11:00:00',
                'server_time' => '2026-10-17 10:00:00',
            ]);
    }

    public function test_active_booking_that_has_ended_does_not_unlock_pc(): void
    {
        $this->createBooking($this->firstPc->id, 'active', endTime: '2026-10-17 09:59:59');

        $this->getJson("/api/pc-status/{$this->firstPc->id}")
            ->assertOk()
            ->assertJsonPath('command', 'LOCK');
    }

    public function test_booking_for_one_pc_does_not_unlock_another_pc(): void
    {
        $this->createBooking($this->firstPc->id, 'active');

        $this->getJson("/api/pc-status/{$this->secondPc->id}")
            ->assertOk()
            ->assertJsonPath('command', 'LOCK');
    }

    public function test_active_full_room_booking_unlocks_every_pc_in_its_cluster(): void
    {
        $this->createBooking(null, 'active');

        foreach ([$this->firstPc, $this->secondPc] as $pc) {
            $this->getJson("/api/pc-status/{$pc->id}")
                ->assertOk()
                ->assertJsonPath('command', 'UNLOCK')
                ->assertJsonPath('user', $this->user->name);
        }
    }

    public function test_unknown_pc_returns_not_found_and_ignore_command(): void
    {
        $this->getJson('/api/pc-status/999999')
            ->assertNotFound()
            ->assertExactJson([
                'command' => 'IGNORE',
                'message' => 'PC Not Found',
            ]);
    }

    private function createPc(string $name): Inventory
    {
        return Inventory::create([
            'cluster_id' => $this->cluster->id,
            'name' => $name,
            'status' => 'available',
        ]);
    }

    private function createBooking(?int $inventoryId, string $status, string $endTime = '2026-10-17 11:00:00'): Booking
    {
        return Booking::create([
            'user_id' => $this->user->id,
            'cluster_id' => $this->cluster->id,
            'inventory_id' => $inventoryId,
            'start_time' => '2026-10-17 09:00:00',
            'end_time' => $endTime,
            'status' => $status,
        ]);
    }
}
