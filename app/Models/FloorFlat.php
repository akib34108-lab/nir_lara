<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FloorFlat extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'floor_id', 'flat_number', 'name', 'flat_type', 'area', 'area_unit',
        'bedroom_count', 'master_bedroom_count', 'bathroom_count', 'balcony_count',
        'kitchen_count', 'living_room_count', 'dining_room_count',
        'description', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'area' => 'decimal:2',
            'bedroom_count' => 'integer',
            'master_bedroom_count' => 'integer',
            'bathroom_count' => 'integer',
            'balcony_count' => 'integer',
            'kitchen_count' => 'integer',
            'living_room_count' => 'integer',
            'dining_room_count' => 'integer',
        ];
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(FloorFlatRoom::class, 'flat_id')->orderBy('id');
    }
}
