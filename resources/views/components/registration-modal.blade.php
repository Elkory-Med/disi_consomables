@props(['show' => false])

<div x-data="{ show: @js($show) }"
     x-show="show"
     x-on:registration-success.window="show = true"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;"
     role="dialog"
     aria-modal="true">
    
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <!-- Modal panel -->
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
             @click.away="show = false">
            
            <!-- Modal content -->
            <div>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">
                        Inscription Réussie
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Votre compte a été créé avec succès ! Cependant, vous ne pourrez pas passer des commandes tant que votre compte n'est pas approuvé par l'administrateur. Vous recevrez un email dès que votre compte sera approuvé.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Modal actions -->
            <div class="mt-5 sm:mt-6">
                <button type="button"
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                        @click="show = false">
                    Compris
                </button>
            </div>
        </div>
    </div>
</div>
