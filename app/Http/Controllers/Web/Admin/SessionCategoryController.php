<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\SessionCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionCategoryController extends Controller
{
    public function index()
    {
        return view('admin.session-categories.index', [
            'categories' => SessionCategory::withCount('sessionTypes')
                ->orderBy('display_order')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('admin.session-categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']);

        $category = SessionCategory::create($data);
        ActivityLogger::log('Session category created', $category);

        return redirect()->route('admin.session-categories.index')
            ->with('success', "Category '{$category->name}' created.");
    }

    public function edit(SessionCategory $sessionCategory)
    {
        return view('admin.session-categories.edit', [
            'category' => $sessionCategory,
        ]);
    }

    public function update(Request $request, SessionCategory $sessionCategory)
    {
        $data = $this->validated($request);
        if ($data['name'] !== $sessionCategory->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $sessionCategory->update($data);
        ActivityLogger::log('Session category updated', $sessionCategory);

        return redirect()->route('admin.session-categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(SessionCategory $sessionCategory)
    {
        if ($sessionCategory->sessionTypes()->exists()) {
            return back()->with('error', 'Cannot delete a category with session types.');
        }

        ActivityLogger::log('Session category deleted', $sessionCategory);
        $sessionCategory->delete();

        return redirect()->route('admin.session-categories.index')
            ->with('success', 'Category deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name'             => 'required|string|max:100',
            'icon'             => 'nullable|string|max:50',
            'color'            => 'required|in:brand,green,blue,orange,red,purple,pink,gray',
            'description'      => 'nullable|string|max:500',
            'default_capacity' => 'required|integer|min:1|max:500',
            'requires_trainer' => 'boolean',
            'is_active'        => 'boolean',
            'display_order'    => 'nullable|integer|min:0',
        ]);
    }
}