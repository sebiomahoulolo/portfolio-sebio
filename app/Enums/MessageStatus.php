<?php

namespace App\Enums;

/**
 * Statut de lecture d'un message, distinct du statut commercial du
 * prospect (LeadStatus) : un message peut être lu sans que la demande
 * ait été traitée.
 */
enum MessageStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nouveau',
            self::Read => 'Lu',
            self::Archived => 'Archivé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'orange',
            self::Read => 'zinc',
            self::Archived => 'amber',
        };
    }
}
