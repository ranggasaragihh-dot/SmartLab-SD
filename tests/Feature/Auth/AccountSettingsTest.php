<?php

namespace Tests\Feature\Auth;

use App\Livewire\Pages\AccountSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_their_password_with_the_current_password(): void
    {
        $user = User::create([
            'name' => 'Password Test',
            'nomor_induk' => 'password-test-001',
            'email' => 'password-test@example.test',
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user);

        Livewire::test(AccountSettings::class)
            ->set('current_password', 'old-password')
            ->set('password', 'new123')
            ->set('password_confirmation', 'new123')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSee('Password berhasil diperbarui.');

        $this->assertTrue(Hash::check('new123', $user->fresh()->password));
    }

    public function test_user_cannot_change_password_with_an_incorrect_current_password(): void
    {
        $user = User::create([
            'name' => 'Password Test',
            'nomor_induk' => 'password-test-002',
            'email' => 'password-test-2@example.test',
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user);

        Livewire::test(AccountSettings::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_new_password_must_differ_from_the_current_password(): void
    {
        $user = User::create([
            'name' => 'Password Test',
            'nomor_induk' => 'password-test-003',
            'email' => 'password-test-3@example.test',
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user);

        Livewire::test(AccountSettings::class)
            ->set('current_password', 'old-password')
            ->set('password', 'old-password')
            ->set('password_confirmation', 'old-password')
            ->call('updatePassword')
            ->assertHasErrors(['password' => 'different'])
            ->assertSee('Password baru harus berbeda dari password saat ini.');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}