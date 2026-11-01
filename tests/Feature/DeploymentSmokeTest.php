<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Membuka setiap halaman dengan data contoh dan memastikan tidak ada error 500.
 */
class DeploymentSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;
    private Cluster $cluster;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->makeUser('admin', 'admin-001');
        $this->student = $this->makeUser('student', '210710001');
        $this->makeUser('lecturer', 'dosen-001');

        $this->cluster = Cluster::create(['name' => 'Lab A', 'type' => 'public', 'is_active' => true]);
        Cluster::create(['name' => 'Lab TA', 'type' => 'private', 'is_active' => true, 'requires_ta' => true]);

        $pc = Inventory::create(['cluster_id' => $this->cluster->id, 'name' => 'PC-01', 'status' => 'available', 'is_bookable' => true]);
        Inventory::create(['cluster_id' => $this->cluster->id, 'name' => 'PC-02', 'status' => 'maintenance']);

        Booking::create([
            'user_id' => $this->student->id,
            'cluster_id' => $this->cluster->id,
            'inventory_id' => $pc->id,
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addHour(),
            'check_in_time' => now()->subMinutes(5),
            'status' => 'active',
        ]);
        Booking::create([
            'user_id' => $this->student->id,
            'cluster_id' => $this->cluster->id,
            'inventory_id' => null,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHours(2),
            'status' => 'booked',
            'purpose' => 'Praktikum',
        ]);
    }

    private function makeUser(string $role, string $nomorInduk): User
    {
        $user = User::create([
            'name' => ucfirst($role) . ' User',
            'nomor_induk' => $nomorInduk,
            'email' => $nomorInduk . '@example.test',
            'password' => 'password',
        ]);
        $user->roles()->attach(Role::firstOrCreate(['role' => $role]));

        return $user;
    }

    public function test_guest_pages_render(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/login')->assertOk();
        $this->get('/display')->assertOk();
        $this->get('/up')->assertOk();
        $this->get('/booking')->assertRedirect(route('login'));
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_student_pages_render(): void
    {
        $this->actingAs($this->student);

        $this->get('/dashboard')->assertRedirect(route('booking.index'));
        $this->get('/booking')->assertOk();
        $this->get('/booking/' . $this->cluster->id)->assertOk();
        $this->get('/qr-scanner')->assertOk();
        $this->get('/profile')->assertOk();
        $this->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->admin);

        foreach ([
            '/admin/dashboard',
            '/admin/inventory',
            '/admin/clusters',
            '/admin/inventaris',
            '/admin/manajemen-user',
            '/admin/booking',
            '/admin/booking/history',
            '/admin/booking/' . $this->cluster->id,
            '/profile',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_https_app_url_does_not_force_https_on_local_access(): void
    {
        config(['app.url' => 'https://smartlab.kampus.ac.id']);

        $this->get('http://127.0.0.1:8000/')->assertRedirect('http://127.0.0.1:8000/login');
        $this->get('http://smartlab.kampus.ac.id/')->assertRedirect('https://smartlab.kampus.ac.id/login');
    }

    public function test_pc_status_api_responds(): void
    {
        $this->getJson('/api/pc-status/1')->assertOk()->assertJsonPath('command', 'UNLOCK');
        $this->getJson('/api/pc-status/999')->assertNotFound()->assertJsonPath('command', 'IGNORE');
    }

    public function test_booking_status_command_runs(): void
    {
        $this->artisan('booking:update-status')->assertSuccessful();
    }
}
