<?php

namespace Tests\Feature;

use App\Livewire\BookingPage;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Mahasiswa tidak boleh bisa melewati aturan booking dengan memanipulasi request Livewire.
 */
class BookingSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Cluster $cluster;
    private Inventory $pc;
    private User $student;
    private string $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cluster = Cluster::create(['name' => 'Lab A', 'type' => 'public', 'is_active' => true]);
        $this->pc = Inventory::create(['cluster_id' => $this->cluster->id, 'name' => 'PC-01', 'status' => 'available']);
        $this->student = User::create([
            'name' => 'Mahasiswa',
            'nomor_induk' => '210710001',
            'email' => 'mhs@example.test',
            'password' => 'password',
        ]);
        $this->student->roles()->attach(Role::create(['role' => 'student']));
        $this->date = Carbon::tomorrow()->toDateString();
    }

    private function fillTimes($component)
    {
        return $component
            ->set('start_hour', '09')
            ->set('start_minute', '00')
            ->set('end_hour', '10')
            ->set('end_minute', '00');
    }

    public function test_student_cannot_book_full_room_without_selecting_a_pc(): void
    {
        $component = Livewire::actingAs($this->student)
            ->test(BookingPage::class)
            ->set('filterDate', $this->date);

        $this->fillTimes($component)->call('createBooking');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_selected_inventory_cannot_be_changed_from_the_browser(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::actingAs($this->student)
            ->test(BookingPage::class)
            ->set('selectedInventoryId', null);
    }

    public function test_pc_in_maintenance_cannot_be_booked_even_after_it_was_selected(): void
    {
        $component = Livewire::actingAs($this->student)
            ->test(BookingPage::class)
            ->set('filterDate', $this->date)
            ->call('clickSeat', $this->pc->id)
            ->assertSet('showBookModal', true);

        $this->pc->update(['status' => 'maintenance']);

        $this->fillTimes($component)->call('createBooking');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_pc_from_another_cluster_cannot_be_selected(): void
    {
        $otherCluster = Cluster::create(['name' => 'Lab B', 'type' => 'public', 'is_active' => true]);
        $otherPc = Inventory::create(['cluster_id' => $otherCluster->id, 'name' => 'PC-B1', 'status' => 'available']);

        Livewire::actingAs($this->student)
            ->test(BookingPage::class, ['clusterId' => $this->cluster->id])
            ->call('clickSeat', $otherPc->id)
            ->assertSet('showBookModal', false)
            ->assertSet('selectedInventoryId', null);
    }

    public function test_student_cannot_switch_to_a_private_or_inactive_cluster(): void
    {
        $private = Cluster::create(['name' => 'Lab TA', 'type' => 'private', 'is_active' => true]);
        $inactive = Cluster::create(['name' => 'Lab Lama', 'type' => 'public', 'is_active' => false]);

        Livewire::actingAs($this->student)
            ->test(BookingPage::class, ['clusterId' => $this->cluster->id])
            ->set('selectedClusterId', $private->id)
            ->assertSet('selectedClusterId', $this->cluster->id)
            ->set('selectedClusterId', $inactive->id)
            ->assertSet('selectedClusterId', $this->cluster->id);
    }

    public function test_check_in_is_only_possible_by_scanning_the_qr(): void
    {
        $this->assertFalse(method_exists(BookingPage::class, 'processCheckIn'));
    }
}
