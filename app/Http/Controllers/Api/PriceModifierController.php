<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceModifier;
use Illuminate\Http\Request;

class PriceModifierController extends Controller
{
    /**
     * Get all active price modifiers
     */
    public function index()
    {
        $modifiers = PriceModifier::active()->get();

        return response()->json([
            'success' => true,
            'data' => $modifiers
        ]);
    }
}