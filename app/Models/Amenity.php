<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    public const CATEGORIES = [
        'Safety' => 'Safety',
        'Utilities' => 'Utilities',
        'Recreation' => 'Recreation',
        'Parking' => 'Parking',
        'Services' => 'Services',
        'Other' => 'Other',
    ];

    public const STATUS_LABELS = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public const STATUS_BADGE_COLORS = [
        'active' => 'success',
        'inactive' => 'secondary',
    ];

    protected $fillable = [
        'name',
        'category',
        'description',
        'icon',
        'status',
    ];

    public function buildings(): BelongsToMany
    {
        return $this->belongsToMany(Building::class, 'building_amenity')
            ->withPivot('quantity', 'notes')
            ->withTimestamps();
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status ?? '');
    }

    public function statusBadgeColor(): string
    {
        return self::STATUS_BADGE_COLORS[$this->status] ?? 'secondary';
    }
}
