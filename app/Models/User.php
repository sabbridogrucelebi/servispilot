<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Concerns\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'company_id',
        'customer_id',
        'name',
        'username',
        'email',
        'password',
        'role',
        'user_type',
        'is_active',
        'is_super_admin',
        'permissions_updated_at',
        'profile_photo',
        'expo_push_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'      => 'datetime',
        'permissions_updated_at' => 'datetime',
        'is_active'              => 'boolean',
        'is_super_admin'         => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')->withTimestamps();
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Chat\Conversation::class, 'conversation_user')
            ->withPivot('last_read_message_id', 'deleted_at')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE HELPERS
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === 'company_admin';
    }

    public function isOperation(): bool
    {
        return $this->role === 'operation';
    }

    public function isAccounting(): bool
    {
        return $this->role === 'accounting';
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    public function isCustomerPortal(): bool
    {
        return $this->user_type === 'customer_portal';
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeSameCompany(Builder $query): Builder
    {
        if (auth()->check() && auth()->user()->company_id) {
            return $query->where('company_id', auth()->user()->company_id);
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | PERMISSION CHECK
    |--------------------------------------------------------------------------
    */

    public function hasPermission(string $permissionKey): bool
    {
        // Super admin her şeye erişebilir
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Firma yöneticisi (company_admin) kendi firması için tam yetkilidir.
        // Bu sayede yeni eklenen granüler yetkiler (vehicles.create/edit/delete vb.)
        // için ayrıca atama yapılmasına gerek kalmaz ve admin hiçbir zaman
        // kendi panelinde "yetkisiz" duruma düşmez.
        if ($this->isCompanyAdmin()) {
            return true;
        }

        if ($this->isCustomerPortal()) {
            return false;
        }

        if (!$this->relationLoaded('permissions')) {
            $this->load('permissions');
        }

        return $this->permissions->contains('key', $permissionKey);
    }

    /*
    |--------------------------------------------------------------------------
    | MODULE CHECK
    |--------------------------------------------------------------------------
    */

    /**
     * Kullanıcının firmasının ilgili modüle erişimi var mı?
     */
    public function canAccessModule(string $moduleKey): bool
    {
        // Super admin tüm modüllere erişebilir
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Firma yoksa erişim yok
        if (!$this->company) {
            return false;
        }

        return $this->company->hasModule($moduleKey);
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD AUTO HASH (ÖNEMLİ)
    |--------------------------------------------------------------------------
    */

    public function setPasswordAttribute($value): void
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }
}