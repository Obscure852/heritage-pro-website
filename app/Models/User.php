<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'firstname',
        'lastname',
        'username',
        'email',
        'password',
        'role',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'owner_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'owner_id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'owner_id');
    }

    public function requests()
    {
        return $this->hasMany(CrmRequest::class, 'owner_id');
    }

    public function crmPresence(): HasOne
    {
        return $this->hasOne(CrmUserPresence::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isRep(): bool
    {
        return $this->role === 'rep';
    }

    public function canAccessCrm(): bool
    {
        return $this->active && in_array($this->role, ['admin', 'manager', 'rep'], true);
    }

    public function canManageCrmUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canManageCrmSettings(): bool
    {
        return $this->isAdmin();
    }

    public function canManageOperationalRecords(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canAccessOwnedRecord(?int $ownerId): bool
    {
        return $this->canManageOperationalRecords() || $ownerId === $this->id;
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                if (is_string($value) && trim($value) !== '') {
                    return trim($value);
                }

                $fullName = trim(implode(' ', array_filter([
                    $attributes['firstname'] ?? null,
                    $attributes['lastname'] ?? null,
                ])));

                return $fullName !== '' ? $fullName : ($attributes['username'] ?? $attributes['email'] ?? 'User');
            },
            set: function (?string $value) {
                $value = trim((string) $value);

                if ($value === '') {
                    return [];
                }

                if (Schema::hasColumn($this->getTable(), 'name')) {
                    return ['name' => $value];
                }

                $parts = preg_split('/\s+/', $value, 2) ?: [];
                $firstname = $parts[0] ?? $value;
                $lastname = $parts[1] ?? 'CRM User';

                return [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'username' => $this->attributes['username'] ?? $this->attributes['email'] ?? strtolower($firstname . '.' . $lastname),
                ];
            }
        );
    }
}
