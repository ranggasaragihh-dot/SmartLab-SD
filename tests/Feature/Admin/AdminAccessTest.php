<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_livewire_update_on_admin_page_is_rejected_after_admin_role_is_removed(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::create(['role' => 'admin']);
        $admin->roles()->attach($adminRole);

        $html = $this->actingAs($admin)->get('/admin/manajemen-user')->assertOk()->getContent();
        preg_match('/wire:snapshot="([^"]+)"/', $html, $snapshot);
        $this->assertNotEmpty($snapshot, 'Snapshot Livewire tidak ditemukan.');

        $admin->roles()->detach();

        $response = $this->actingAs($admin->fresh())->postJson('/livewire/update', [
            '_token' => csrf_token(),
            'components' => [[
                'snapshot' => html_entity_decode($snapshot[1]),
                'updates' => [],
                'calls' => [['path' => '', 'method' => 'create', 'params' => []]],
            ]],
        ], ['X-Livewire' => 'true']);

        $this->assertNotSame(200, $response->getStatusCode());
    }
}
