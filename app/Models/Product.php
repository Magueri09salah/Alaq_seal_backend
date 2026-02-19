<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'code',
        'name',
        'description',
        'subcategory',
        'category',
        'price_min',
        'price_max',
        'price_unit',
        'warranty_years',
        'score_technical',
        'score_durability',
        'score_maintenance',
        'norme',
        'devis_text',
        'is_active',
        'display_order',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function cases()
    {
        return $this->hasMany(ProductCase::class)->orderBy('display_order');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
    public function scopeOrdered($q)
    {
        return $q->orderBy('display_order');
    }
    public function scopeSubcategory($q, $subcategory)
    {
        return $q->where('subcategory', $subcategory);
    }
}
