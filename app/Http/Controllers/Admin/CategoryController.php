<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $slug = Str::slug($request->name);

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'supports_size' => 'boolean',
            'supports_color' => 'boolean',
        ]);
        $imagePath = null;

        validator(['slug' => $slug], [
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')],
        ])->validate();

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'image' => $imagePath,
            'supports_size' => $request->has('supports_size'),
            'supports_color' => $request->has('supports_color'),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría creada con éxito.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'supports_size' => 'boolean',
            'supports_color' => 'boolean',
        ]);

        validator(['slug' => $category->slug], [
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
        ])->validate();

        $category->name = $request->name;
        $category->supports_size = $request->has('supports_size');
        $category->supports_color = $request->has('supports_color');

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $category->image = $request->file('image')->store('categories', 'public');
        }

        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Categoría actualizada con éxito.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'No se puede eliminar una categoría con productos.');
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Categoría eliminada con éxito.');
    }
}
