@extends('admin.layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Send Notifications</h1>
        <p class="text-gray-600 mt-1">Send a custom WhatsApp message to multiple stores at once.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="space-y-8 mb-8">
        <!-- Send Message Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">New Message</h2>
            <form action="{{ route('notifications.send') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Select Stores</label>
                    <select name="store_ids[]" id="storeSelect" multiple required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->country_code }}
                                {{ $store->mobile_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">Message Content</label>
                    <textarea name="message" id="messageEditor" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Type your WhatsApp message here..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Basic formatting (Bold, Italic) will be converted to WhatsApp
                        formatting automatically.</p>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded transition-colors flex justify-center items-center text-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Send Notification
                </button>
            </form>
        </div>

        <!-- Notification History -->
        <div class="bg-white shadow rounded-lg overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <h2 class="text-xl font-bold text-gray-800">Message History</h2>
            </div>

            <div class="p-0 flex-1 overflow-x-auto">
                <table class="min-w-full leading-normal w-full">
                    <thead>
                        <tr>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date</th>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/3">
                                Message</th>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Recipients & Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $notification)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                    <div class="text-gray-900 whitespace-nowrap">
                                        {{ $notification->created_at->format('M d, Y') }}</div>
                                    <div class="text-gray-500 text-xs">{{ $notification->created_at->format('H:i A') }}</div>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 text-sm max-w-xs text-gray-600">
                                    {{ $notification->message }}
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 text-sm" colspan="2">
                                    <div class="max-h-32 overflow-y-auto pr-2">
                                        @forelse($notification->recipients as $recipient)
                                            <div
                                                class="flex justify-between items-center mb-1 pb-1 border-b border-gray-50 last:border-0">
                                                <span
                                                    class="font-medium text-gray-800 text-xs">{{ $recipient->store->name ?? 'Deleted Store' }}</span>
                                                @if($recipient->status == 'sent')
                                                    <span
                                                        class="px-1.5 py-0.5 inline-flex text-[10px] leading-4 font-semibold rounded-full bg-green-100 text-green-800">Sent</span>
                                                @else
                                                    <span
                                                        class="px-1.5 py-0.5 inline-flex text-[10px] leading-4 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                                @endif
                                            </div>
                                        @empty
                                            <span class="text-xs text-gray-400">No recipients saved</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 border-b border-gray-200 text-center text-sm text-gray-500">
                                    No notifications sent yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($notifications->hasPages())
                <div class="p-4 border-t border-gray-200 bg-white">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

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

            $('#messageEditor').summernote({
                placeholder: 'Type your WhatsApp message here... (Bold, Italic, Strikethrough are supported)',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['bold', 'italic', 'strikethrough', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['view', ['codeview']]
                ]
            });
        });
    </script>
@endsection