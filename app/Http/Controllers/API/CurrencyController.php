<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Http\Requests\API\CurrencyRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Currency::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Order by default first, then name
        $query->orderBy('is_default', 'desc')
              ->orderBy('name');

        $currencies = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'data' => $currencies->items(),
            'pagination' => [
                'current_page' => $currencies->currentPage(),
                'last_page' => $currencies->lastPage(),
                'per_page' => $currencies->perPage(),
                'total' => $currencies->total(),
                'from' => $currencies->firstItem(),
                'to' => $currencies->lastItem(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CurrencyRequest $request): JsonResponse
    {
        $currency = Currency::create($request->validated());

        return response()->json([
            'message' => 'Currency created successfully',
            'data' => $currency
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Currency $currency): JsonResponse
    {
        return response()->json([
            'data' => $currency
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CurrencyRequest $request, Currency $currency): JsonResponse
    {
        $currency->update($request->validated());

        return response()->json([
            'message' => 'Currency updated successfully',
            'data' => $currency
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currency $currency): JsonResponse
    {
        // Prevent deletion of default currency
        if ($currency->is_default) {
            return response()->json([
                'message' => 'Cannot delete default currency'
            ], 422);
        }

        $currency->delete();

        return response()->json([
            'message' => 'Currency deleted successfully'
        ]);
    }

    /**
     * Toggle currency status.
     */
    public function toggleStatus(Currency $currency): JsonResponse
    {
        $currency->is_active = !$currency->is_active;
        $currency->save();

        return response()->json([
            'message' => "Currency " . ($currency->is_active ? 'activated' : 'deactivated') . " successfully",
            'data' => $currency
        ]);
    }

    /**
     * Set currency as default.
     */
    public function setDefault(Currency $currency): JsonResponse
    {
        if ($currency->is_default) {
            return response()->json([
                'message' => 'Currency is already default'
            ]);
        }

        // Remove default from all other currencies
        Currency::where('is_default', true)
               ->where('id', '!=', $currency->id)
               ->update(['is_default' => false]);

        // Set this currency as default
        $currency->is_default = true;
        $currency->save();

        return response()->json([
            'message' => "{$currency->name} set as default currency successfully",
            'data' => $currency
        ]);
    }

    /**
     * Get default currency.
     */
    public function getDefault(): JsonResponse
    {
        $currency = Currency::getDefault();

        if (!$currency) {
            return response()->json([
                'message' => 'No default currency found'
            ], 404);
        }

        return response()->json([
            'data' => $currency
        ]);
    }

    /**
     * Get active currencies.
     */
    public function getActive(): JsonResponse
    {
        $currencies = Currency::getActive();

        return response()->json([
            'data' => $currencies
        ]);
    }
}
