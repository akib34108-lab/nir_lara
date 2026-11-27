<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FloorPlan extends Model
{
    use SoftDeletes;

    public const STORAGE_DISK = 'local';

    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    protected $fillable = [
        'building_id',
        'floor_id',
        'title',
        'description',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function fileTypeIcon(): string
    {
        return $this->mime_type === 'application/pdf' ? 'ti-file' : 'ti-image';
    }

    public function fileTypeLabel(): string
    {
        return $this->mime_type === 'application/pdf' ? 'PDF' : 'Image';
    }

    public function formattedFileSize(): string
    {
        if ($this->file_size >= 1024 * 1024) {
            return number_format($this->file_size / (1024 * 1024), 2).' MB';
        }

        return number_format($this->file_size / 1024, 1).' KB';
    }
}
