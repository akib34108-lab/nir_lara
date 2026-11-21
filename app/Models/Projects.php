<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projects extends Model
{
    use SoftDeletes;
    public function progressHistory()
    {
        return $this->hasMany(ProjectProgress::class, 'project_id');
    }
    protected $fillable = [
    'project_code',
    'client_id',
    'project_name',
    'description',
    'project_manager_id',
    'start_date',
    'expected_end_date',
    'actual_end_date',
    'estimated_budget',
    'progress',
    'status',
    'site_address',
    'city',
];
}
