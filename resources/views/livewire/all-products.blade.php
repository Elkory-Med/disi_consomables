<div class='px-10 md:px-20 sm:px-30 py-3'>
    <h3 class='font-medium text-[10px] my-3'>TOUS LES PRODUITS</h3>
    
    @php
        $all = 'all';
    @endphp
    <livewire:product-listing :category_id="$all" :current_product_id="0"/>
</div>
