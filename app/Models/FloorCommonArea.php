<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FloorCommonArea extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'floor_id', 'name', 'area_type', 'length', 'width', 'area',
        'area_unit', 'description', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'area' => 'decimal:2',
        ];
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }
}
