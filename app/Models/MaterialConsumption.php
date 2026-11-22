<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialConsumption extends Model
{
    protected $fillable = ['project_id', 'site', 'material_issue_id', 'material_id', 'warehouse_id', 'issued_quantity', 'estimated_quantity', 'consumed_quantity', 'wasted_quantity', 'remaining_quantity', 'consumption_date', 'notes', 'recorded_by'];

    protected function casts(): array
    {
        return [
            'issued_quantity' => 'decimal:3',
            'estimated_quantity' => 'decimal:3',
            'consumed_quantity' => 'decimal:3',
            'wasted_quantity' => 'decimal:3',
            'remaining_quantity' => 'decimal:3',
            'consumption_date' => 'date',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function issue()
    {
        return $this->belongsTo(MaterialIssue::class, 'material_issue_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
