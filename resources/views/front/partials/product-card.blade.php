<div class="product-card-wrapper bg-white rounded-[16px] shadow-sm border border-gray-100 p-4 flex gap-4 hover:shadow-md transition-all duration-200">
    <!-- Left: Info -->
    <div class="flex-1 flex flex-col justify-between">
        <div>
            <h4 class="product-title font-bold text-gray-900 text-[16px] leading-tight mb-1">{{ $product->name }}</h4>
            @if(!empty(trim($product->description)))
                <p id="desc-{{ $product->id }}" class="text-[13px] text-gray-500 line-clamp-2 leading-snug mb-1 transition-all duration-300">{{ $product->description }}</p>
                @if(strlen(trim($product->description)) > 60)
                    <button type="button" onclick="let d = document.getElementById('desc-{{ $product->id }}'); d.classList.toggle('line-clamp-2'); this.innerText = d.classList.contains('line-clamp-2') ? 'Read More' : 'Read Less';" class="text-[#25D366] text-[11px] font-bold tracking-wider mb-2 inline-block hover:underline">Read More</button>
                @endif
            @endif
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-2 mt-auto">
            <!-- Add State -->
            <button id="addBtn-{{ $product->id }}" onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})" class="px-6 py-1.5 bg-white text-[#00a651] border border-[#00a651] rounded-md font-bold text-[13px] hover:bg-[#00a651] hover:text-white transition-colors">
                ADD
            </button>
            
            <!-- Quantity State (Hidden initially) -->
            <div id="qtyControl-{{ $product->id }}" class="hidden w-[90px] bg-white border border-gray-200 rounded-md flex items-center justify-between overflow-hidden">
                <button onclick="updateQty({{ $product->id }}, -1)" class="w-1/3 py-1.5 text-[#25D366] font-bold hover:bg-gray-50 text-lg leading-none active:bg-gray-100 transition-colors">−</button>
                <span id="qty-{{ $product->id }}" class="w-1/3 text-center text-gray-900 font-bold text-[13px]">1</span>
                <button onclick="updateQty({{ $product->id }}, 1)" class="w-1/3 py-1.5 text-[#25D366] font-bold hover:bg-gray-50 text-lg leading-none active:bg-gray-100 transition-colors">+</button>
            </div>
        </div>
    </div>
    
    <!-- Right: Image -->
    <div class="w-[110px] h-[110px] flex-shrink-0 relative">
        @if($product->image)
            <div class="w-full h-full rounded-xl overflow-hidden relative z-0">
                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            </div>
        @else
            <div class="w-full h-full rounded-xl bg-gray-100 border border-gray-200 relative z-0 flex flex-col items-center justify-center">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        @endif
    </div>
</div>
