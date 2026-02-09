<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceModifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'value',
        'is_percentage',
        'description',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_percentage' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function devisModifiers()
    {
        return $this->hasMany(DevisModifier::class, 'modifier_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSurcharges($query)
    {
        return $query->where('type', 'surcharge');
    }

    public function scopeDiscounts($query)
    {
        return $query->where('type', 'discount');
    }
}