@extends('admin.layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold">Add Store Group</h1>
</div>

<div class="bg-white shadow rounded-lg p-6 max-w-3xl">
    <form action="{{ route('store-groups.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Group Name *</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Select Stores to Include</label>
            <select name="stores[]" id="storeSelect" multiple required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @foreach($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->mobile_number }})</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Save Group</button>
        <a href="{{ route('store-groups.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .select2-container .select2-selection--multiple {
            border-color: #d1d5db;
            border-radius: 0.5rem;
            min-height: 42px;
        }

        .custom-checkbox {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            margin-right: 8px;
            vertical-align: middle;
            position: relative;
            background-color: #fff;
            transition: all 0.2s;
        }

        .select2-results__option[aria-selected="true"] .custom-checkbox {
            background-color: #25eb2cff;
            border-color: #25eb2cff;
        }

        .select2-results__option[aria-selected="true"] .custom-checkbox::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 2px;
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
    </style>

    <script>
        $(document).ready(function () {
            var $storeSelect = $('#storeSelect');
            $storeSelect.select2({
                placeholder: "Search and select stores...",
                allowClear: true,
                width: '100%',
                closeOnSelect: false,
                templateResult: function (state) {
                    if (!state.id) {
                        return state.text;
                    }
                    var $state = $(
                        '<span class="flex items-center w-full"><span class="custom-checkbox cursor-pointer"></span> <span class="cursor-default">' + state.text + '</span></span>'
                    );
                    return $state;
                }
            });

            // Only allow selection if the custom checkbox is clicked
            $storeSelect.on('select2:selecting select2:unselecting', function (e) {
                if (e.params && e.params.originalEvent) {
                    var $target = $(e.params.originalEvent.target);
                    if (!$target.hasClass('custom-checkbox')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
@endsection
