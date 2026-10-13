<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Propriétaire',
            self::Editor => 'Éditeur',
        };
    }
}
