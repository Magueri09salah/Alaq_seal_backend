<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseMaterial extends Model
{
    protected $table    = 'case_materials';
    protected $fillable = [
        'product_case_id',
        'step_order',
        'name',
        'type',
        'formula_type',
        'formula_factor',
        'unit',
        'is_optional',
    ];
    protected $casts = [
        'formula_factor' => 'decimal:4',
        'is_optional'    => 'boolean',
    ];

    public function productCase()
    {
        return $this->belongsTo(ProductCase::class);
    }
}
