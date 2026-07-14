<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Place Order - Catalog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            -webkit-tap-highlight-color: transparent;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Smooth scrolling for native feel */
        html { scroll-behavior: smooth; }
        /* Prevent pull-to-refresh on mobile if we want an app feel */
        body { overscroll-behavior-y: none; }
        
        @keyframes pop-bounce {
            0% { transform: scale(1); }
            40% { transform: scale(1.25); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }
        .animate-pop {
            animation: pop-bounce 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col pb-24 relative selection:bg-[#25D366] selection:text-white font-sans antialiased overflow-x-hidden">
    <!-- Header -->
    <header class="bg-white sticky top-0 z-50 border-b border-gray-200 shadow-sm py-3 px-4 sm:px-8">
        <div class="max-w-[1400px] mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                 <div class="h-10 flex-shrink-0 flex items-center justify-center bg-white">
                    <img src="{{ asset('public/images/Logo.png') }}" alt="Logo" class="h-full w-auto object-contain">
                </div>
                <div>
                    <h1 class="text-xl font-black text-gray-900 tracking-tight leading-none">Order Portal</h1>
                </div>
            </div>



            <div class="flex items-center gap-4">
                <button id="cartButton" onclick="toggleCart()" class="relative bg-gray-50 hover:bg-green-50 p-2.5 rounded-xl border border-gray-200 transition-all group lg:hidden">
                    <svg class="w-6 h-6 text-gray-700 group-hover:text-[#25D366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span id="cartBadge" class="absolute -top-1.5 -right-1.5 bg-[#25D366] text-white text-[11px] font-black rounded-full h-5 min-w-[20px] flex items-center justify-center px-1 border-2 border-white shadow-sm">0</span>
                </button>
            </div>
        </div>
    </header>
            
    <!-- Category Nav -->
    <div class="bg-white border-b border-gray-100 sticky top-[65px] z-40 shadow-sm" id="stickyNav">
        <div class="max-w-[1400px] mx-auto flex gap-3 overflow-x-auto px-4 sm:px-8 py-4 no-scrollbar items-center justify-start">
            <button onclick="selectCategory('all')" id="btn-cat-all" class="cat-nav-btn flex-shrink-0 px-5 py-2.5 rounded-full font-bold text-[14px] transition-all duration-300 border-2 active bg-[#25D366] text-white border-[#25D366]">
                View All
            </button>
            @foreach($categories as $category)
                <button onclick="selectCategory('{{ $category->id }}')" id="btn-cat-{{ $category->id }}" class="cat-nav-btn flex-shrink-0 px-5 py-2.5 rounded-full font-bold text-[14px] transition-all duration-300 border-2 border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </div>

        <!-- Subcategories Bar -->
        <div id="subCategoriesContainer" class="w-full bg-gray-50/50 border-t border-gray-100 shadow-inner sticky top-[135px] z-30">
            @foreach($categories as $category)
                @if($category->children->count() > 0)
                    <div id="subnav-{{ $category->id }}" class="subnav-group max-w-[1400px] mx-auto flex gap-2.5 overflow-x-auto px-4 sm:px-8 py-3 no-scrollbar items-center justify-start {{ $loop->first ? '' : 'hidden' }}">
                        @foreach($category->children as $child)
                            <button onclick="scrollToElement('subcat-{{ $child->id }}')" class="flex-shrink-0 px-4 py-2 rounded-xl bg-white text-gray-600 text-[13px] font-bold shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-gray-200 hover:text-[#25D366] hover:border-[#25D366] transition-colors whitespace-nowrap">
                                {{ $child->name }}
                            </button>
                        @endforeach
                    </div>
                @endif
            @endforeach
            @if($categories->first() && $categories->first()->children->count() == 0)
                <div id="subnav-empty-placeholder" class="subnav-group max-w-[1400px] mx-auto text-xs text-gray-400 italic font-medium px-4 sm:px-8 py-3">No subcategories</div>
            @endif
        </div>


    <!-- Main Content & Sidebar -->
    <main class="max-w-[1400px] mx-auto px-4 sm:px-8 py-8 w-full flex flex-col lg:flex-row gap-8">
        
        <!-- Left Column: Products -->
        <div class="flex-1">
            <!-- Search Bar -->
            <div class="mb-6 relative w-full group max-w-2xl">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#25D366] transition-colors" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" id="searchInput" class="block w-full pl-11 pr-4 py-3.5 border border-gray-200 rounded-2xl bg-white placeholder-gray-400 font-medium text-gray-900 focus:outline-none focus:border-[#25D366] focus:ring-4 focus:ring-green-500/10 transition-all shadow-sm text-base" placeholder="Search for products...">
            </div>

            <div id="ajaxSearchResults" class="hidden mb-12">
                <h4 class="text-2xl font-black text-[#0f172a] mt-2 mb-6">Search Results</h4>
                <div id="ajaxSearchGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-8">
                </div>
                <div id="ajaxSearchEmpty" class="hidden text-gray-500 py-10 text-center font-medium">
                    No products found matching your search.
                </div>
            </div>

            <div id="mainProductsContainer">
            @foreach($categories as $category)
                <div id="cat-{{ $category->id }}" class="category-section mb-12">
                    @if($category->children->count() > 0)
                        @if($products->where('category_id', $category->id)->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-8 mt-2">
                                @foreach($products->where('category_id', $category->id) as $product)
                                    @include('front.partials.product-card', ['product' => $product])
                                @endforeach
                            </div>
                        @endif

                        @foreach($category->children as $child)
                            <h4 id="subcat-{{ $child->id }}" class="text-2xl font-black text-[#0f172a] mt-2 mb-6">{{ $child->name }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-8">
                                @foreach($products->where('category_id', $child->id) as $product)
                                    @include('front.partials.product-card', ['product' => $product])
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        @foreach($products->where('category_id', $category->id) as $product)
                            @include('front.partials.product-card', ['product' => $product])
                        @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
            </div>
        </div>

        <!-- Right Column: Sidebar Cart -->
        <div class="hidden lg:block w-[350px] flex-shrink-0">
            <div class="sticky top-[160px] bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden h-[calc(100vh-180px)] flex flex-col">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#25D366]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <h2 class="text-lg font-black text-[#0f172a]">Your Order <span id="desktopCartTotalQty" class="text-sm font-medium text-gray-500 ml-1"></span></h2>
                </div>
                
                <div id="desktopCartItemsContainer" class="flex-1 overflow-y-auto p-5 flex flex-col gap-4 bg-gray-50/50">
                    <div class="text-center py-10 flex flex-col items-center justify-center h-full text-gray-400">
                        <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <p class="font-bold text-gray-500 mb-1">Your cart is empty</p>
                        <p class="text-xs">Add items from the menu</p>
                    </div>
                </div>

                <div class="p-5 bg-white border-t border-gray-100">
                    <button onclick="placeOrder()" id="desktopCheckoutBtn" disabled class="w-full bg-[#25D366] text-white py-3.5 rounded-xl font-black text-[15px] hover:bg-[#128C7E] transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                        CHECKOUT
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Cart Drawer Modal -->
    <div id="cartModal" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[100] hidden transition-opacity">
        <div class="absolute inset-y-0 right-0 max-w-md w-full bg-[#f8f9fa] shadow-2xl flex flex-col animate-slide-in rounded-l-3xl overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white shadow-sm z-10">
                <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Your Cart <span id="mobileCartTotalQty" class="text-sm font-medium text-gray-500 ml-1"></span></h2>
                <button onclick="toggleCart()" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Return to store
                </button>
            </div>
            
            <div id="cartItemsContainer" class="flex-1 overflow-y-auto p-6 flex flex-col gap-4">
                <!-- Items injected by JS -->
                <div class="text-center text-gray-500 py-10" id="emptyCartMsg">Your cart is empty</div>
            </div>
            
            <div class="border-t border-gray-100 p-6 bg-white shadow-[0_-10px_30px_-10px_rgba(0,0,0,0.05)] z-10">
                <button onclick="placeOrder()" id="checkoutBtn" class="w-full py-4 bg-[#25D366] shadow-lg shadow-green-500/30 text-white rounded-2xl font-extrabold text-lg tracking-wide hover:bg-[#128C7E] hover:shadow-green-500/40 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none" disabled>
                    Place Order
                </button>
            </div>
        </div>
    </div>

    <script>
        let cart = {};

        function triggerCartPop() {
            const cartBtn = document.getElementById('cartButton');
            if (cartBtn) {
                cartBtn.classList.remove('animate-pop');
                // Trigger reflow to restart animation
                void cartBtn.offsetWidth;
                cartBtn.classList.add('animate-pop');
            }
        }

        function scrollToElement(id) {
            const el = document.getElementById(id);
            if(el) {
                // Get the total height of the sticky headers dynamically
                const headerHeight = document.querySelector('header').offsetHeight;
                const stickyBarHeight = document.getElementById('stickyNav').offsetHeight;
                const totalOffset = headerHeight + stickyBarHeight + 20; // 20px extra padding
                const y = el.getBoundingClientRect().top + window.scrollY - totalOffset;
                window.scrollTo({top: y, behavior: 'smooth'});
            }
        }

        let currentCategoryId = 'all';

        function selectCategory(id) {
            currentCategoryId = id;
            // Highlight the main category button (WhatsApp Theme)
            $('.cat-nav-btn').removeClass('active bg-[#25D366] text-white border-[#25D366]')
                             .addClass('border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50');
            
            $('#btn-cat-' + id).removeClass('border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50')
                               .addClass('active bg-[#25D366] text-white border-[#25D366]');
            
            if (id === 'all') {
                // Show all categories
                $('.subnav-group').addClass('hidden');
                $('#subnav-empty-placeholder').addClass('hidden');
                $('.category-section').removeClass('hidden');
            } else {
                // Show subcategories for the selected category
                $('.subnav-group').addClass('hidden');
                if ($('#subnav-' + id).length) {
                    $('#subnav-' + id).removeClass('hidden');
                    $('#subnav-empty-placeholder').addClass('hidden');
                } else {
                    $('#subnav-empty-placeholder').removeClass('hidden');
                }

                // Show the main category feed
                $('.category-section').addClass('hidden');
                $('#cat-' + id).removeClass('hidden');
            }

            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        function toggleCart() {
            $('#cartModal').toggleClass('hidden');
            renderCart();
        }

        function updateQty(productId, change) {
            if(cart[productId]) {
                cart[productId].quantity = parseInt(cart[productId].quantity) + parseInt(change);
                if(cart[productId].quantity <= 0) {
                    delete cart[productId];
                    // Revert to ADD button
                    $(`#addBtn-${productId}`).removeClass('hidden');
                    $(`#qtyControl-${productId}`).addClass('hidden');
                } else {
                    $(`#qty-${productId}`).text(cart[productId].quantity);
                    if(change > 0) triggerCartPop();
                }
            }
            updateCartBadge();
            renderCart();
        }

        function addToCart(productId, name, price) {
            cart[productId] = { id: productId, name: name, price: price, quantity: 1 };
            $(`#qty-${productId}`).text(1);
            
            // Switch button state
            $(`#addBtn-${productId}`).addClass('hidden');
            $(`#qtyControl-${productId}`).removeClass('hidden');
            
            updateCartBadge();
            renderCart();
            triggerCartPop();
        }

        function updateCartBadge() {
            let count = Object.keys(cart).length;
            $('#cartBadge').text(count);
            if(count > 0) {
                $('#checkoutBtn').prop('disabled', false);
                $('#desktopCheckoutBtn').prop('disabled', false);
            } else {
                $('#checkoutBtn').prop('disabled', true);
                $('#desktopCheckoutBtn').prop('disabled', true);
            }
        }

        function renderCart() {
            const container = $('#cartItemsContainer');
            const desktopContainer = $('#desktopCartItemsContainer');
            
            container.empty();
            desktopContainer.empty();
            let total = 0;
            let totalQuantity = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
            
            if (totalQuantity > 0) {
                $('#desktopCartTotalQty').text(`(${totalQuantity} items)`);
                $('#mobileCartTotalQty').text(`(${totalQuantity} items)`);
            } else {
                $('#desktopCartTotalQty').text('');
                $('#mobileCartTotalQty').text('');
            }
            
            if(Object.keys(cart).length === 0) {
                container.append('<div class="text-center text-gray-500 py-10">Your cart is empty</div>');
                desktopContainer.append(`
                    <div class="text-center py-10 flex flex-col items-center justify-center h-full text-gray-400">
                        <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <p class="font-bold text-gray-500 mb-1">Your cart is empty</p>
                        <p class="text-xs">Add items from the menu</p>
                    </div>
                `);
            } else {
                let htmlStr = '';
                Object.values(cart).forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    htmlStr += `
                        <div class="flex items-center justify-between border-b pb-4 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 leading-tight">${item.name}</h4>
                                <div class="flex items-center gap-3 mt-2">
                                    <div class="flex items-center border border-gray-200 rounded-md">
                                        <button onclick="updateQty('${item.id}', -1)" class="px-4 py-1 text-[#25D366] hover:bg-gray-50 font-bold text-lg leading-none">−</button>
                                        <span class="px-2 text-sm font-bold text-gray-900 min-w-[28px] text-center">${item.quantity}</span>
                                        <button onclick="updateQty('${item.id}', 1)" class="px-4 py-1 text-[#25D366] hover:bg-gray-50 font-bold text-lg leading-none">+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button onclick="removeFromCart(${item.id})" class="text-red-500 p-1.5 hover:bg-red-50 rounded-md transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    `;
                });
                container.append(htmlStr);
                desktopContainer.append(htmlStr);
            }
        }

        function removeFromCart(id) {
            delete cart[id];
            updateCartBadge();
            renderCart();
            
            // Revert UI to ADD button
            $(`#addBtn-${id}`).removeClass('hidden');
            $(`#qtyControl-${id}`).addClass('hidden');
        }

        $(document).ready(function() {
            let searchTimeout;
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                
                clearTimeout(searchTimeout);
                
                if (value === '') {
                    // Restore original layout
                    $('#ajaxSearchResults').addClass('hidden');
                    $('#mainProductsContainer').removeClass('hidden');
                    return;
                }
                
                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('shop.search', request()->route('uuid')) }}",
                        type: 'GET',
                        data: { query: value },
                        success: function(res) {
                            if (!res.empty) {
                                $('#mainProductsContainer').addClass('hidden');
                                $('#ajaxSearchResults').removeClass('hidden');
                                
                                if (res.count > 0) {
                                    $('#ajaxSearchGrid').html(res.html).removeClass('hidden');
                                    $('#ajaxSearchEmpty').addClass('hidden');
                                } else {
                                    $('#ajaxSearchGrid').html('').addClass('hidden');
                                    $('#ajaxSearchEmpty').removeClass('hidden');
                                }
                            }
                        }
                    });
                }, 300);
            });
        });

        function placeOrder() {
            if(Object.keys(cart).length === 0) return;
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to place this order?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#25D366',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, place it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = $('#checkoutBtn');
                    const desktopBtn = $('#desktopCheckoutBtn');
                    btn.prop('disabled', true).text('Processing...');
                    desktopBtn.prop('disabled', true).text('Processing...');
                    
                    $.ajax({
                        url: "{{ route('shop.place-order', request()->route('uuid')) }}",
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            cart: Object.values(cart)
                        },
                        success: function(res) {
                            if(res.success) {
                                window.location.href = res.redirect_url;
                            }
                        },
                        error: function(err) {
                            Swal.fire(
                                'Error!',
                                'Failed to place order. Please try again.',
                                'error'
                            );
                            btn.prop('disabled', false).text('Place Order');
                            desktopBtn.prop('disabled', false).text('CHECKOUT');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
