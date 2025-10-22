<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = ['title', 'description', 'start_date', 'due_date', 'user_id', 'file_path'];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];    

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Scope a query to only include projects where the user is the creator or a member.
     * * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user
     * @return void
     */
    public function scopeAccessibleByUser(Builder $query, User $user): void
    {
        $userId = $user->id;

        $query->where(function (Builder $q) use ($userId) {
            $q->where('user_id', $userId) // Proyectos que el usuario creó
            ->orWhereHas('members', function ($subQ) use ($userId) {
                $subQ->where('users.id', $userId);
            });
        });
    }
}
