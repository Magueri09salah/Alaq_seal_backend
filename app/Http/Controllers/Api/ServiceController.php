<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Get all active services with their options
     */
    public function index()
    {
        $services = Service::active()
            ->ordered()
            ->with('options')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Get a single service with options
     */
    public function show($id)
    {
        $service = Service::with('options')->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Service non trouvé'
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }

    /**
     * Get service options
     */
    public function options($id)
    {
        $service = Service::with('options')->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Service non trouvé'
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service->options
        ]);
    }
}