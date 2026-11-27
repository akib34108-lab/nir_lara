<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use SoftDeletes;

    public const BUILDING_TYPES = [
        'Residential',
        'Commercial',
        'Mixed Use',
        'Industrial',
        'Other',
    ];

    public const STRUCTURE_TYPES = [
        'RCC Frame',
        'Steel Frame',
        'Load Bearing',
        'Pre-Engineered',
        'Other',
    ];

    public const STATUS_LABELS = [
        'planning' => 'Planning',
        'under_construction' => 'Under Construction',
        'active' => 'Active',
        'on_hold' => 'On Hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_BADGE_COLORS = [
        'planning' => 'info',
        'under_construction' => 'warning',
        'active' => 'success',
        'on_hold' => 'secondary',
        'completed' => 'primary',
        'cancelled' => 'danger',
    ];

    protected $fillable = [
        'site_id',
        'name',
        'code',
        'building_type',
        'structure_type',
        'total_floors',
        'total_flats',
        'basement_count',
        'elevator_count',
        'parking_capacity',
        'status',
        'description',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Sites::class, 'site_id');
    }

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class)->orderBy('floor_number');
    }

    public function floorPlans(): HasMany
    {
        return $this->hasMany(FloorPlan::class)->latest();
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'building_amenity')
            ->withPivot('quantity', 'notes')
            ->withTimestamps()
            ->orderBy('category')
            ->orderBy('name');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status ?? ''));
    }

    public function statusBadgeColor(): string
    {
        return self::STATUS_BADGE_COLORS[$this->status] ?? 'secondary';
    }
}
