<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{
    use SoftDeletes;

    public const FLOOR_TYPES = [
        'basement' => 'Basement',
        'parking' => 'Parking',
        'ground' => 'Ground',
        'residential' => 'Residential',
        'commercial' => 'Commercial',
        'mixed_use' => 'Mixed Use',
        'rooftop' => 'Rooftop',
        'other' => 'Other',
    ];

    public const FLOOR_AREA_UNITS = [
        'sqft' => 'Square Feet (sqft)',
        'sqm' => 'Square Meters (sqm)',
    ];

    public const STATUS_LABELS = [
        'planned' => 'Planned',
        'under_construction' => 'Under Construction',
        'completed' => 'Completed',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public const STATUS_BADGE_COLORS = [
        'planned' => 'info',
        'under_construction' => 'warning',
        'completed' => 'primary',
        'active' => 'success',
        'inactive' => 'secondary',
    ];

    protected $fillable = [
        'building_id',
        'name',
        'floor_number',
        'floor_type',
        'floor_area',
        'floor_area_unit',
        'common_area',
        'description',
        'notes',
        'configuration',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
        ];
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function floorPlans(): HasMany
    {
        return $this->hasMany(FloorPlan::class)->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status ?? ''));
    }

    public function statusBadgeColor(): string
    {
        return self::STATUS_BADGE_COLORS[$this->status] ?? 'secondary';
    }

    public function floorTypeLabel(): string
    {
        return self::FLOOR_TYPES[$this->floor_type] ?? ucfirst(str_replace('_', ' ', $this->floor_type ?? ''));
    }

    public function floorAreaUnitLabel(): string
    {
        return self::FLOOR_AREA_UNITS[$this->floor_area_unit] ?? $this->floor_area_unit;
    }
}
