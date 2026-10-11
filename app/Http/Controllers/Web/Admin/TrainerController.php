<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainerController extends Controller
{
    public function index()
    {
        return view('admin.trainers.index', [
            'trainers' => Trainer::with('user')
                ->whereHas('user')                 // ← only trainers whose user exists
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.trainers.create', [
            'users' => User::role('trainer')
                ->doesntHave('trainer')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'          => 'required|exists:users,id|unique:trainers,user_id',
            'bio'              => 'nullable|string',
            'specializations'  => 'nullable|string',
            'certifications'   => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0|max:70',
            'hourly_rate'      => 'nullable|numeric|min:0',
            'is_available'     => 'boolean',
        ]);

        $data['specializations'] = $this->csvToArray($data['specializations'] ?? '');
        $data['certifications']  = $this->csvToArray($data['certifications'] ?? '');
        $data['is_available']    = $request->boolean('is_available', true);

        $trainer = Trainer::create($data);
        ActivityLogger::log('Trainer created', $trainer);

        return redirect()
            ->route('admin.trainers.index')
            ->with('success', 'Trainer profile created.');
    }

    public function edit(Trainer $trainer)
    {
        return view('admin.trainers.edit', [
            'trainer' => $trainer,
            'users'   => User::role('trainer')->get(),
        ]);
    }

    public function update(Request $request, Trainer $trainer)
    {
        $data = $request->validate([
            'user_id'          => ['required', 'exists:users,id', Rule::unique('trainers', 'user_id')->ignore($trainer->id)],
            'bio'              => 'nullable|string',
            'specializations'  => 'nullable|string',
            'certifications'   => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0|max:70',
            'hourly_rate'      => 'nullable|numeric|min:0',
            'is_available'     => 'boolean',
        ]);

        $data['specializations'] = $this->csvToArray($data['specializations'] ?? '');
        $data['certifications']  = $this->csvToArray($data['certifications'] ?? '');
        $data['is_available']    = $request->boolean('is_available', true);

        $trainer->update($data);
        ActivityLogger::log('Trainer updated', $trainer);

        return redirect()
            ->route('admin.trainers.index')
            ->with('success', 'Trainer updated.');
    }

    public function destroy(Trainer $trainer)
    {
        // Prevent deleting a trainer with upcoming slots
        if ($trainer->bookings()->whereDate('booking_date', '>=', today())->exists()) {
            return back()->with('error', 'Cannot delete a trainer with upcoming bookings.');
        }

        ActivityLogger::log('Trainer deleted', $trainer);
        $trainer->delete();

        return redirect()
            ->route('admin.trainers.index')
            ->with('success', 'Trainer deleted.');
    }

    protected function csvToArray(string $value): array
    {
        if (empty(trim($value))) return [];
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}