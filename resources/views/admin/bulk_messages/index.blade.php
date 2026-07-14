@extends('admin.layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold">Send WhatsApp Messages</h1>
</div>

<div class="bg-white shadow rounded-lg p-6 max-w-3xl">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('bulk-messages.send') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Target Audience *</label>
            <div class="flex gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="target_type" value="group" class="form-radio h-5 w-5 text-blue-600" checked onchange="toggleTarget()">
                    <span class="ml-2">Store Group</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="target_type" value="single" class="form-radio h-5 w-5 text-blue-600" onchange="toggleTarget()">
                    <span class="ml-2">Single Store</span>
                </label>
            </div>
        </div>

        <div id="group_selection" class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Select Store Group *</label>
            <select name="store_group_id" class="w-full border rounded px-3 py-2">
                <option value="">-- Choose Group --</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->stores->count() }} stores)</option>
                @endforeach
            </select>
        </div>

        <div id="single_selection" class="mb-4 hidden">
            <label class="block text-gray-700 font-bold mb-2">Select Store *</label>
            <select name="store_id" class="w-full border rounded px-3 py-2">
                <option value="">-- Choose Store --</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->mobile_number }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Message *</label>
            <textarea name="message" class="w-full border rounded px-3 py-2" rows="6" placeholder="Type your WhatsApp message here..." required></textarea>
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" onclick="return confirm('Are you sure you want to send this message?');">Send Message</button>
    </form>
</div>

<script>
    function toggleTarget() {
        const type = document.querySelector('input[name="target_type"]:checked').value;
        if (type === 'group') {
            document.getElementById('group_selection').classList.remove('hidden');
            document.getElementById('single_selection').classList.add('hidden');
        } else {
            document.getElementById('group_selection').classList.add('hidden');
            document.getElementById('single_selection').classList.remove('hidden');
        }
    }
</script>
@endsection
