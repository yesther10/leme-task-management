<?php

namespace App\Enums;

enum TaskPriority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;

    public function label(): string
    {
        return match($this) {
            self::Low => 'Baja',
            self::Medium => 'Media',
            self::High => 'Alta',
        };
    }
}
