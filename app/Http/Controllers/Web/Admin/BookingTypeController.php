<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingType;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingTypeController extends Controller
{
    public function index()
    {
        return view('admin.booking-types.index', [
            'bookingTypes' => BookingType::orderBy('display_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.booking-types.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']);

        // Ensure slug uniqueness
        $baseSlug = $data['slug'];
        $i = 1;
        while (BookingType::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $baseSlug . '-' . $i++;
        }

        $type = BookingType::create($data);
        ActivityLogger::log('Booking type created', $type);

        return redirect()->route('admin.booking-types.index')
            ->with('success', "Booking type '{$type->name}' created.");
    }

    public function edit(BookingType $bookingType)
    {
        return view('admin.booking-types.edit', [
            'bookingType' => $bookingType,
        ]);
    }

    public function update(Request $request, BookingType $bookingType)
    {
        $data = $this->validated($request, $bookingType->id);

        if ($data['name'] !== $bookingType->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $bookingType->update($data);
        ActivityLogger::log('Booking type updated', $bookingType);

        return redirect()->route('admin.booking-types.index')
            ->with('success', 'Booking type updated.');
    }

    public function destroy(BookingType $bookingType)
    {
        if ($bookingType->bookings()->exists()) {
            return back()->with('error', 'Cannot delete a booking type with existing bookings.');
        }

        ActivityLogger::log('Booking type deleted', $bookingType);
        $bookingType->delete();

        return redirect()->route('admin.booking-types.index')
            ->with('success', 'Booking type deleted.');
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100', Rule::unique('booking_types', 'name')->ignore($id)],
            'color'         => 'required|in:brand,green,blue,orange,red,purple,pink,gray',
            'icon'          => 'nullable|string|max:50',
            'description'   => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        $data['is_active']     = $request->boolean('is_active', true);
        $data['display_order'] = $data['display_order'] ?? 0;

        return $data;
    }
}