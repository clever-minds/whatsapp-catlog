<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreGroup;
use App\Models\Store;

class StoreGroupController extends Controller
{
    public function index()
    {
        $groups = StoreGroup::withCount('stores')->get();
        return view('admin.store_groups.index', compact('groups'));
    }

    public function create()
    {
        $stores = Store::all();
        return view('admin.store_groups.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stores' => 'array',
            'stores.*' => 'exists:stores,id'
        ]);

        $group = StoreGroup::create([
            'name' => $request->name
        ]);

        if ($request->has('stores')) {
            $group->stores()->sync($request->stores);
        }

        return redirect()->route('store-groups.index')->with('success', 'Store Group created successfully.');
    }

    public function edit(StoreGroup $storeGroup)
    {
        $stores = Store::all();
        return view('admin.store_groups.edit', compact('storeGroup', 'stores'));
    }

    public function update(Request $request, StoreGroup $storeGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stores' => 'array',
            'stores.*' => 'exists:stores,id'
        ]);

        $storeGroup->update([
            'name' => $request->name
        ]);

        if ($request->has('stores')) {
            $storeGroup->stores()->sync($request->stores);
        } else {
            $storeGroup->stores()->sync([]);
        }

        return redirect()->route('store-groups.index')->with('success', 'Store Group updated successfully.');
    }

    public function destroy(StoreGroup $storeGroup)
    {
        $storeGroup->delete();
        return redirect()->route('store-groups.index')->with('success', 'Store Group deleted successfully.');
    }
}
