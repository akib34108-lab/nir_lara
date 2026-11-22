<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    protected $fillable = [
        'project_id',
        'milestone_name',
        'percentage',
        'status',
        'completed_date',
        'created_by',
    ];
    public function project()
        {
            return $this->belongsTo(Projects::class, 'project_id');
        }
    public function createdBy()
        {
            return $this->belongsTo(User::class, 'created_by');
        }
}
