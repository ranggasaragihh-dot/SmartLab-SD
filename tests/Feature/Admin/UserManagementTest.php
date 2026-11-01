<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UserManagement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_npm_is_reported_as_a_validation_error(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'nomor_induk' => 'admin-001',
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);
        $adminRole = Role::create(['role' => 'admin']);
        $admin->roles()->attach($adminRole);

        User::create([
            'name' => 'Existing Student',
            'nomor_induk' => '210710001',
            'email' => 'student@example.test',
            'password' => 'password',
        ]);

        $studentRole = Role::create(['role' => 'student']);

        $this->actingAs($admin);

        Livewire::test(UserManagement::class)
            ->call('create')
            ->set('name', 'New Student')
            ->set('email', 'new-student@example.test')
            ->set('nomor_induk', '210710001')
            ->set('password', 'password')
            ->set('selectedRoles', [$studentRole->id])
            ->set('ta', 0)
            ->call('store')
            ->assertHasErrors(['nomor_induk' => 'unique'])
            ->assertSee('NPM ini sudah terdaftar.');

        $this->assertDatabaseCount('users', 2);
    }

    public function test_manual_user_creation_rejects_more_than_one_role(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'nomor_induk' => 'admin-002',
            'email' => 'admin-2@example.test',
            'password' => 'password',
        ]);
        $adminRole = Role::create(['role' => 'admin']);
        $admin->roles()->attach($adminRole);

        $studentRole = Role::create(['role' => 'student']);
        $lecturerRole = Role::create(['role' => 'lecturer']);

        $this->actingAs($admin);

        Livewire::test(UserManagement::class)
            ->call('create')
            ->set('name', 'New User')
            ->set('email', 'new-user@example.test')
            ->set('nomor_induk', '210710002')
            ->set('password', 'password')
            ->set('selectedRoles', [$studentRole->id, $lecturerRole->id])
            ->set('ta', 0)
            ->call('store')
            ->assertHasErrors(['selectedRoles' => 'size']);

        $this->assertDatabaseMissing('users', ['email' => 'new-user@example.test']);
    }

    public function test_manual_user_creation_stores_only_the_selected_role(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'nomor_induk' => 'admin-003',
            'email' => 'admin-3@example.test',
            'password' => 'password',
        ]);
        $adminRole = Role::create(['role' => 'admin']);
        $admin->roles()->attach($adminRole);

        $studentRole = Role::create(['role' => 'student']);
        $lecturerRole = Role::create(['role' => 'lecturer']);

        $this->actingAs($admin);

        Livewire::test(UserManagement::class)
            ->call('create')
            ->set('name', 'New User')
            ->set('email', 'new-user@example.test')
            ->set('nomor_induk', '210710003')
            ->set('password', 'password')
            ->call('selectRole', $studentRole->id)
            ->call('selectRole', $lecturerRole->id)
            ->set('ta', 0)
            ->call('store')
            ->assertHasNoErrors();

        $user = User::where('email', 'new-user@example.test')->firstOrFail();
        $this->assertSame([$lecturerRole->id], $user->roles()->pluck('roles.id')->all());
    }

    public function test_ta_status_is_only_shown_and_saved_for_student_users(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'nomor_induk' => 'admin-004',
            'email' => 'admin-4@example.test',
            'password' => 'password',
        ]);
        $adminRole = Role::create(['role' => 'admin']);
        $admin->roles()->attach($adminRole);

        $studentRole = Role::create(['role' => 'student']);
        $lecturerRole = Role::create(['role' => 'lecturer']);

        $this->actingAs($admin);

        Livewire::test(UserManagement::class)
            ->call('create')
            ->assertDontSee('Status Tugas Akhir')
            ->call('selectRole', $studentRole->id)
            ->assertSee('Status Tugas Akhir')
            ->set('name', 'New Student')
            ->set('email', 'new-student-ta@example.test')
            ->set('nomor_induk', '210710004')
            ->set('password', 'password')
            ->set('ta', 1)
            ->call('store')
            ->assertHasNoErrors();

        $student = User::where('email', 'new-student-ta@example.test')->firstOrFail();
        $this->assertTrue((bool) $student->ta);

        Livewire::test(UserManagement::class)
            ->call('create')
            ->call('selectRole', $lecturerRole->id)
            ->assertDontSee('Status Tugas Akhir')
            ->set('name', 'New Lecturer')
            ->set('email', 'new-lecturer@example.test')
            ->set('nomor_induk', 'lecturer-004')
            ->set('password', 'password')
            ->set('ta', 1)
            ->call('store')
            ->assertHasNoErrors();

        $lecturer = User::where('email', 'new-lecturer@example.test')->firstOrFail();
        $this->assertFalse((bool) $lecturer->ta);
    }

    public function test_ta_status_is_hidden_when_editing_non_student_users(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'nomor_induk' => 'admin-005',
            'email' => 'admin-5@example.test',
            'password' => 'password',
        ]);
        $adminRole = Role::create(['role' => 'admin']);
        $admin->roles()->attach($adminRole);

        $lecturerRole = Role::create(['role' => 'lecturer']);
        $lecturer = User::create([
            'name' => 'Lecturer User',
            'nomor_induk' => 'lecturer-005',
            'email' => 'lecturer-5@example.test',
            'password' => 'password',
            'ta' => true,
        ]);
        $lecturer->roles()->attach($lecturerRole);

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('edit', $lecturer->id)
            ->assertDontSee('Status Tugas Akhir')
            ->set('ta', 1)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertFalse((bool) $lecturer->fresh()->ta);
    }

    public function test_admin_can_edit_own_account_but_not_remove_own_admin_role(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'nomor_induk' => 'admin-006',
            'email' => 'admin-6@example.test',
            'password' => 'password',
        ]);
        $adminRole = Role::create(['role' => 'admin']);
        $studentRole = Role::create(['role' => 'student']);
        $admin->roles()->attach($adminRole);

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('edit', $admin->id)
            ->set('name', 'Admin Baru')
            ->call('update')
            ->assertHasNoErrors();
        $this->assertSame('Admin Baru', $admin->fresh()->name);

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('edit', $admin->id)
            ->call('selectRole', $studentRole->id)
            ->call('update');
        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_editing_user_with_duplicate_email_is_a_validation_error(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'nomor_induk' => 'admin-007',
            'email' => 'admin-7@example.test',
            'password' => 'password',
        ]);
        $admin->roles()->attach(Role::create(['role' => 'admin']));
        $student = User::create([
            'name' => 'Student',
            'nomor_induk' => '210710007',
            'email' => 'student-7@example.test',
            'password' => 'password',
        ]);
        $student->roles()->attach(Role::create(['role' => 'student']));

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('edit', $student->id)
            ->set('email', 'admin-7@example.test')
            ->call('update')
            ->assertHasErrors(['email' => 'unique']);
    }
}
