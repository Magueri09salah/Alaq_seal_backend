<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Models\DevisItem;
use App\Models\DevisModifier;
use App\Models\PriceModifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DevisController extends Controller
{
    /**
     * Get all devis for authenticated user
     */
    public function index(Request $request)
    {
        $query = Devis::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $devis = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $devis->items(),
            'meta' => [
                'current_page' => $devis->currentPage(),
                'per_page' => $devis->perPage(),
                'total' => $devis->total(),
                'last_page' => $devis->lastPage(),
            ]
        ]);
    }

    /**
     * Get a single devis
     */
    public function show(Request $request, $id)
    {
        $devis = Devis::with(['items.service', 'modifiers.modifier'])
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$devis) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Devis non trouvé'
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $devis
        ]);
    }

    /**
     * Create a new devis
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'nullable|string|max:255',
            'project_location' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,saved',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.description' => 'required|string',
            'items.*.surface_m2' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.option_multiplier' => 'required|numeric|min:1',
            'items.*.selected_option_ids' => 'nullable|array',
            'modifier_ids' => 'nullable|array',
            'modifier_ids.*' => 'exists:price_modifiers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Données invalides',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create devis
            $devis = Devis::create([
                'devis_number' => Devis::generateDevisNumber($request->user()->id),
                'user_id' => $request->user()->id,
                'project_name' => $request->project_name,
                'project_location' => $request->project_location,
                'notes' => $request->notes,
                'status' => $request->status ?? 'draft',
                'tva_rate' => 20.00,
            ]);

            // Create items
            foreach ($request->items as $index => $itemData) {
                $subtotal = $itemData['surface_m2'] * $itemData['unit_price'] * $itemData['option_multiplier'];
                
                DevisItem::create([
                    'devis_id' => $devis->id,
                    'service_id' => $itemData['service_id'],
                    'description' => $itemData['description'],
                    'surface_m2' => $itemData['surface_m2'],
                    'unit_price' => $itemData['unit_price'],
                    'selected_options' => $itemData['selected_option_ids'] ?? [],
                    'option_multiplier' => $itemData['option_multiplier'],
                    'subtotal' => $subtotal,
                    'order_index' => $index,
                ]);
            }

            // Apply modifiers
            if ($request->has('modifier_ids') && is_array($request->modifier_ids)) {
                $itemsTotal = $devis->items->sum('subtotal');
                
                foreach ($request->modifier_ids as $modifierId) {
                    $modifier = PriceModifier::find($modifierId);
                    if ($modifier) {
                        $appliedValue = $this->calculateModifierValue($itemsTotal, $modifier);
                        
                        DevisModifier::create([
                            'devis_id' => $devis->id,
                            'modifier_id' => $modifierId,
                            'applied_value' => $appliedValue,
                        ]);
                    }
                }
            }

            // Calculate totals
            $devis->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $devis->load(['items.service', 'modifiers.modifier']),
                'message' => 'Devis créé avec succès'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATION_FAILED',
                    'message' => 'Erreur lors de la création du devis',
                    'details' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Update a devis (only drafts)
     */
    public function update(Request $request, $id)
    {
        $devis = Devis::where('user_id', $request->user()->id)->find($id);

        if (!$devis) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Devis non trouvé'
                ]
            ], 404);
        }

        if ($devis->status !== 'draft') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Seuls les brouillons peuvent être modifiés'
                ]
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'project_name' => 'nullable|string|max:255',
            'project_location' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'modifier_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Données invalides',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update devis
            $devis->update($request->only(['project_name', 'project_location', 'notes']));

            // Delete old items and modifiers
            $devis->items()->delete();
            $devis->modifiers()->delete();

            // Create new items
            foreach ($request->items as $index => $itemData) {
                $subtotal = $itemData['surface_m2'] * $itemData['unit_price'] * $itemData['option_multiplier'];
                
                DevisItem::create([
                    'devis_id' => $devis->id,
                    'service_id' => $itemData['service_id'],
                    'description' => $itemData['description'],
                    'surface_m2' => $itemData['surface_m2'],
                    'unit_price' => $itemData['unit_price'],
                    'selected_options' => $itemData['selected_option_ids'] ?? [],
                    'option_multiplier' => $itemData['option_multiplier'],
                    'subtotal' => $subtotal,
                    'order_index' => $index,
                ]);
            }

            // Apply new modifiers
            if ($request->has('modifier_ids')) {
                $itemsTotal = $devis->fresh()->items->sum('subtotal');
                
                foreach ($request->modifier_ids as $modifierId) {
                    $modifier = PriceModifier::find($modifierId);
                    if ($modifier) {
                        $appliedValue = $this->calculateModifierValue($itemsTotal, $modifier);
                        
                        DevisModifier::create([
                            'devis_id' => $devis->id,
                            'modifier_id' => $modifierId,
                            'applied_value' => $appliedValue,
                        ]);
                    }
                }
            }

            // Recalculate totals
            $devis->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $devis->load(['items.service', 'modifiers.modifier']),
                'message' => 'Devis mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Erreur lors de la mise à jour',
                    'details' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Calculate price without saving
     */
    public function calculate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.surface_m2' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.option_multiplier' => 'required|numeric|min:1',
            'modifier_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Données invalides',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        // Calculate items subtotal
        $itemsSubtotal = 0;
        foreach ($request->items as $item) {
            $itemsSubtotal += $item['surface_m2'] * $item['unit_price'] * $item['option_multiplier'];
        }

        // Calculate modifiers
        $modifiers = [];
        $surchargesTotal = 0;
        $discountsTotal = 0;

        if ($request->has('modifier_ids')) {
            foreach ($request->modifier_ids as $modifierId) {
                $modifier = PriceModifier::find($modifierId);
                if ($modifier) {
                    $appliedValue = $this->calculateModifierValue($itemsSubtotal, $modifier);
                    
                    $modifiers[] = [
                        'name' => $modifier->name,
                        'type' => $modifier->type,
                        'applied_value' => $appliedValue
                    ];

                    if ($modifier->type === 'surcharge') {
                        $surchargesTotal += $appliedValue;
                    } else {
                        $discountsTotal += abs($appliedValue);
                    }
                }
            }
        }

        $subtotalHt = $itemsSubtotal + $surchargesTotal - $discountsTotal;
        $tvaAmount = $subtotalHt * 0.20;
        $totalTtc = $subtotalHt + $tvaAmount;

        return response()->json([
            'success' => true,
            'data' => [
                'items_subtotal' => round($itemsSubtotal, 2),
                'modifiers' => $modifiers,
                'subtotal_ht' => round($subtotalHt, 2),
                'tva_rate' => 20.00,
                'tva_amount' => round($tvaAmount, 2),
                'total_ttc' => round($totalTtc, 2),
                'breakdown' => [
                    'base_price' => round($itemsSubtotal, 2),
                    'surcharges' => round($surchargesTotal, 2),
                    'discounts' => round($discountsTotal, 2),
                ]
            ]
        ]);
    }

    /**
     * Submit devis to Alaq Seal
     */
    public function submit(Request $request, $id)
    {
        $devis = Devis::where('user_id', $request->user()->id)->find($id);

        if (!$devis) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Devis non trouvé'
                ]
            ], 404);
        }

        $devis->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // TODO: Send email notification to Alaq Seal

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $devis->status,
                'submitted_at' => $devis->submitted_at,
            ],
            'message' => 'Devis soumis avec succès. Alaq Seal vous contactera sous 24-48h.'
        ]);
    }

    /**
     * Delete a devis
     */
    public function destroy(Request $request, $id)
    {
        $devis = Devis::where('user_id', $request->user()->id)->find($id);

        if (!$devis) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Devis non trouvé'
                ]
            ], 404);
        }

        $devis->delete();

        return response()->json([
            'success' => true,
            'message' => 'Devis supprimé avec succès'
        ]);
    }

    /**
     * Helper: Calculate modifier value
     */
    private function calculateModifierValue($baseAmount, $modifier)
    {
        if ($modifier->is_percentage) {
            $value = $baseAmount * ($modifier->value / 100);
        } else {
            $value = $modifier->value;
        }

        return $modifier->type === 'discount' ? -abs($value) : abs($value);
    }
}