<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleImage extends Model
{
    use \App\Models\Concerns\BelongsToCompany;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'title',
        'file_path',
        'is_featured',
        'sort_order',
        'image_type',
        'upload_source',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getImageTypeLabelAttribute(): string
    {
        return match ($this->image_type) {
            'front' => 'Araç Ön Resmi',
            'right_side' => 'Sağ Yan',
            'left_side' => 'Sol Yan',
            'rear' => 'Arka',
            'interior_1' => 'İç Resim 1',
            'interior_2' => 'İç Resim 2',
            'dashboard' => 'Göğüs',
            'other' => 'Diğer Resimler',
            default => $this->title ?: 'Araç Görseli',
        };
    }

    public function getUploadSourceLabelAttribute(): string
    {
        return match ($this->upload_source) {
            'driver_link' => 'Şoför Linki',
            'panel' => 'Panel',
            default => '-',
        };
    }
}