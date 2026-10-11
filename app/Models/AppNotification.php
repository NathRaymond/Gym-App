<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id', 'title', 'message', 'type',
        'action_url', 'data', 'is_read', 'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Send a notification to a user.
     * (Renamed from push() because Model::push() already exists.)
     */
    public static function send(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        ?string $url = null,
        array $data = []
    ): self {
        return static::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $url,
            'data' => $data,
        ]);
    }

    // ===== Scopes =====
    public function scopeUnread($q)
    {
        return $q->where('is_read', false);
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    // ===== Helpers =====
    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}