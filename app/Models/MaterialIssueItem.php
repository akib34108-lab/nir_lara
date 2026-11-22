<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialIssueItem extends Model
{
    protected $fillable = ['material_issue_id', 'material_id', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3'];
    }

    public function issue()
    {
        return $this->belongsTo(MaterialIssue::class, 'material_issue_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
