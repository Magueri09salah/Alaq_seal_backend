<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCase extends Model
{
    protected $table    = 'product_cases';
    protected $fillable = ['product_id', 'code', 'name', 'description', 'icon_type', 'display_order'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function materials()
    {
        return $this->hasMany(CaseMaterial::class, 'product_case_id')->orderBy('step_order');
    }
}
