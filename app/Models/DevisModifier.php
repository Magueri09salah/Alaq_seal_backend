<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevisModifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'devis_id',
        'modifier_id',
        'applied_value',
    ];

    protected $casts = [
        'applied_value' => 'decimal:2',
    ];

    // Relationships
    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

    public function modifier()
    {
        return $this->belongsTo(PriceModifier::class, 'modifier_id');
    }
}