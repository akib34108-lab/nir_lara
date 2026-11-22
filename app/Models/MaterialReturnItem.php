<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialReturnItem extends Model
{
    protected $fillable = ['material_return_id', 'material_id', 'quantity', 'accepted_quantity', 'rejected_quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'accepted_quantity' => 'decimal:3', 'rejected_quantity' => 'decimal:3'];
    }

    public function materialReturn()
    {
        return $this->belongsTo(MaterialReturn::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
