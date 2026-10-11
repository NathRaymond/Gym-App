<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index()
    {
        return response()->json(Equipment::latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:equipment,code',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'condition' => 'nullable|string',
            'purchased_at' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'last_maintenance' => 'nullable|date',
            'next_maintenance' => 'nullable|date',
            'status' => 'nullable|in:available,in_use,maintenance,out_of_order',
        ]);
        $e = Equipment::create($data);
        ActivityLogger::log('Equipment created', $e);
        return response()->json(['message' => 'Equipment created', 'equipment' => $e], 201);
    }

    public function update(Request $request, Equipment $equipment)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'quantity' => 'sometimes|integer|min:1',
            'condition' => 'nullable|string',
            'last_maintenance' => 'nullable|date',
            'next_maintenance' => 'nullable|date',
            'status' => 'nullable|in:available,in_use,maintenance,out_of_order',
        ]);
        $equipment->update($data);
        return response()->json(['message' => 'Equipment updated', 'equipment' => $equipment->fresh()]);
    }

    public function destroy(Equipment $equipment)
    {
        ActivityLogger::log('Equipment deleted', $equipment);
        $equipment->delete();
        return response()->json(['message' => 'Equipment deleted']);
    }
}