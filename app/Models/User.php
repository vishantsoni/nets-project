<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitute;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, ScopesToInstitute;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
        'institute_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'student' => $this->isStudent(),
            'admin' => $this->canAccessAdminPanel(),
            default => false,
        };
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->isAdmin() || $this->isInstitute() || $this->isTeacher();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isPlatformAdmin(): bool
    {
        return $this->isAdmin() || $this->isSuperAdmin();
    }

    public function hasInstitute(): bool
    {
        return filled($this->institute_id);
    }

    public function isInstitute(): bool
    {
        return $this->role === 'institute';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}
