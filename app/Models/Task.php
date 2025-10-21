<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;

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
}
