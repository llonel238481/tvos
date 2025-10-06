<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'role',
    ];

    public function employee()
    {
        return $this->hasOne(Employees::class);
    }

     public function faculty()
    {
        return $this->hasOne(Faculty::class);
    }

    public function ceo()
    {
        return $this->hasOne(CEO::class);
    }

     // Helper to get signature based on role
    public function signature()
    {
        return match($this->role) {
            'Supervisor', 'Employee' => $this->faculty?->signature,
            'CEO' => $this->ceo?->signature,
            default => null,
        };
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }
}
