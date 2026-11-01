<?php

namespace Tests\Feature;

use App\Livewire\Admin\ClusterManager;
use App\Livewire\BookingPage;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Role;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ClusterTaAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_a_cluster_restricted_to_ta_students(): void
    {
        $admin = $this->createUser('admin', false);

        $component = Livewire::actingAs($admin)
            ->test(ClusterManager::class)
            ->call('create')
            ->set('form.name', 'Lab TA')
            ->set('form.requires_ta', true)
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clusters', [
            'name' => 'Lab TA',
            'requires_ta' => true,
        ]);

        $cluster = Cluster::where('name', 'Lab TA')->firstOrFail();
        $component->call('edit', $cluster->id)
            ->assertSet('form.requires_ta', true);
    }

    public function test_student_without_ta_cannot_book_a_restricted_cluster(): void
    {
        $student = $this->createUser('student', false);
        [$cluster, $inventory] = $this->createRestrictedCluster();

        Livewire::actingAs($student)
            ->test(BookingPage::class, ['clusterId' => $cluster->id])
            ->assertViewHas('allowedClusters', fn ($clusters) => $clusters->contains('id', $cluster->id))
            ->call('clickSeat', $inventory->id)
            ->assertSet('showBookModal', false)
            ->assertSee('Akses Cluster Terbatas');

        $this->expectException(\Exception::class);
        app(BookingService::class)->createBooking(
            $student->id,
            $inventory->id,
            $cluster->id,
            now()->addDay(),
            now()->addDay()->addHour(),
        );
    }

    public function test_lecturer_can_see_a_ta_restricted_cluster_but_cannot_book_it(): void
    {
        $lecturer = $this->createUser('lecturer', true);
        [$cluster, $inventory] = $this->createRestrictedCluster();

        Livewire::actingAs($lecturer)
            ->test(BookingPage::class, ['clusterId' => $cluster->id])
            ->assertViewHas('allowedClusters', fn ($clusters) => $clusters->contains('id', $cluster->id))
            ->assertSee('Akses Cluster Terbatas')
            ->call('clickSeat', $inventory->id)
            ->assertSet('showBookModal', false);
    }

    public function test_student_with_ta_can_book_a_restricted_cluster(): void
    {
        $student = $this->createUser('student', true);
        [$cluster, $inventory] = $this->createRestrictedCluster();

        Livewire::actingAs($student)
            ->test(BookingPage::class, ['clusterId' => $cluster->id])
            ->assertViewHas('allowedClusters', fn ($clusters) => $clusters->contains('id', $cluster->id))
            ->call('clickSeat', $inventory->id)
            ->assertSet('showBookModal', true);
    }

    private function createUser(string $roleName, bool $hasTa): User
    {
        $user = User::create([
            'name' => ucfirst($roleName),
            'nomor_induk' => $roleName . '-001',
            'email' => $roleName . '@example.test',
            'password' => Hash::make('password'),
            'ta' => $hasTa,
        ]);
        $role = Role::create(['role' => $roleName]);
        $user->roles()->attach($role);

        return $user;
    }

    private function createRestrictedCluster(): array
    {
        $cluster = Cluster::create([
            'name' => 'Restricted Lab',
            'type' => 'public',
            'requires_ta' => true,
            'is_active' => true,
        ]);
        $inventory = Inventory::create([
            'cluster_id' => $cluster->id,
            'name' => 'Restricted Unit',
            'status' => 'available',
        ]);

        return [$cluster, $inventory];
    }
}
