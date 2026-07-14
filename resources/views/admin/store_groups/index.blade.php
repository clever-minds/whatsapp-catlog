@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Store Groups</h1>
    <a href="{{ route('store-groups.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add New Group</a>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b text-left">ID</th>
                <th class="py-2 px-4 border-b text-left">Name</th>
                <th class="py-2 px-4 border-b text-left">Stores Count</th>
                <th class="py-2 px-4 border-b text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $group)
                <tr>
                    <td class="py-2 px-4 border-b">{{ $group->id }}</td>
                    <td class="py-2 px-4 border-b">{{ $group->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $group->stores_count }}</td>
                    <td class="py-2 px-4 border-b">
                        <a href="{{ route('store-groups.edit', $group) }}" class="text-blue-500 hover:text-blue-700 mr-3">Edit</a>
                        <form action="{{ route('store-groups.destroy', $group) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this group?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-4 px-4 text-center text-gray-500">No store groups found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
