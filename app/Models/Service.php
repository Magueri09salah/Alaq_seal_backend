<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'base_price_per_m2',
        'min_price',
        'icon',
        'image_url',
        'order_display',
        'is_active',
    ];

    protected $casts = [
        'base_price_per_m2' => 'decimal:2',
        'min_price' => 'decimal:2',
        'is_active' => 'boolean',
        'order_display' => 'integer',
    ];

    // Relationships
    public function options()
    {
        return $this->hasMany(ServiceOption::class);
    }

    public function devisItems()
    {
        return $this->hasMany(DevisItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_display', 'asc');
    }
}