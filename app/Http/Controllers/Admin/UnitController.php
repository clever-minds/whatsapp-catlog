<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of(Unit::query())
                ->addColumn('action', function ($row) {
                    $editUrl = route('units.edit', $row->id);
                    $deleteUrl = route('units.destroy', $row->id);
                    return '
                        <a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <button type="button" onclick="openDeleteModal(\''.$deleteUrl.'\')" class="text-red-600 hover:text-red-900">Delete</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.units.index');
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50'
        ]);
        
        Unit::create($data);
        return redirect()->route('units.index')->with('success', 'Unit created');
    }

    public function edit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50'
        ]);

        $unit->update($data);
        return redirect()->route('units.index')->with('success', 'Unit updated');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unit deleted');
    }
}
