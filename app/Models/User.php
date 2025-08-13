<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    public function employee()
    {
        return $this->hasOne(\App\Models\Employee::class, 'user_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    'login_code',
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

    /**
     * Restringir acceso a paneles de Filament por rol, según documentación.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Acceso al panel de meseros sólo para rol 'waiter'
        if ($panel->getId() === 'waiter') {
            return $this->hasRole('waiter');
        }

        // Acceso al panel admin por defecto: cualquier usuario autenticado
        // Si se desea más restricción, ajustar aquí (e.g., roles específicos)
        if ($panel->getId() === 'admin') {
            return auth()->check();
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     *
     * @param string $permission
     * @return bool
     */
    public function hasCustomPermission(string $permission): bool
    {
        // Si el usuario es super_admin, siempre tiene permiso
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Verificar si el usuario tiene el permiso
        return $this->hasPermissionTo($permission);
    }
}
