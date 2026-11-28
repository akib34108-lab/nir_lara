<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FloorFlatRoom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'flat_id', 'name', 'room_type', 'length', 'width', 'area', 'area_unit',
        'floor_level', 'attached_bathroom', 'door_count', 'window_count',
        'flooring_type', 'ceiling_type', 'wall_finish', 'ac_provision',
        'electrical_provision', 'plumbing_provision', 'ventilation_details',
        'description', 'notes', 'properties',
    ];

    protected function casts(): array
    {
        return [
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'area' => 'decimal:2',
            'attached_bathroom' => 'boolean',
            'door_count' => 'integer',
            'window_count' => 'integer',
            'properties' => 'array',
        ];
    }

    public function flat(): BelongsTo
    {
        return $this->belongsTo(FloorFlat::class, 'flat_id');
    }
}
