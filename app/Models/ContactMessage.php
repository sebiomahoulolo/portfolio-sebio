<?php

namespace App\Models;

use App\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    public const TYPES = [
        'contact' => 'Prise de contact',
        'quote' => 'Demande de devis',
    ];

    protected $attributes = ['status' => 'new', 'type' => 'contact'];

    protected $fillable = [
        'type', 'name', 'email', 'phone', 'company', 'subject', 'message', 'budget',
        'ip_hash', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'status' => MessageStatus::class,
            'read_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function markAsRead(): void
    {
        if ($this->status === MessageStatus::New) {
            $this->forceFill(['status' => MessageStatus::Read, 'read_at' => now()])->save();
        }
    }
}
