@extends('admin.layouts.app')

@section('page-title', 'Global Settings')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Global Settings</h1>
</div>

@if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm mb-6 flex items-start">
        <svg class="w-5 h-5 text-emerald-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
            <p class="font-bold">Success</p>
            <p class="text-sm">{{ session('success') }}</p>
        </div>
    </div>
@endif

<div class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 flex justify-between items-center text-white" style="background:linear-gradient(135deg,#14532d,#15803d);">
        <h2 class="font-bold text-lg tracking-wide">Tax Settings</h2>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" class="p-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Global Tax Name</label>
                <input type="text" name="tax_name" value="{{ old('tax_name', $taxName) }}" class="w-full border border-gray-200 py-2.5 px-4 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="e.g. VAT, GST">
            </div>
            
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Global Tax Percentage (%)</label>
                <div class="relative">
                    <input type="number" step="0.01" min="0" name="tax_percent" value="{{ old('tax_percent', $taxPercent) }}" class="w-full border border-gray-200 py-2.5 pl-4 pr-10 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="e.g. 5.00">
                    <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 font-bold">%</span>
                </div>
            </div>
        </div>
        
        <p class="text-sm text-gray-500 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
            <strong>Note:</strong> This tax percentage will be automatically applied to all new orders and dynamically calculated when an order is updated in the admin panel. 
        </p>

        <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-all duration-150 hover:-translate-y-0.5 active:translate-y-0 text-center">
            Save Settings
        </button>
    </form>
</div>

@endsection
