<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'password', 'avatar',
        'date_of_birth', 'gender', 'address', 'city', 'state', 'country',
        'emergency_contact_name', 'emergency_contact_phone',
        'status', 'membership_number', 'last_login_at', 'last_login_ip', 'is_verified',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'date_of_birth'     => 'date',
        'is_verified'       => 'boolean',
    ];

    // ============================================================
    // ACTIVITY LOG
    // ============================================================
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'email', 'phone'])
            ->logOnlyDirty();
    }

    // ============================================================
    // ACCESSORS
    // ============================================================
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=6366f1&color=fff';
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function activeMembership()
    {
        return $this->hasOne(Membership::class)
            ->where('status', 'active')
            ->latestOfMany();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function trainer()
    {
        return $this->hasOne(Trainer::class);
    }

    public function notifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    // ============================================================
    // HELPERS — WALLET
    // ============================================================
    /**
     * Get existing wallet OR create one. Safe to call multiple times.
     */
    public function getOrCreateWallet(): Wallet
    {
        // If relation already loaded, return it
        if ($this->relationLoaded('wallet') && $this->wallet) {
            return $this->wallet;
        }

        // Atomic firstOrCreate — prevents duplicate-key errors
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $this->id],
            ['balance' => 0, 'currency' => 'NGN', 'is_active' => true]
        );

        // Update the in-memory relation so future calls return the same instance
        $this->setRelation('wallet', $wallet);

        return $wallet;
    }

    // ============================================================
    // HELPERS — ROLES
    // ============================================================
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isFrontDesk(): bool
    {
        return $this->hasRole('front-desk');
    }

    public function isTrainer(): bool
    {
        return $this->hasRole('trainer');
    }

    public function isMember(): bool
    {
        return $this->hasRole('member');
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'manager', 'front-desk', 'trainer']);
    }

    // ============================================================
    // HELPERS — DASHBOARD REDIRECT
    // ============================================================
    /**
     * Where should this user land after login?
     */
    public function dashboardUrl(): string
    {
        return match (true) {
            $this->hasAnyRole(['super-admin', 'admin', 'manager']) => route('admin.dashboard'),
            $this->hasRole('front-desk') => route('frontdesk.dashboard'),
            $this->hasRole('trainer')    => route('trainer.dashboard'),
            default                       => route('member.dashboard'),
        };
    }

    // ============================================================
    // HELPERS — MEMBERSHIP
    // ============================================================
    public function hasActiveMembership(): bool
    {
        return $this->memberships()
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->exists();
    }
}