<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of(Category::query())
                ->addColumn('action', function ($row) {
                    $editUrl = route('categories.edit', $row->id);
                    $deleteUrl = route('categories.destroy', $row->id);
                    return '
                        <a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <button type="button" onclick="openDeleteModal(\''.$deleteUrl.'\')" class="text-red-600 hover:text-red-900">Delete</button>
                    ';
                })
                ->editColumn('is_active', function ($row) {
                    $color = $row->is_active ? 'green' : 'red';
                    $text = $row->is_active ? 'Active' : 'Inactive';
                    return '<span class="relative inline-block px-3 py-1 font-semibold text-'.$color.'-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-'.$color.'-200 opacity-50 rounded-full"></span>
                                <span class="relative">'.$text.'</span>
                            </span>';
                })
                ->rawColumns(['action', 'is_active'])
                ->make(true);
        }

        return view('admin.categories.index');
    }

    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create([
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted');
    }
}
