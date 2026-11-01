<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nomor_induk',
        'ta',
        'is_banned'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user','user_id', 'role_id');
    }
    
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function hasRole($roleName)
    {
        return $this->roles()->where('role', $roleName)->exists();
    }

    public function roleBadges(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->roles->isEmpty()) {
                    return [];
                }

                return $this->roles->map(function ($role) {
                    $roleName = ($role->role instanceof \BackedEnum) ? $role->role->value : $role->role;
                    $roleName = strtolower($roleName);

                    $badgeClass = match($roleName) {
                        'admin'                => 'bg-purple-100 text-purple-800 border-purple-200',
                        'lecturer', 'dosen'    => 'bg-blue-100 text-blue-800 border-blue-200',
                        'student', 'mahasiswa' => 'bg-green-100 text-green-800 border-green-200',
                        default                => 'bg-gray-100 text-gray-800 border-gray-200',
                    };

                    return [
                        'label' => ucfirst($roleName),
                        'class' => $badgeClass
                    ];
                });
            }
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ta' => 'boolean',
            'is_banned' => 'boolean',
        ];
    }
}
