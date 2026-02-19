<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Devis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'devis_number',
        'project_name',
        'project_location',
        'notes',
        'service_id',
        'product_id',
        'subcategory',
        'product_case_id',
        'longueur',
        'largeur',
        'hauteur',
        'nombre_murs',
        'surface_area',
        'factor_height',
        'factor_condition',
        'factor_complexity',
        'factor_region',
        'base_price',
        'price_with_factors',
        'fixed_costs',
        'subtotal_ht',
        'tva_rate',
        'tva_amount',
        'total_ttc',
        'calculated_materials',
        'estimated_days',
        'preparation_days',
        'drying_days',
        'status',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'calculated_materials' => 'array',
        'submitted_at'         => 'datetime',
        'reviewed_at'          => 'datetime',
        'longueur'             => 'decimal:2',
        'largeur'              => 'decimal:2',
        'hauteur'              => 'decimal:2',
        'surface_area'         => 'decimal:2',
        'base_price'           => 'decimal:2',
        'price_with_factors'   => 'decimal:2',
        'subtotal_ht'          => 'decimal:2',
        'tva_amount'           => 'decimal:2',
        'total_ttc'            => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function productCase()
    {
        return $this->belongsTo(ProductCase::class, 'product_case_id');
    }

    public function submit()
    {
        $this->update(['status' => 'submitted', 'submitted_at' => now()]);
    }

    public function canEdit()
    {
        return in_array($this->status, ['draft', 'saved']);
    }
    public function canDelete()
    {
        return in_array($this->status, ['draft', 'saved']);
    }

    /**
     * Generate unique devis number — checks DB to ensure no duplicate
     */
    public static function generateNumber(int $userId): string
    {
        do {
            $number = 'DEV-' . $userId . '-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('devis_number', $number)->exists());

        return $number;
    }

    public function scopeByUser($q, $userId)
    {
        return $q->where('user_id', $userId);
    }
    public function scopeByStatus($q, $status)
    {
        return $q->where('status', $status);
    }
    public function scopeRecent($q)
    {
        return $q->orderBy('created_at', 'desc');
    }
}
