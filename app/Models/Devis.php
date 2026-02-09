<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devis extends Model
{
    use HasFactory;

    protected $table = 'devis';

    protected $fillable = [
        'devis_number',
        'user_id',
        'project_name',
        'project_location',
        'notes',
        'status',
        'subtotal_ht',
        'tva_rate',
        'tva_amount',
        'total_ttc',
        'pdf_path',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'subtotal_ht' => 'decimal:2',
        'tva_rate' => 'decimal:2',
        'tva_amount' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(DevisItem::class);
    }

    public function modifiers()
    {
        return $this->hasMany(DevisModifier::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSaved($query)
    {
        return $query->where('status', 'saved');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    // Helper Methods
    public static function generateDevisNumber($userId)
    {
        return 'DEV-' . $userId . '-' . now()->format('YmdHis');
    }

    public function calculateTotals()
    {
        $this->subtotal_ht = $this->items->sum('subtotal');
        
        // Apply modifiers
        $modifierTotal = $this->modifiers->sum('applied_value');
        $this->subtotal_ht += $modifierTotal;
        
        // Calculate TVA
        $this->tva_amount = $this->subtotal_ht * ($this->tva_rate / 100);
        
        // Calculate TTC
        $this->total_ttc = $this->subtotal_ht + $this->tva_amount;
        
        $this->save();
    }
}