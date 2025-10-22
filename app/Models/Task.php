<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Builder;
class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = ['title', 'description', 'due_date', 'priority', 'status', 'project_id', 'user_id'];

    protected $casts = [
        'due_date' => 'date',
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(TaskFile::class);
    }

        /**
         * Scope a query to only include tasks relevant to the given user (creator or project member).
         * * @param \Illuminate\Database\Eloquent\Builder $query
         * @param \App\Models\User $user
         * @return void
         */
        public function scopeRelevantToUser(Builder $query, User $user): void
        {
            // Obtener los IDs de los proyectos de los que el usuario es miembro
            $memberProjectIds = $user->projects()->pluck('projects.id');

            $query->where(function (Builder $q) use ($user, $memberProjectIds) {
                $q->where('user_id', $user->id) // Tareas que creó
                ->orWhereIn('project_id', $memberProjectIds); // Tareas en proyectos de los que es miembro
            });
        }
}
