@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Units</h1>
    <a href="{{ route('units.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Unit</a>
</div>

<div class="bg-white shadow rounded-lg overflow-x-auto overflow-y-hidden p-6">
    <table id="units-table" class="min-w-full leading-normal w-full stripe hover">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Short Name</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Delete Unit Form -->
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#units-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("units.index") }}',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'short_name', name: 'short_name' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });

    function openDeleteModal(url) {
        if(confirm('Are you sure you want to delete this unit?')) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endsection
