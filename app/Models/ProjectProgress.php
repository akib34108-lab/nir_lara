<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectProgress extends Model
{
    protected $table = 'project_progress';
    protected $fillable = [
        'project_id',
        'progress_date',
        'progress_percentage',
        'remarks',
        'created_by',
    ];

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}