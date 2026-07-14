<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of(Store::query())
                ->addColumn('action', function ($row) {
                    $editUrl = route('stores.edit', $row->id);
                    $deleteUrl = route('stores.destroy', $row->id);
                    return '
                        <a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <button type="button" onclick="openDeleteModal(\''.$deleteUrl.'\')" class="text-red-600 hover:text-red-900">Delete</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.stores.index');
    }

    public function create()
    {
        return view('admin.stores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'country_code' => 'nullable|string|max:5',
            'mobile_number' => 'nullable|string|max:15|regex:/^[0-9]+$/',
        ]);

        Store::create($data);

        return redirect()->route('stores.index')->with('success', 'Store created successfully.');
    }

    public function edit(Store $store)
    {
        return view('admin.stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'country_code' => 'nullable|string|max:5',
            'mobile_number' => 'nullable|string|max:15|regex:/^[0-9]+$/',
        ]);

        $store->update($data);

        return redirect()->route('stores.index')->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->route('stores.index')->with('success', 'Store deleted successfully.');
    }
}
