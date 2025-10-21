<?php

namespace App\Enums;

enum TaskStatus: int
{
    case Pending = 1;
    case InProgress = 2;
    case Completed = 3;

    // Puedes agregar métodos helpers si quieres, por ejemplo para mostrar etiquetas
    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pendiente',
            self::InProgress => 'En progreso',
            self::Completed => 'Completada',
        };
    }
}
