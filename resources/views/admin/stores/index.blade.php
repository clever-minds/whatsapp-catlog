@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Stores</h1>
    <a href="{{ route('stores.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
        Add Store
    </a>
</div>

<div class="bg-white shadow rounded-lg overflow-x-auto overflow-y-hidden p-6">
    <table id="stores-table" class="min-w-full leading-normal w-full stripe hover">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Address</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Delete Store Form -->
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#stores-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("stores.index") }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name', className: 'font-semibold' },
                { 
                    data: null, 
                    name: 'mobile_number',
                    render: function(data, type, row) { 
                        return row.mobile_number ? (row.country_code + ' ' + row.mobile_number) : '<span class="text-gray-400">Not Set</span>'; 
                    } 
                },
                { data: 'address', name: 'address', render: function(data) { return data ? data : 'N/A'; } },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });

    function openDeleteModal(url) {
        if(confirm('Delete this store?')) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endsection
