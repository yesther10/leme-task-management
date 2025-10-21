<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskFile extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFileFactory> */
    use HasFactory;

    protected $fillable = ['task_id', 'file_path', 'file_type'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
