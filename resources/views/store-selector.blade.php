<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Select Delivery Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            -webkit-tap-highlight-color: transparent;
            overscroll-behavior-y: none;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        html { scroll-behavior: smooth; }
        .store-card { transition: all 0.2s ease-in-out; }
        .store-card:active { transform: scale(0.98); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col relative selection:bg-[#25D366] selection:text-white font-sans antialiased overflow-x-hidden">

    <!-- Header -->
    <header class="bg-white sticky top-0 z-50 border-b border-gray-200 shadow-sm py-3 px-4 sm:px-8">
        <div class="max-w-[1400px] mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3.5">
               <div class="h-10 flex-shrink-0 flex items-center justify-center bg-white">
                    <img src="{{ asset('public/images/Logo.png') }}" alt="Logo" class="h-full w-auto object-contain">
                </div>
                <div>
                    <h1 class="text-xl font-black text-gray-900 tracking-tight leading-none">Select Store</h1>
                    <p class="text-[10px] text-gray-400 font-extrabold uppercase tracking-[0.2em] mt-1.5">Delivery Location</p>
                </div>
            </div>
        </div>
    </header>

    <div id="main-content" class="flex-1 flex flex-col">
        <!-- Main Content -->
        <main class="max-w-[1400px] mx-auto px-4 sm:px-8 py-8 w-full">
            
            <!-- Search Bar -->
            <div class="mb-6 relative w-full group max-w-2xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#25D366] transition-colors" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" id="searchInput" class="block w-full pl-11 pr-4 py-3.5 border border-gray-200 rounded-2xl bg-white placeholder-gray-400 font-medium text-gray-900 focus:outline-none focus:border-[#25D366] focus:ring-4 focus:ring-green-500/10 transition-all shadow-sm text-base" placeholder="Search by store name..." autocomplete="off">
            </div>

            <div id="loading" class="text-center py-10" style="display: block;">
                <svg class="animate-spin h-8 w-8 text-[#25D366] mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-500 text-sm font-medium">Loading stores...</p>
            </div>

            <div id="no-results" class="text-center py-10 hidden">
                <p class="text-gray-500 font-medium">No stores found. Try another search.</p>
            </div>

            <div id="storeList" class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-8 hidden max-w-4xl mx-auto">
                <!-- Stores rendered here -->
            </div>
        </main>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden bg-gray-900/40 backdrop-blur-sm transition-opacity" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div id="modalContent" class="inline-block align-bottom bg-white rounded-2xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full sm:p-6 opacity-0 scale-95 mx-4 border border-gray-100">
            <div>
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-50 border-4 border-green-100">
                    <svg class="h-8 w-8 text-[#25D366]" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="mt-4 text-center sm:mt-5">
                    <h3 class="text-xl leading-6 font-black text-gray-900" id="modal-title">Store Selected!</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 font-medium">We have successfully saved your preferred store. The catalog has been sent to you on WhatsApp.</p>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                @php
                    $waPhone = env('WHATSAPP_PHONE_NUMBER', '');
                    $waPhoneClean = preg_replace('/[^0-9]/', '', $waPhone);
                    $waLink = $waPhoneClean ? 'https://wa.me/' . $waPhoneClean : 'whatsapp://send?text=Hello';
                @endphp
                <a href="{{ $waLink }}" onclick="document.getElementById('successModal').classList.add('hidden');" class="w-full py-3.5 bg-[#25D366] shadow-lg shadow-green-500/30 text-white rounded-xl font-extrabold text-[15px] hover:bg-[#128C7E] hover:shadow-green-500/40 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                    </svg>
                    Return to WhatsApp
                </a>
            </div>
        </div>
    </div>

    <script>
        const phone = '{{ $phone }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const searchInput = document.getElementById('searchInput');
        const storeList = document.getElementById('storeList');
        const loading = document.getElementById('loading');
        const noResults = document.getElementById('no-results');
        const successModal = document.getElementById('successModal');
        const modalContent = document.getElementById('modalContent');

        let debounceTimer;

        fetchStores('');

        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchStores(e.target.value.trim());
            }, 300);
        });

        async function fetchStores(query) {
            storeList.classList.add('hidden');
            noResults.classList.add('hidden');
            loading.style.display = 'block';

            try {
                const res = await fetch(`{{ url('api/store-selector/search') }}?q=${encodeURIComponent(query)}&_t=${Date.now()}`, {
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });
                const stores = await res.json();
                
                loading.style.display = 'none';

                if (stores.length === 0) {
                    noResults.classList.remove('hidden');
                    return;
                }

                storeList.innerHTML = '';
                stores.forEach(store => {
                    const card = document.createElement('div');
                    card.className = 'store-card bg-white rounded-[16px] shadow-sm border border-gray-100 p-5 flex gap-4 hover:shadow-md transition-all duration-200 cursor-pointer justify-between items-center';
                    card.onclick = () => selectStore(store.id, card);
                    card.innerHTML = `
                        <div class="pr-4 flex-1">
                            <h3 class="font-bold text-gray-900 text-[16px] leading-tight mb-1.5">${store.name}</h3>
                            <p class="text-[13px] text-gray-500 leading-snug">${store.address}<br>${store.city} - ${store.pincode}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <button class="select-btn px-6 py-2 bg-white text-[#00a651] border border-[#00a651] rounded-lg font-bold text-[13px] hover:bg-[#00a651] hover:text-white transition-colors">Select</button>
                        </div>
                    `;
                    storeList.appendChild(card);
                });
                storeList.classList.remove('hidden');
            } catch (err) {
                console.error(err);
                loading.style.display = 'none';
                noResults.innerHTML = '<p class="text-red-500 text-sm font-medium">Failed to load stores. Please check your connection and try again.</p>';
                noResults.classList.remove('hidden');
            }
        }

        async function selectStore(storeId, cardElement) {
            const btn = cardElement.querySelector('.select-btn');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '...';
            btn.disabled = true;
            
            try {
                const res = await fetch(`{{ url('api/store-selector/save') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ phone, store_id: storeId })
                });

                if (res.ok) {
                    // Show success modal
                    successModal.classList.remove('hidden');
                    setTimeout(() => {
                        modalContent.classList.remove('opacity-0', 'scale-95');
                        modalContent.classList.add('opacity-100', 'scale-100');
                    }, 10);
                } else {
                    alert('Error selecting store. Please try again.');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Something went wrong. Please check your connection.');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
