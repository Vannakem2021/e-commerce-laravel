<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\CambodiaAddressService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    public function __construct(
        private CambodiaAddressService $cambodiaAddressService
    ) {}

    /**
     * Get Cambodia provinces.
     */
    public function getProvinces(): JsonResponse
    {
        $provinces = $this->cambodiaAddressService->getProvinces();

        return response()->json($provinces);
    }

    /**
     * Get districts for a province.
     */
    public function getDistricts(Request $request): JsonResponse
    {
        $request->validate([
            'province_id' => 'required|integer',
        ]);

        $districts = $this->cambodiaAddressService->getDistricts($request->province_id);

        return response()->json($districts);
    }

    /**
     * Get communes for a district.
     */
    public function getCommunes(Request $request): JsonResponse
    {
        $request->validate([
            'district_id' => 'required|integer',
        ]);

        $communes = $this->cambodiaAddressService->getCommunes($request->district_id);

        return response()->json($communes);
    }

    /**
     * Get postal code for selected area.
     */
    public function getPostalCode(Request $request): JsonResponse
    {
        $request->validate([
            'province_id' => 'required|integer',
            'district_id' => 'required|integer',
            'commune_name' => 'required|string',
        ]);

        $postalCode = $this->cambodiaAddressService->getPostalCode(
            $request->province_id,
            $request->district_id,
            $request->commune_name
        );

        return response()->json(['postal_code' => $postalCode]);
    }

    /**
     * Search areas for autocomplete.
     */
    public function searchAreas(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $results = $this->cambodiaAddressService->searchAreas($request->query);

        return response()->json($results);
    }

    /**
     * Store a new address.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['shipping', 'billing'])],
            'contact_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'house_number' => 'nullable|string|max:100',
            'street_number' => 'nullable|string|max:100',
            'city_province' => 'required|string|max:255',
            'district_khan' => 'required|string|max:255',
            'commune_sangkat' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'additional_info' => 'nullable|string|max:500',
            'is_default' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();

        $address = Address::create($validated);

        if ($validated['is_default'] ?? false) {
            $address->setAsDefault();
        }

        return response()->json([
            'message' => 'Address created successfully',
            'address' => $address->load('user'),
        ], 201);
    }

    /**
     * Update an existing address.
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        // Ensure user can only update their own addresses
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(['shipping', 'billing'])],
            'contact_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'house_number' => 'nullable|string|max:100',
            'street_number' => 'nullable|string|max:100',
            'city_province' => 'required|string|max:255',
            'district_khan' => 'required|string|max:255',
            'commune_sangkat' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'additional_info' => 'nullable|string|max:500',
            'is_default' => 'boolean',
        ]);

        $address->update($validated);

        if ($validated['is_default'] ?? false) {
            $address->setAsDefault();
        }

        return response()->json([
            'message' => 'Address updated successfully',
            'address' => $address->fresh()->load('user'),
        ]);
    }

    /**
     * Delete an address.
     */
    public function destroy(Address $address): JsonResponse
    {
        // Ensure user can only delete their own addresses
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully',
        ]);
    }
}
