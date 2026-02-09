<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevisItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'devis_id',
        'service_id',
        'description',
        'surface_m2',
        'unit_price',
        'selected_options',
        'option_multiplier',
        'subtotal',
        'order_index',
    ];

    protected $casts = [
        'surface_m2' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'option_multiplier' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'selected_options' => 'array',
        'order_index' => 'integer',
    ];

    // Relationships
    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Helper Methods
    public function calculateSubtotal()
    {
        $this->subtotal = $this->surface_m2 * $this->unit_price * $this->option_multiplier;
        $this->save();
        return $this->subtotal;
    }
}