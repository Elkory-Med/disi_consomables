<div class='px-4 sm:px-6 md:px-8 lg:px-12 py-6 md:py-10 max-w-7xl mx-auto'>
    <div class="relative z-10">
        <!-- Brand New  -->
        <div class="mb-8 md:mb-12">
            @include('components.navigation.view-all',[
                'Category' => 'Nouveaux'
            ])
            <livewire:product-listing :category_id="0" :current_product_id="0"/>
        </div>

        <!-- Matériel informatique  -->
        <div class="mb-8 md:mb-12">
            @include('components.navigation.view-all',[
                'Category' => 'Matériel informatique'
            ])
            @php
                $materielId = \App\Models\Category::where('name', 'Matériel informatique')->first()->id ?? 1;
            @endphp
            <livewire:product-listing :category_id="$materielId" :current_product_id="0"/>
        </div>

        <!-- Logiciels  -->
        <div class="mb-8 md:mb-12">
            @include('components.navigation.view-all',[
                'Category' => 'Logiciels'
            ])
            @php
                $logicielsId = \App\Models\Category::where('name', 'Logiciels')->first()->id ?? 3;
            @endphp
            <livewire:product-listing :category_id="$logicielsId" :current_product_id="0"/>
        </div>
    </div>
</div>