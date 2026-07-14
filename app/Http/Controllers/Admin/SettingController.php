<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $taxName = Setting::where('key', 'tax_name')->value('value') ?? '';
        $taxPercent = Setting::where('key', 'tax_percent')->value('value') ?? '';

        return view('admin.settings.index', compact('taxName', 'taxPercent'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'tax_name' => 'nullable|string|max:255',
            'tax_percent' => 'nullable|numeric|min:0',
        ]);

        Setting::updateOrCreate(['key' => 'tax_name'], ['value' => $request->tax_name]);
        Setting::updateOrCreate(['key' => 'tax_percent'], ['value' => $request->tax_percent]);

        return redirect()->route('admin.settings')->with('success', 'Global tax settings updated successfully.');
    }
}
