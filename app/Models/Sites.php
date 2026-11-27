<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sites extends Model
{
    use SoftDeletes;

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, 'site_id');
    }

    protected $fillable = [
        // Basic Information
        'project_code',
        'project_name',
        'project_status',
        'description',

        // Location Information
        'address',
        'area',
        'city',
        'district',
        'division',
        'country',
        'postal_code',
        'google_maps_location',

        // Land Information
        'land_area',
        'land_area_unit',
        'plot_number',
        'block',
        'road_number',
        'sector',
        'land_description',

        // Project Timeline
        'planned_start_date',
        'planned_completion_date',
        'actual_start_date',
        'actual_completion_date',

        // Share Information
        'total_shares',
        'per_share_value',

        // Budget Information
        'estimated_project_budget',
    ];
}
