<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index()
    {
        return view('admin.equipment.index', [
            'equipment' => Equipment::latest()->paginate(20),
        ]);
    }

    public function create() { return view('admin.equipment.create'); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $item = Equipment::create($data);
        ActivityLogger::log('Equipment created', $item);

        return redirect()->route('admin.equipment.index')->with('success', 'Equipment added.');
    }

    public function edit(Equipment $equipment)
    {
        return view('admin.equipment.edit', compact('equipment'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $data = $this->validated($request);
        $equipment->update($data);
        ActivityLogger::log('Equipment updated', $equipment);

        return redirect()->route('admin.equipment.index')->with('success', 'Equipment updated.');
    }

    public function destroy(Equipment $equipment)
    {
        ActivityLogger::log('Equipment deleted', $equipment);
        $equipment->delete();

        return redirect()->route('admin.equipment.index')->with('success', 'Equipment deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:equipment,code,' . optional($request->route('equipment'))->id,
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:new,good,fair,poor,broken',
            'purchased_at' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'last_maintenance' => 'nullable|date',
            'next_maintenance' => 'nullable|date',
            'status' => 'required|in:available,in_use,maintenance,out_of_order',
        ]);
    }
}