<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'material_code',
        'name',
        'category_id',
        'unit',
        'description',
        'minimum_stock_level',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'minimum_stock_level' => 'decimal:3',
            'status' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id');
    }

    public function inventories()
    {
        return $this->hasMany(MaterialInventory::class);
    }
}
