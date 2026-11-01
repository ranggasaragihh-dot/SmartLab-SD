<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Inventory as InventoryPage;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_unit_name_is_a_validation_error_and_editing_keeps_own_name(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'nomor_induk' => 'admin-001',
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);
        $admin->roles()->attach(Role::create(['role' => 'admin']));
        $cluster = Cluster::create(['name' => 'Lab A']);
        $existing = Inventory::create(['cluster_id' => $cluster->id, 'name' => 'PC-01']);

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->call('create')
            ->set('form.cluster_id', $cluster->id)
            ->set('form.name', 'PC-01')
            ->call('store')
            ->assertHasErrors(['form.name' => 'unique']);

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->call('create')
            ->set('form.name', 'PC-02')
            ->call('store')
            ->assertHasErrors(['form.cluster_id' => 'required']);

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->call('edit', $existing->id)
            ->set('form.status', 'maintenance')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertSame('maintenance', $existing->fresh()->status);
        $this->assertDatabaseCount('inventory', 1);
    }
}
