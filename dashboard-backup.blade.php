<div class="admin-dashboard-content-wrapper">
@php
$isLoading = $isLoading ?? false;
@endphp

<!-- Loading overlay -->
<div wire:loading class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
        <p class="mt-2 text-center text-gray-700">Chargement des données...</p>
    </div>
</div>

<!-- Error alerts -->
<div id="dashboard-error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
    <strong class="font-bold">Erreur!</strong>
    <span class="block sm:inline" id="error-message"></span>
</div>

<!-- Debug info section -->
<div class="bg-gray-100 p-4 mb-4 rounded-lg">
    <h3 class="text-lg font-semibold mb-2">Debug Info:</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <p><span class="font-medium">ApexCharts available:</span> <span id="apexcharts-status">Checking...</span></p>
        </div>
        <div>
            <p><span class="font-medium">Livewire connected:</span> <span id="livewire-status">Checking...</span></p>
        </div>
        <div>
            <p><span class="font-medium">Data retrieved:</span> <span id="data-status">Waiting...</span></p>
        </div>
    </div>
    
    <!-- Livewire Status Section -->
    <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
        <h4 class="font-medium text-blue-800 mb-2">Livewire Component State:</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="mb-1"><span class="font-medium">Test Property:</span> <span class="font-bold text-blue-600">{{ $testProperty ?? 'Property not available' }}</span></p>
                <p class="mb-1"><span class="font-medium">Counter:</span> <span class="font-bold text-blue-600">{{ $counter ?? 'Not available' }}</span></p>
                <p class="mb-1"><span class="font-medium">Last Update:</span> <span class="font-bold text-blue-600">{{ isset($lastUpdateTime) && $lastUpdateTime ? $lastUpdateTime->format('H:i:s') : 'Never' }}</span></p>
            </div>
            <div>
                <p class="mb-1"><span class="font-medium">Data Source:</span> <span class="font-bold text-blue-600">{{ isset($dashboardData['dataSource']) ? $dashboardData['dataSource'] : 'None' }}</span></p>
                <p class="mb-1"><span class="font-medium">Is Loading:</span> <span class="font-bold text-blue-600">{{ $isLoading ? 'Yes' : 'No' }}</span></p>
                <p class="mb-1"><span class="font-medium">Has Error:</span> <span class="font-bold text-blue-600">{{ !empty($errorMessage) ? 'Yes: '.$errorMessage : 'No' }}</span></p>
            </div>
        </div>
        <div class="mt-3">
            <button wire:click="refresh" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                Refresh Component
            </button>
        </div>
    </div>
    
    <!-- Test Buttons -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3">
        <button 
            onclick="Livewire.emit('refreshDashboard')"
            class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-sm">
            Emit Event
        </button>
        
        <button 
            wire:click="test"
            class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm">
            Test Method
        </button>
        
        <button 
            wire:click="sayHello"
            class="bg-purple-500 hover:bg-purple-600 text-white px-2 py-1 rounded text-sm">
            Say Hello
        </button>
        
        <button 
            id="js-test-button"
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-sm">
            JS Test
        </button>
        
        <button 
            onclick="logChartData()"
            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm col-span-2">
            Log Current Chart Data
        </button>
    </div>
    
    <div class="mt-2">
        <details>
            <summary class="cursor-pointer text-blue-600">View data structure</summary>
            <pre id="data-structure" class="bg-gray-800 text-white p-4 rounded mt-2 overflow-auto max-h-96 text-xs"></pre>
        </details>
    </div>
</div>

<!-- Dashboard header -->
<div class="flex justify-between items-center mb-6">
    <div class="flex items-center">
        <span class="text-sm text-gray-500 mr-2">Dernière mise à jour: {{ isset($lastUpdateTime) && $lastUpdateTime ? $lastUpdateTime->format('d/m/Y H:i:s') : 'Jamais' }}</span>
        <button 
            wire:click="handleRefreshEvent" 
            id="refresh-button"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Actualiser
        </button>
    </div>
</div>

<!-- Main metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Metrics cards would go here -->
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Statut des commandes</h2>
        <div id="orderStatusChart" class="h-80 border-2 border-gray-200"></div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Commandes livrées par unité</h2>
        <div id="deliveredOrdersChart" class="h-80 border-2 border-gray-200"></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Produits les plus livrés</h2>
        <div id="deliveredProductsChart" class="h-80 border-2 border-gray-200"></div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Commandes livrées par administration</h2>
        <div id="administrationChart" class="h-80 border-2 border-gray-200"></div>
        <p class="text-xs text-gray-500 mt-2">Note: Ce graphique affiche les commandes marquées comme livrées (champ "delivered" = true) groupées par administration</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4">Tendance des commandes (7 derniers jours)</h2>
    <div id="orderTrendsChart" class="h-80 border-2 border-gray-200"></div>
</div>

<!-- Hidden input for data -->
<input type="hidden" id="component-data" wire:ignore value="{{ json_encode($dashboardData ?? []) }}">

<script>
    // Force update status indicators function
    function forceUpdateStatusIndicators() {
        console.log('Forcing update of status indicators');
        
        // ApexCharts status
        const apexChartsStatus = document.getElementById('apexcharts-status');
        if (apexChartsStatus) {
            apexChartsStatus.textContent = typeof ApexCharts !== 'undefined' ? 'Yes' : 'No';
            apexChartsStatus.classList.remove('text-red-600', 'text-green-600');
            apexChartsStatus.classList.add(typeof ApexCharts !== 'undefined' ? 'text-green-600' : 'text-red-600');
        }
        
        // Livewire status
        const livewireStatus = document.getElementById('livewire-status');
        if (livewireStatus) {
            livewireStatus.textContent = typeof window.Livewire !== 'undefined' ? 'Yes' : 'No';
            livewireStatus.classList.remove('text-red-600', 'text-green-600');
            livewireStatus.classList.add(typeof window.Livewire !== 'undefined' ? 'text-green-600' : 'text-red-600');
        }
        
        // Data status
        const dataStatus = document.getElementById('data-status');
        if (dataStatus) {
            dataStatus.textContent = 'Available';
            dataStatus.classList.remove('text-red-600', 'text-yellow-600');
            dataStatus.classList.add('text-green-600');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded event triggered');
        
        // Debug Livewire component ID
        const wireElements = document.querySelectorAll('[wire\\:id]');
        console.log('Found Livewire elements:', wireElements.length);
        wireElements.forEach(el => {
            console.log('Livewire component ID:', el.getAttribute('wire:id'));
        });
        
        initializeDashboard();
        
        // Force update after a short delay
        setTimeout(forceUpdateStatusIndicators, 500);
        // And again after a longer delay to be sure
        setTimeout(forceUpdateStatusIndicators, 1500);
        
        // JS Test button
        document.getElementById('js-test-button').addEventListener('click', function() {
            console.log('JS Test button clicked');
            if (typeof window.Livewire !== 'undefined') {
                try {
                    // Check for Livewire version and use appropriate method
                    if (typeof window.Livewire.dispatch === 'function') {
                        // Livewire 3
                        window.Livewire.dispatch('ping');
                        console.log('Dispatched ping event (Livewire 3)');
                        
                        // Try to find the component and call the method directly
                        const wireEl = document.querySelector('[wire\\:id]');
                        if (wireEl) {
                            const component = Livewire.find(wireEl.getAttribute('wire:id'));
                            if (component) {
                                console.log('Found Livewire component, calling getChartData method directly');
                                component.call('getChartData');
                                return;
                            }
                        }
                        
                        // Fallback to event
                        window.Livewire.dispatch('refreshDashboard');
                        console.log('Dispatched refreshDashboard event (Livewire 3)');
                    } else if (typeof window.Livewire.emit === 'function') {
                        // Livewire 2
                        window.Livewire.emit('ping');
                        console.log('Emitted ping event (Livewire 2)');
                        window.Livewire.emit('refreshDashboard');
                        console.log('Emitted refreshDashboard event (Livewire 2)');
                    } else {
                        console.error('Neither Livewire.emit nor Livewire.dispatch is available');
                    }
                } catch (e) {
                    console.error('Error triggering Livewire events:', e);
                }
            } else {
                console.error('Livewire not available');
            }
        });
    });
            
    // Global variables
    let charts = {};
    window.chartsInitialized = false;
    let realDataReceived = false; // Flag to track if real data was received
    
    // Test response handler
    window.addEventListener('test-response', function(event) {
        console.log('Received test-response event:', event.detail);
        forceUpdateStatusIndicators();
        alert('Test response received: ' + event.detail.message);
    });
    
    // Event listeners for browser events
    window.addEventListener('dashboardDataUpdated', function(event) {
        console.log('Received browser event dashboardDataUpdated', event.detail);
        forceUpdateStatusIndicators();
        
        if (event.detail && (event.detail.isRealData === true || event.detail.dataSource === 'database-simple')) {
            console.log('Real data received from source:', event.detail.dataSource);
            realDataReceived = true;
        } else if (event.detail && event.detail.isFallbackData === true) {
            console.log('Fallback data received from source:', event.detail.dataSource);
            // Only use fallback if no real data was received yet
            if (!realDataReceived) {
                renderDashboardCharts(event.detail);
            } else {
                console.log('Ignoring fallback data because real data was already received');
                return;
            }
        } else {
            console.log('Data received without clear source flags');
        }
        
        renderDashboardCharts(event.detail);
    });
    
    // Event listeners for Livewire events
    if (typeof window.Livewire !== 'undefined') {
        console.log('Setting up Livewire event listeners');
        
        try {
            // Determine Livewire version and use appropriate methods
            const isLivewire3 = typeof window.Livewire.dispatch === 'function';
            console.log('Detected Livewire version:', isLivewire3 ? '3.x' : '2.x');
            
            if (isLivewire3) {
                // Livewire 3.x - Use the modern event API
                window.Livewire.on('dashboardDataUpdated', (data) => {
                    console.log('Received dashboardDataUpdated event via Livewire.on (Livewire 3)', data);
                    forceUpdateStatusIndicators();
                    
                    if (data && (data.isRealData === true || data.dataSource === 'database-simple')) {
                        console.log('Real data received from Livewire event');
                        realDataReceived = true;
                    }
                    
                    renderDashboardCharts(data);
                });
                
                window.Livewire.on('dashboard-refreshed', () => {
                    console.log('Dashboard refreshed event received');
                    forceUpdateStatusIndicators();
                });
                
                window.Livewire.on('test-response', (data) => {
                    console.log('Test response received:', data);
                    forceUpdateStatusIndicators();
                    alert('Test response: ' + (data.message || 'No message'));
                });
                
                document.addEventListener('livewire:initialized', () => {
                    console.log('Livewire 3 initialized via document event');
                    forceUpdateStatusIndicators();
                });
            } else {
                // Livewire 2.x
                window.Livewire.on('dashboardDataUpdated', function(data) {
                    console.log('Received Livewire dashboardDataUpdated event', data);
                    
                    if (data && (data.isRealData === true || data.dataSource === 'database-simple')) {
                        console.log('Real data received from Livewire event');
                        realDataReceived = true;
                    }
                    
                    renderDashboardCharts(data);
                });
                
                window.Livewire.on('dashboardError', function(message) {
                    console.error('Dashboard error:', message);
                    const errorDiv = document.getElementById('dashboard-error');
                    const errorMessage = document.getElementById('error-message');
                    errorMessage.textContent = message;
                    errorDiv.classList.remove('hidden');
                });
                
                // Test Livewire connection using emit
                window.Livewire.emit('ping');
                console.log('Sent ping to test Livewire connection (Livewire 2)');
            }
        } catch (e) {
            console.error('Error setting up Livewire event listeners:', e);
        }
    } else {
        console.warn('Livewire object not available for event listeners');
        
        // Check for Livewire again after a delay
        setTimeout(function() {
            if (typeof window.Livewire !== 'undefined') {
                console.log('Livewire found after delay, setting up listeners');
                
                try {
                    // Determine Livewire version
                    const isLivewire3 = typeof window.Livewire.dispatch === 'function';
                    console.log('Detected Livewire version after delay:', isLivewire3 ? '3.x' : '2.x');
                    
                    if (isLivewire3) {
                        // For Livewire 3, try using the modern API
                        try {
                            Livewire.on('dashboardDataUpdated', (data) => {
                                console.log('Received delayed dashboardDataUpdated event (Livewire 3)', data);
                                
                                if (data && (data.isRealData === true || data.dataSource === 'database-simple')) {
                                    console.log('Real data received from delayed Livewire event');
                                    realDataReceived = true;
                                }
                                
                                renderDashboardCharts(data);
                            });
                            
                            // Try to find the component and call the method directly
                            const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                            if (component) {
                                console.log('Found Livewire component after delay, calling getChartData method directly');
                                component.call('getChartData');
                            } else {
                                console.log('Could not find Livewire component after delay, using dispatch');
                                window.Livewire.dispatch('refreshDashboard');
                            }
                        } catch (e) {
                            console.error('Error with Livewire 3 delayed setup:', e);
                            // Fallback to dispatch directly
                            try {
                                window.Livewire.dispatch('refreshDashboard');
                                console.log('Dispatched delayed refreshDashboard event as fallback');
                            } catch (dispatchError) {
                                console.error('Error dispatching delayed event:', dispatchError);
                            }
                        }
                    } else {
                        // For Livewire 2, use the legacy API
                        window.Livewire.on('dashboardDataUpdated', function(data) {
                            console.log('Received delayed dashboardDataUpdated event (Livewire 2)', data);
                            
                            if (data && (data.isRealData === true || data.dataSource === 'database-simple')) {
                                console.log('Real data received from delayed Livewire event');
                                realDataReceived = true;
                            }
                            
                            renderDashboardCharts(data);
                        });
                        
                        window.Livewire.emit('ping');
                        window.Livewire.emit('refreshDashboard');
                        console.log('Sent delayed events (Livewire 2)');
                    }
                } catch (e) {
                    console.error('Error setting up delayed Livewire event listeners:', e);
                }
            }
        }, 1000);
    }
    
    function initializeDashboard() {
        console.log('Initializing dashboard...');
        
        // Force update status indicators directly
        forceUpdateStatusIndicators();
        
        // Check ApexCharts
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts library is not available - charts cannot be rendered');
            
            // Show error message
            const errorDiv = document.getElementById('dashboard-error');
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = "ApexCharts not found. Please check your JavaScript includes.";
            errorDiv.classList.remove('hidden');
            return;
        }
        
        // Check Livewire
        if (typeof window.Livewire === 'undefined') {
            console.warn('Livewire is not available - falling back to direct data');
            return;
        }
        
        try {
            // Determine Livewire version
            const isLivewire3 = typeof window.Livewire.dispatch === 'function';
            console.log('Detected Livewire version in initialization:', isLivewire3 ? '3.x' : '2.x');
            
            // Check if we already have a component-data
            const dataElement = document.getElementById('component-data');
            if (dataElement && dataElement.value && dataElement.value !== '[]' && dataElement.value !== '{}') {
                try {
                    const data = JSON.parse(dataElement.value);
                    console.log('Found data in hidden input', data);
                    if (data && Object.keys(data).length > 0) {
                        if (data.isRealData === true || data.dataSource === 'database' || data.dataSource === 'database-simple') {
                            console.log('Real data found in hidden input');
                            realDataReceived = true;
                        }
                        renderDashboardCharts(data);
                        return;
                    }
                } catch (error) {
                    console.error('Error parsing data from hidden input', error);
                }
            }
            
            console.log('Requesting fresh data via Livewire...');
            
            if (isLivewire3) {
                // For Livewire 3, try multiple approaches
                try {
                    // First try to get the component and call the method directly
                    const wireEl = document.querySelector('[wire\\:id]');
                    if (wireEl) {
                        const component = Livewire.find(wireEl.getAttribute('wire:id'));
                        if (component) {
                            console.log('Found Livewire component, calling getChartData method directly');
                            component.call('getChartData');
                            return;
                        }
                    }
                    
                    // If that fails, try dispatch with the event name that's handled in the component
                    console.log('No component found, trying dispatch for event');
                    window.Livewire.dispatch('refreshDashboard');
                    console.log('Dispatched refreshDashboard event (Livewire 3)');
                } catch (e) {
                    console.error('Error triggering Livewire 3 refresh:', e);
                    
                    // As a last resort, try with a different approach
                    try {
                        document.dispatchEvent(new CustomEvent('refresh-dashboard'));
                        console.log('Dispatched custom event as fallback');
                    } catch (customError) {
                        console.error('Error dispatching custom event:', customError);
                    }
                }
            } else {
                // For Livewire 2, use emit
                try {
                    window.Livewire.emit('refreshDashboard');
                    console.log('Emitted refreshDashboard event (Livewire 2)');
                } catch (e) {
                    console.error('Error triggering Livewire 2 refresh:', e);
                }
            }
        } catch (error) {
            console.error('Error in Livewire initialization:', error);
        }
        
        // Only show fallback data as a last resort
        setTimeout(function() {
            if (!realDataReceived && !window.chartsInitialized) {
                console.warn('No real data received after timeout, showing fallback data');
                showFallbackData();
            }
        }, 5000); // Increased to 5 seconds to give more time for real data
    }
    
    function showFallbackData() {
        // Only show fallback if no real data was received
        if (realDataReceived) {
            console.log('Real data was already received, not showing fallback data');
            return;
        }
        
        console.log('Showing fallback sample data');
        const fallbackData = {
            'orderStatus': {
                'labels': ['En cours', 'Livrée', 'Annulée'],
                'series': [3, 5, 2]
            },
            'deliveredOrders': {
                'labels': ['Unité 1', 'Unité 2', 'Unité 3'],
                'series': [4, 6, 2]
            },
            'deliveredProducts': {
                'labels': ['Produit 1', 'Produit 2', 'Produit 3'],
                'series': [10, 8, 5]
            },
            'productDistribution': {
                'labels': ['Catégorie 1', 'Catégorie 2', 'Catégorie 3'],
                'series': [
                    {
                        'name': 'Produits',
                        'data': [12, 8, 16]
                    }
                ]
            },
            'orderTrends': {
                'labels': ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                'series': [
                    {
                        'name': 'Total',
                        'data': [10, 12, 9, 14, 15, 8, 13]
                    },
                    {
                        'name': 'Livrées',
                        'data': [8, 9, 6, 10, 12, 5, 10]
                    }
                ]
            },
            'isFallbackData': true,
            'dataSource': 'javascript-fallback',
            'randomId': Math.floor(Math.random() * 9000) + 1000,
            'timestamp': Date.now()
        };
        
        const dataStatus = document.getElementById('data-status');
        dataStatus.textContent = 'Yes (fallback data)';
        dataStatus.classList.add('text-yellow-600');
        
        const errorDiv = document.getElementById('dashboard-error');
        const errorMessage = document.getElementById('error-message');
        errorMessage.textContent = "Utilisation de données de secours pour l'affichage des graphiques. Les données réelles n'ont pas pu être chargées.";
        errorDiv.classList.remove('hidden');
        errorDiv.classList.add('bg-yellow-100', 'border-yellow-400', 'text-yellow-700');
        
        renderDashboardCharts(fallbackData);
    }
    
    function renderDashboardCharts(data) {
        if (!data || Object.keys(data).length === 0) {
            console.error('Cannot render charts with empty data');
            return;
        }
        
        console.log('Rendering dashboard charts with data from source:', data.dataSource || 'unknown');
        
        // Force update status indicators
        forceUpdateStatusIndicators();
        
        // Clear any error messages if this is real data
        const errorDiv = document.getElementById('dashboard-error');
        if (!errorDiv.classList.contains('hidden') && 
            (data.isRealData === true || data.dataSource === 'database-simple')) {
            console.log('Hiding error message because real data was received');
            errorDiv.classList.add('hidden');
        }
        
        // Update data status and structure
        const dataStatus = document.getElementById('data-status');
        const dataStructure = document.getElementById('data-structure');
        
        if (data.isRealData === true || data.dataSource === 'database-simple') {
            dataStatus.textContent = 'Yes (database)';
            dataStatus.classList.remove('text-red-600', 'text-yellow-600');
            dataStatus.classList.add('text-green-600');
        } else if (data.isFallbackData === true) {
            dataStatus.textContent = 'Yes (fallback)';
            dataStatus.classList.remove('text-red-600', 'text-green-600');
            dataStatus.classList.add('text-yellow-600');
        } else {
            dataStatus.textContent = 'Yes';
            dataStatus.classList.remove('text-red-600', 'text-yellow-600');
            dataStatus.classList.add('text-green-600');
        }
        
        dataStructure.textContent = JSON.stringify(data, null, 2);
        
        // Track if the administration chart was rendered with the new implementation
        let administrationChartRendered = false;
        
        // Admin Distribution Chart - New Implementation (we process this first)
        if (data.administrationStats) {
            console.log('Rendering administration chart with new data:', data.administrationStats);
            administrationChartRendered = true;
            
            // Check if we have valid data
            if (data.administrationStats.isEmpty !== true && 
                data.administrationStats.labels && 
                data.administrationStats.labels.length > 0 &&
                data.administrationStats.labels[0] !== 'Aucune donnée' &&
                data.administrationStats.labels[0] !== 'Erreur de chargement') {
                
                // Get the data in the right format
                let labels = data.administrationStats.labels;
                let series = [];
                
                // Filter out user roles and statuses from labels
                const filterLabels = ['user', 'admin', 'utilisateur', 'administrateur', 'en attente', 'rejeté', 'pending', 'approved', 'rejected', 'utilisateurs', 'administrateurs', 'en_attente', 'rejetés', 'attente', 'rejet', 'null', 'undefined'];
                const filteredLabels = [];
                const filteredSeries = [];
                
                // Extract series data based on the structure
                if (data.administrationStats.series && 
                    Array.isArray(data.administrationStats.series) && 
                    data.administrationStats.series.length > 0) {
                    
                    if (Array.isArray(data.administrationStats.series[0].data)) {
                        series = data.administrationStats.series[0].data;
                    } else {
                        series = data.administrationStats.series.map(item => 
                            typeof item === 'object' ? item.data : item
                        ).flat();
                    }
                }
                
                // Log raw data to help debugging
<div class="admin-dashboard-content-wrapper">
@php
$isLoading = $isLoading ?? false;
@endphp

<!-- Loading overlay -->
<div wire:loading class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
        <p class="mt-2 text-center text-gray-700">Chargement des données...</p>
    </div>
</div>

<!-- Error alerts -->
<div id="dashboard-error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
    <strong class="font-bold">Erreur!</strong>
    <span class="block sm:inline" id="error-message"></span>
</div>

<!-- Debug info section -->
<div class="bg-gray-100 p-4 mb-4 rounded-lg">
    <h3 class="text-lg font-semibold mb-2">Debug Info:</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <p><span class="font-medium">ApexCharts available:</span> <span id="apexcharts-status">Checking...</span></p>
        </div>
        <div>
            <p><span class="font-medium">Livewire connected:</span> <span id="livewire-status">Checking...</span></p>
        </div>
        <div>
            <p><span class="font-medium">Data retrieved:</span> <span id="data-status">Waiting...</span></p>
        </div>
    </div>
    
    <!-- Livewire Status Section -->
    <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
        <h4 class="font-medium text-blue-800 mb-2">Livewire Component State:</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="mb-1"><span class="font-medium">Test Property:</span> <span class="font-bold text-blue-600">{{ $testProperty ?? 'Property not available' }}</span></p>
                <p class="mb-1"><span class="font-medium">Counter:</span> <span class="font-bold text-blue-600">{{ $counter ?? 'Not available' }}</span></p>
                <p class="mb-1"><span class="font-medium">Last Update:</span> <span class="font-bold text-blue-600">{{ isset($lastUpdateTime) && $lastUpdateTime ? $lastUpdateTime->format('H:i:s') : 'Never' }}</span></p>
            </div>
            <div>
                <p class="mb-1"><span class="font-medium">Data Source:</span> <span class="font-bold text-blue-600">{{ isset($dashboardData['dataSource']) ? $dashboardData['dataSource'] : 'None' }}</span></p>
                <p class="mb-1"><span class="font-medium">Is Loading:</span> <span class="font-bold text-blue-600">{{ $isLoading ? 'Yes' : 'No' }}</span></p>
                <p class="mb-1"><span class="font-medium">Has Error:</span> <span class="font-bold text-blue-600">{{ !empty($errorMessage) ? 'Yes: '.$errorMessage : 'No' }}</span></p>
            </div>
        </div>
        <div class="mt-3">
            <button wire:click="refresh" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                Refresh Component
            </button>
        </div>
    </div>
    
    <!-- Test Buttons -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3">
        <button 
            onclick="Livewire.emit('refreshDashboard')"
            class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-sm">
            Emit Event
        </button>
        
        <button 
            wire:click="test"
            class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm">
            Test Method
        </button>
        
        <button 
            wire:click="sayHello"
            class="bg-purple-500 hover:bg-purple-600 text-white px-2 py-1 rounded text-sm">
            Say Hello
        </button>
        
        <button 
            id="js-test-button"
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-sm">
            JS Test
        </button>
        
        <button 
            onclick="logChartData()"
            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm col-span-2">
            Log Current Chart Data
        </button>
    </div>
    
    <div class="mt-2">
        <details>
            <summary class="cursor-pointer text-blue-600">View data structure</summary>
            <pre id="data-structure" class="bg-gray-800 text-white p-4 rounded mt-2 overflow-auto max-h-96 text-xs"></pre>
        </details>
    </div>
</div>

<!-- Dashboard header -->
<div class="flex justify-between items-center mb-6">
    <div class="flex items-center">
        <span class="text-sm text-gray-500 mr-2">Dernière mise à jour: {{ isset($lastUpdateTime) && $lastUpdateTime ? $lastUpdateTime->format('d/m/Y H:i:s') : 'Jamais' }}</span>
        <button 
            wire:click="handleRefreshEvent" 
            id="refresh-button"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Actualiser
        </button>
    </div>
</div>

<!-- Main metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Metrics cards would go here -->
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Statut des commandes</h2>
        <div id="orderStatusChart" class="h-80 border-2 border-gray-200"></div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Commandes livrées par unité</h2>
        <div id="deliveredOrdersChart" class="h-80 border-2 border-gray-200"></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Produits les plus livrés</h2>
        <div id="deliveredProductsChart" class="h-80 border-2 border-gray-200"></div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Commandes livrées par administration</h2>
        <div id="administrationChart" class="h-80 border-2 border-gray-200"></div>
        <p class="text-xs text-gray-500 mt-2">Note: Ce graphique affiche les commandes marquées comme livrées (champ "delivered" = true) groupées par administration</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4">Tendance des commandes (7 derniers jours)</h2>
    <div id="orderTrendsChart" class="h-80 border-2 border-gray-200"></div>
</div>

<!-- Hidden input for data -->
<input type="hidden" id="component-data" wire:ignore value="{{ json_encode($dashboardData ?? []) }}">

<script>
    // Force update status indicators function
    function forceUpdateStatusIndicators() {
        console.log('Forcing update of status indicators');
        
        // ApexCharts status
        const apexChartsStatus = document.getElementById('apexcharts-status');
        if (apexChartsStatus) {
            apexChartsStatus.textContent = typeof ApexCharts !== 'undefined' ? 'Yes' : 'No';
            apexChartsStatus.classList.remove('text-red-600', 'text-green-600');
            apexChartsStatus.classList.add(typeof ApexCharts !== 'undefined' ? 'text-green-600' : 'text-red-600');
        }
        
        // Livewire status
        const livewireStatus = document.getElementById('livewire-status');
        if (livewireStatus) {
            livewireStatus.textContent = typeof window.Livewire !== 'undefined' ? 'Yes' : 'No';
            livewireStatus.classList.remove('text-red-600', 'text-green-600');
            livewireStatus.classList.add(typeof window.Livewire !== 'undefined' ? 'text-green-600' : 'text-red-600');
        }
        
        // Data status
        const dataStatus = document.getElementById('data-status');
        if (dataStatus) {
            dataStatus.textContent = 'Available';
            dataStatus.classList.remove('text-red-600', 'text-yellow-600');
            dataStatus.classList.add('text-green-600');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded event triggered');
        
        // Debug Livewire component ID
        const wireElements = document.querySelectorAll('[wire\\:id]');
        console.log('Found Livewire elements:', wireElements.length);
        wireElements.forEach(el => {
            console.log('Livewire component ID:', el.getAttribute('wire:id'));
        });
        
        initializeDashboard();
        
        // Force update after a short delay
        setTimeout(forceUpdateStatusIndicators, 500);
        // And again after a longer delay to be sure
        setTimeout(forceUpdateStatusIndicators, 1500);
        
        // JS Test button
        document.getElementById('js-test-button').addEventListener('click', function() {
            console.log('JS Test button clicked');
            if (typeof window.Livewire !== 'undefined') {
                try {
                    // Check for Livewire version and use appropriate method
                    if (typeof window.Livewire.dispatch === 'function') {
                        // Livewire 3
                        window.Livewire.dispatch('ping');
                        console.log('Dispatched ping event (Livewire 3)');
                        
                        // Try to find the component and call the method directly
                        const wireEl = document.querySelector('[wire\\:id]');
                        if (wireEl) {
                            const component = Livewire.find(wireEl.getAttribute('wire:id'));
                            if (component) {
                                console.log('Found Livewire component, calling getChartData method directly');
                                component.call('getChartData');
                                return;
                            }
                        }
                        
                        // Fallback to event
                        window.Livewire.dispatch('refreshDashboard');
                        console.log('Dispatched refreshDashboard event (Livewire 3)');
                    } else if (typeof window.Livewire.emit === 'function') {
                        // Livewire 2
                        window.Livewire.emit('ping');
                        console.log('Emitted ping event (Livewire 2)');
                        window.Livewire.emit('refreshDashboard');
                        console.log('Emitted refreshDashboard event (Livewire 2)');
                    } else {
                        console.error('Neither Livewire.emit nor Livewire.dispatch is available');
                    }
                } catch (e) {
                    console.error('Error triggering Livewire events:', e);
                }
            } else {
                console.error('Livewire not available');
            }
        });
    });
            
    // Global variables
    let charts = {};
    window.chartsInitialized = false;
    let realDataReceived = false; // Flag to track if real data was received
    
    // Test response handler
    window.addEventListener('test-response', function(event) {
        console.log('Received test-response event:', event.detail);
        forceUpdateStatusIndicators();
        alert('Test response received: ' + event.detail.message);
    });
    
    // Event listeners for browser events
    window.addEventListener('dashboardDataUpdated', function(event) {
        console.log('Received browser event dashboardDataUpdated', event.detail);
        forceUpdateStatusIndicators();
        
        if (event.detail && (event.detail.isRealData === true || event.detail.dataSource === 'database-simple')) {
            console.log('Real data received from source:', event.detail.dataSource);
            realDataReceived = true;
        } else if (event.detail && event.detail.isFallbackData === true) {
            console.log('Fallback data received from source:', event.detail.dataSource);
            // Only use fallback if no real data was received yet
            if (!realDataReceived) {
                renderDashboardCharts(event.detail);
            } else {
                console.log('Ignoring fallback data because real data was already received');
                return;
            }
        } else {
            console.log('Data received without clear source flags');
        }
        
        renderDashboardCharts(event.detail);
    });
    
    // Event listeners for Livewire events
    if (typeof window.Livewire !== 'undefined') {
        console.log('Setting up Livewire event listeners');
        
        try {
            // Determine Livewire version and use appropriate methods
            const isLivewire3 = typeof window.Livewire.dispatch === 'function';
            console.log('Detected Livewire version:', isLivewire3 ? '3.x' : '2.x');
            
            if (isLivewire3) {
                // Livewire 3.x - Use the modern event API
                window.Livewire.on('dashboardDataUpdated', (data) => {
                    console.log('Received dashboardDataUpdated event via Livewire.on (Livewire 3)', data);
                    forceUpdateStatusIndicators();
                    
                    if (data && (data.isRealData === true || data.dataSource === 'database-simple')) {
                        console.log('Real data received from Livewire event');
                        realDataReceived = true;
                    }
                    
                    renderDashboardCharts(data);
                });
                
                window.Livewire.on('dashboard-refreshed', () => {
                    console.log('Dashboard refreshed event received');
                    forceUpdateStatusIndicators();
                });
                
                window.Livewire.on('test-response', (data) => {
                    console.log('Test response received:', data);
                    forceUpdateStatusIndicators();
                    alert('Test response: ' + (data.message || 'No message'));
                });
                
                document.addEventListener('livewire:initialized', () => {
                    console.log('Livewire 3 initialized via document event');
                    forceUpdateStatusIndicators();
                });
            } else {
                // Livewire 2.x
                window.Livewire.on('dashboardDataUpdated', function(data) {
                    console.log('Received Livewire dashboardDataUpdated event', data);
                    
                    if (data && (data.isRealData === true || data.dataSource === 'database-simple')) {
                        console.log('Real data received from Livewire event');
                        realDataReceived = true;
                    }
                    
                    renderDashboardCharts(data);
                });
                
                window.Livewire.on('dashboardError', function(message) {
                    console.error('Dashboard error:', message);
                    const errorDiv = document.getElementById('dashboard-error');
                    const errorMessage = document.getElementById('error-message');
                    errorMessage.textContent = message;
                    errorDiv.classList.remove('hidden');
                });
                
                // Test Livewire connection using emit
                window.Livewire.emit('ping');
                console.log('Sent ping to test Livewire connection (Livewire 2)');
            }
        } catch (e) {
            console.error('Error setting up Livewire event listeners:', e);
        }
    } else {
        console.warn('Livewire object not available for event listeners');
        
        // Check for Livewire again after a delay
        setTimeout(function() {
            if (typeof window.Livewire !== 'undefined') {
                console.log('Livewire found after delay, setting up listeners');
                
                try {
                    // Determine Livewire version
                    const isLivewire3 = typeof window.Livewire.dispatch === 'function';
                    console.log('Detected Livewire version after delay:', isLivewire3 ? '3.x' : '2.x');
                    
                    if (isLivewire3) {
                        // For Livewire 3, try using the modern API
                        try {
                            Livewire.on('dashboardDataUpdated', (data) => {
                                console.log('Received delayed dashboardDataUpdated event (Livewire 3)', data);
                                
                                if (data && (data.isRealData === true || data.dataSource === 'database-simple')) {
                                    console.log('Real data received from delayed Livewire event');
                                    realDataReceived = true;
                                }
                                
                                renderDashboardCharts(data);
                            });
                            
                            // Try to find the component and call the method directly
                            const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                            if (component) {
                                console.log('Found Livewire component after delay, calling getChartData method directly');
                                component.call('getChartData');
                            } else {
                                console.log('Could not find Livewire component after delay, using dispatch');
                                window.Livewire.dispatch('refreshDashboard');
                            }
                        } catch (e) {
                            console.error('Error with Livewire 3 delayed setup:', e);
                            // Fallback to dispatch directly
                            try {
                                window.Livewire.dispatch('refreshDashboard');
                                console.log('Dispatched delayed refreshDashboard event as fallback');
                            } catch (dispatchError) {
                                console.error('Error dispatching delayed event:', dispatchError);
                            }
                        }
                    } else {
                        // For Livewire 2, use the legacy API
                        window.Livewire.on('dashboardDataUpdated', function(data) {
                            console.log('Received delayed dashboardDataUpdated event (Livewire 2)', data);
                            
                            if (data && (data.isRealData === true || data.dataSource === 'database-simple')) {
                                console.log('Real data received from delayed Livewire event');
                                realDataReceived = true;
                            }
                            
                            renderDashboardCharts(data);
                        });
                        
                        window.Livewire.emit('ping');
                        window.Livewire.emit('refreshDashboard');
                        console.log('Sent delayed events (Livewire 2)');
                    }
                } catch (e) {
                    console.error('Error setting up delayed Livewire event listeners:', e);
                }
            }
        }, 1000);
    }
    
    function initializeDashboard() {
        console.log('Initializing dashboard...');
        
        // Force update status indicators directly
        forceUpdateStatusIndicators();
        
        // Check ApexCharts
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts library is not available - charts cannot be rendered');
            
            // Show error message
            const errorDiv = document.getElementById('dashboard-error');
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = "ApexCharts not found. Please check your JavaScript includes.";
            errorDiv.classList.remove('hidden');
            return;
        }
        
        // Check Livewire
        if (typeof window.Livewire === 'undefined') {
            console.warn('Livewire is not available - falling back to direct data');
            return;
        }
        
        try {
            // Determine Livewire version
            const isLivewire3 = typeof window.Livewire.dispatch === 'function';
            console.log('Detected Livewire version in initialization:', isLivewire3 ? '3.x' : '2.x');
            
            // Check if we already have a component-data
            const dataElement = document.getElementById('component-data');
            if (dataElement && dataElement.value && dataElement.value !== '[]' && dataElement.value !== '{}') {
                try {
                    const data = JSON.parse(dataElement.value);
                    console.log('Found data in hidden input', data);
                    if (data && Object.keys(data).length > 0) {
                        if (data.isRealData === true || data.dataSource === 'database' || data.dataSource === 'database-simple') {
                            console.log('Real data found in hidden input');
                            realDataReceived = true;
                        }
                        renderDashboardCharts(data);
                        return;
                    }
                } catch (error) {
                    console.error('Error parsing data from hidden input', error);
                }
            }
            
            console.log('Requesting fresh data via Livewire...');
            
            if (isLivewire3) {
                // For Livewire 3, try multiple approaches
                try {
                    // First try to get the component and call the method directly
                    const wireEl = document.querySelector('[wire\\:id]');
                    if (wireEl) {
                        const component = Livewire.find(wireEl.getAttribute('wire:id'));
                        if (component) {
                            console.log('Found Livewire component, calling getChartData method directly');
                            component.call('getChartData');
                            return;
                        }
                    }
                    
                    // If that fails, try dispatch with the event name that's handled in the component
                    console.log('No component found, trying dispatch for event');
                    window.Livewire.dispatch('refreshDashboard');
                    console.log('Dispatched refreshDashboard event (Livewire 3)');
                } catch (e) {
                    console.error('Error triggering Livewire 3 refresh:', e);
                    
                    // As a last resort, try with a different approach
                    try {
                        document.dispatchEvent(new CustomEvent('refresh-dashboard'));
                        console.log('Dispatched custom event as fallback');
                    } catch (customError) {
                        console.error('Error dispatching custom event:', customError);
                    }
                }
            } else {
                // For Livewire 2, use emit
                try {
                    window.Livewire.emit('refreshDashboard');
                    console.log('Emitted refreshDashboard event (Livewire 2)');
                } catch (e) {
                    console.error('Error triggering Livewire 2 refresh:', e);
                }
            }
        } catch (error) {
            console.error('Error in Livewire initialization:', error);
        }
        
        // Only show fallback data as a last resort
        setTimeout(function() {
            if (!realDataReceived && !window.chartsInitialized) {
                console.warn('No real data received after timeout, showing fallback data');
                showFallbackData();
            }
        }, 5000); // Increased to 5 seconds to give more time for real data
    }
    
    function showFallbackData() {
        // Only show fallback if no real data was received
        if (realDataReceived) {
            console.log('Real data was already received, not showing fallback data');
            return;
        }
        
        console.log('Showing fallback sample data');
        const fallbackData = {
            'orderStatus': {
                'labels': ['En cours', 'Livrée', 'Annulée'],
                'series': [3, 5, 2]
            },
            'deliveredOrders': {
                'labels': ['Unité 1', 'Unité 2', 'Unité 3'],
                'series': [4, 6, 2]
            },
            'deliveredProducts': {
                'labels': ['Produit 1', 'Produit 2', 'Produit 3'],
                'series': [10, 8, 5]
            },
            'productDistribution': {
                'labels': ['Catégorie 1', 'Catégorie 2', 'Catégorie 3'],
                'series': [
                    {
                        'name': 'Produits',
                        'data': [12, 8, 16]
                    }
                ]
            },
            'orderTrends': {
                'labels': ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                'series': [
                    {
                        'name': 'Total',
                        'data': [10, 12, 9, 14, 15, 8, 13]
                    },
                    {
                        'name': 'Livrées',
                        'data': [8, 9, 6, 10, 12, 5, 10]
                    }
                ]
            },
            'isFallbackData': true,
            'dataSource': 'javascript-fallback',
            'randomId': Math.floor(Math.random() * 9000) + 1000,
            'timestamp': Date.now()
        };
        
        const dataStatus = document.getElementById('data-status');
        dataStatus.textContent = 'Yes (fallback data)';
        dataStatus.classList.add('text-yellow-600');
        
        const errorDiv = document.getElementById('dashboard-error');
        const errorMessage = document.getElementById('error-message');
        errorMessage.textContent = "Utilisation de données de secours pour l'affichage des graphiques. Les données réelles n'ont pas pu être chargées.";
        errorDiv.classList.remove('hidden');
        errorDiv.classList.add('bg-yellow-100', 'border-yellow-400', 'text-yellow-700');
        
        renderDashboardCharts(fallbackData);
    }
    
    function renderDashboardCharts(data) {
        if (!data || Object.keys(data).length === 0) {
            console.error('Cannot render charts with empty data');
            return;
        }
        
        console.log('Rendering dashboard charts with data from source:', data.dataSource || 'unknown');
        
        // Force update status indicators
        forceUpdateStatusIndicators();
        
        // Clear any error messages if this is real data
        const errorDiv = document.getElementById('dashboard-error');
        if (!errorDiv.classList.contains('hidden') && 
            (data.isRealData === true || data.dataSource === 'database-simple')) {
            console.log('Hiding error message because real data was received');
            errorDiv.classList.add('hidden');
        }
        
        // Update data status and structure
        const dataStatus = document.getElementById('data-status');
        const dataStructure = document.getElementById('data-structure');
        
        if (data.isRealData === true || data.dataSource === 'database-simple') {
            dataStatus.textContent = 'Yes (database)';
            dataStatus.classList.remove('text-red-600', 'text-yellow-600');
            dataStatus.classList.add('text-green-600');
        } else if (data.isFallbackData === true) {
            dataStatus.textContent = 'Yes (fallback)';
            dataStatus.classList.remove('text-red-600', 'text-green-600');
            dataStatus.classList.add('text-yellow-600');
        } else {
            dataStatus.textContent = 'Yes';
            dataStatus.classList.remove('text-red-600', 'text-yellow-600');
            dataStatus.classList.add('text-green-600');
        }
        
        dataStructure.textContent = JSON.stringify(data, null, 2);
        
        // Track if the administration chart was rendered with the new implementation
        let administrationChartRendered = false;
        
        // Admin Distribution Chart - New Implementation (we process this first)
        if (data.administrationStats) {
            console.log('Rendering administration chart with new data:', data.administrationStats);
            administrationChartRendered = true;
            
            // Check if we have valid data
            if (data.administrationStats.isEmpty !== true && 
                data.administrationStats.labels && 
                data.administrationStats.labels.length > 0 &&
                data.administrationStats.labels[0] !== 'Aucune donnée' &&
                data.administrationStats.labels[0] !== 'Erreur de chargement') {
                
                // Get the data in the right format
                let labels = data.administrationStats.labels;
                let series = [];
                
                // Filter out user roles and statuses from labels
                const filterLabels = ['user', 'admin', 'utilisateur', 'administrateur', 'en attente', 'rejeté', 'pending', 'approved', 'rejected', 'utilisateurs', 'administrateurs', 'en_attente', 'rejetés', 'attente', 'rejet'];
                const filteredLabels = [];
                const filteredSeries = [];
                
                // Extract series data based on the structure
                if (data.administrationStats.series && 
                    Array.isArray(data.administrationStats.series) && 
                    data.administrationStats.series.length > 0) {
                    
                    if (Array.isArray(data.administrationStats.series[0].data)) {
                        series = data.administrationStats.series[0].data;
                    } else {
                        series = data.administrationStats.series.map(item => 
                            typeof item === 'object' ? item.data : item
                        ).flat();
                    }
                }
                
                // Apply filtering - much more aggressive filter
                labels.forEach((label, index) => {
                    const lowerLabel = typeof label === 'string' ? label.toLowerCase().trim() : '';
                    
                    // Skip this label if it contains any of the filter words
                    let shouldFilter = false;
                    for (const filter of filterLabels) {
                        if (lowerLabel === filter || lowerLabel.includes(filter)) {
                            shouldFilter = true;
                            break;
                        }
                    }
                    
                    // Also filter out very short labels which are likely not real administrations
                    if (!shouldFilter && lowerLabel.length > 3) {
                        filteredLabels.push(label);
                        filteredSeries.push(series[index] || 0);
                    }
                });
                
                // Use filtered data
                labels = filteredLabels;
                series = filteredSeries;
                
                // Limit to top 10 for better display if we have more
                if (labels.length > 10) {
                    // Create an array of combined data to sort properly
                    const combined = labels.map((label, index) => ({
                        label,
                        value: series[index] || 0
                    }));
                    
                    // Sort by value in descending order
                    combined.sort((a, b) => b.value - a.value);
                    
                    // Take top 10
                    const top10 = combined.slice(0, 10);
                    
                    // Extract back to separate arrays
                    labels = top10.map(item => item.label);
                    series = top10.map(item => item.value);
                }
                
                // If all data was filtered out, show empty message
                if (labels.length === 0) {
                    labels = ['Aucune administration valide'];
                    series = [0];
                }
                
                // Create the chart with the filtered data
                renderChart('administrationChart', {
                    series: [{
                        name: 'Commandes livrées',
                        data: series
                    }],
                    chart: { 
                        type: 'bar',
                        height: 350,
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            dataLabels: {
                                position: 'top',
                            },
                            borderRadius: 4
                        },
                    },
                    dataLabels: { 
                        enabled: true,
                        offsetX: 20,
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold'
                        },
                        formatter: function(val) {
                            return val.toLocaleString('fr-FR');
                        }
                    },
                    stroke: {
                        width: 1,
                        colors: ['#fff']
                    },
                    xaxis: {
                        categories: labels,
                        labels: {
                            style: {
                                fontSize: '12px',
                                fontWeight: 'bold'
                            }
                        }
                    },
                    colors: ['#4F46E5'],
                    title: {
                        text: 'Répartition des commandes livrées par administration',
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold'
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val.toLocaleString('fr-FR') + ' commandes';
                            }
                        }
                    }
                });
            } else {
                // Display empty state
                renderChart('administrationChart', {
                    series: [{
                        name: 'Commandes livrées',
                        data: [0]
                    }],
                    chart: { 
                        type: 'bar',
                        height: 350,
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return 'Aucune donnée';
                        },
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold'
                        }
                    },
                    xaxis: {
                        categories: [''],
                        labels: {
                            show: false
                        }
                    },
                    tooltip: {
                        enabled: false
                    }
                });
            }
        }
        
        // Skip the older implementation if the new one was used
        if (!administrationChartRendered && (data.productDistribution || data.userDistribution)) {
            const productDistData = data.productDistribution || data.userDistribution;
            console.log('Rendering administration/unit distribution chart with older data:', productDistData);
            
            // Ensure data is in the correct format
            let series = [];
            let labels = [];
            let details = [];
            
            // Check immediately for empty dataset condition before processing other data
            const hasEmptyLabel = productDistData.labels && 
                                 productDistData.labels.length === 1 && 
                                 (productDistData.labels[0] === 'Aucune commande livrée' || 
                                  productDistData.labels[0] === 'Aucune donnée');
            
            const hasEmptyData = productDistData.isEmpty === true || 
                                (productDistData.data_type === 'delivered_orders_by_administration' && 
                                 (!productDistData.all_data || productDistData.all_data.length === 0));
            
            if (!hasEmptyData && !hasEmptyLabel && productDistData.labels && productDistData.series) {
                // Filter out user roles and statuses that might appear as administrations
                const filterLabels = ['user', 'admin', 'utilisateur', 'administrateur', 'en attente', 'rejeté', 'pending', 'approved', 'rejected', 'utilisateurs', 'administrateurs', 'en_attente', 'rejetés', 'attente', 'rejet'];
                const filteredSeries = [];
                const filteredLabels = [];
                const filteredDetails = productDistData.details ? [] : null;
                
                // Parse the data structure properly
                let dataLabels = productDistData.labels || [];
                let dataSeries = [];
                
                // Handle different data structures
                if (productDistData.series) {
                    if (Array.isArray(productDistData.series)) {
                        // Series is an array of series objects
                        if (productDistData.series.length > 0 && productDistData.series[0].data) {
                            dataSeries = productDistData.series[0].data;
                        } else if (productDistData.series.length > 0 && Array.isArray(productDistData.series[0])) {
                            dataSeries = productDistData.series[0];
                        } else {
                            dataSeries = productDistData.series;
                        }
                    } else if (productDistData.series.data) {
                        // Series is a single object with data property
                        dataSeries = productDistData.series.data;
                    }
                }
                
                // Filter the data by removing user roles and statuses - more aggressive approach
                dataLabels.forEach((label, index) => {
                    const lowerLabel = typeof label === 'string' ? label.toLowerCase().trim() : '';
                    
                    // Skip this label if it contains any of the filter words
                    let shouldFilter = false;
                    for (const filter of filterLabels) {
                        if (lowerLabel === filter || lowerLabel.includes(filter)) {
                            shouldFilter = true;
                            break;
                        }
                    }
                    
                    // Also filter out very short labels which are likely not real administrations
                    if (!shouldFilter && lowerLabel.length > 3) {
                        filteredLabels.push(label);
                        if (dataSeries[index] !== undefined) {
                            filteredSeries.push(dataSeries[index]);
                        }
                        if (filteredDetails && productDistData.details && productDistData.details[index]) {
                            filteredDetails.push(productDistData.details[index]);
                        }
                    }
                });
                
                // Use the filtered data
                labels = filteredLabels;
                series = filteredSeries;
                details = filteredDetails || productDistData.details || [];
                
                console.log('Filtered administration data:', {
                    labels: labels,
                    series: series,
                    totalLabels: dataLabels.length,
                    filteredCount: dataLabels.length - labels.length
                });
                
                // If all data was filtered out, show empty message
                if (labels.length === 0) {
                    labels = ['Aucune administration valide'];
                    series = [0];
                }
                
                // Convert to display format for the chart
                let displaySeries = [{
                    name: 'Commandes livrées',
                    data: series
                }];
                
                renderChart('administrationChart', {
                    series: displaySeries,
                    chart: { 
                        type: 'bar', 
                        height: 350,
                        stacked: false,
                        toolbar: {
                            show: true
                        },
                        zoom: {
                            enabled: true
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                    },
                    dataLabels: { enabled: false },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: labels,
                        labels: {
                            style: {
                                fontSize: '10px',
                                fontFamily: 'Helvetica, Arial, sans-serif',
                            },
                            formatter: function(value) {
                                // Truncate long labels
                                if (value && value.length > 20) {
                                    return value.substring(0, 20) + '...';
                                }
                                return value;
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Nombre de commandes'
                        }
                    },
                    fill: { opacity: 1 },
                    tooltip: {
                        y: {
                            formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
                                const label = labels[dataPointIndex];
                                const seriesName = w.globals.seriesNames[seriesIndex];
                                const detail = details[dataPointIndex];
                            
                                let detailsHtml = '';
                                if (detail) {
                                    detailsHtml = `
                                        <div class="mt-1 text-xs text-gray-600 border-t border-gray-200 pt-1">
                                            <div>Administration: ${detail.administration || 'Non spécifié'}</div>
                                        </div>
                                    `;
                                }
                            
                                return `
                                    <div class="bg-white p-2 rounded shadow-md">
                                        <div class="font-bold text-sm">${label}</div>
                                        <div class="text-sm">${seriesName}: <span class="font-bold text-blue-600">${value}</span></div>
                                        ${detailsHtml}
                                    </div>
                                `;
                            }
                        }
                    },
                    legend: {
                        position: 'top'
                    }
                });
            } else {
                console.warn('Invalid data format for administration/unit chart', productDistData);
                
                // Fallback data
                renderChart('administrationChart', {
                    series: [{
                        name: 'Commandes livrées',
                        data: [3, 4, 2]
                    }],
                    chart: { type: 'bar', height: 350 },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                    },
                    dataLabels: { enabled: false },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: ['Administration 1', 'Administration 2', 'Administration 3'],
                    },
                    fill: { opacity: 1 }
                });
            }
        }
        
        if (data.orderStats || data.orderStatus) {
            const orderStatsData = data.orderStats || data.orderStatus;
            console.log('Rendering orderStatus chart with data:', orderStatsData);
            
            // Convert data format if needed
            let series = [];
            let labels = [];
            
            if (orderStatsData.pending && typeof orderStatsData.pending === 'object') {
                // Format: {pending: {orders: X}, approved: {orders: Y}, rejected: {orders: Z}}
                series = [
                    orderStatsData.pending.orders || 0, 
                    orderStatsData.approved.orders || 0, 
                    orderStatsData.rejected.orders || 0
                ];
                labels = ['En attente', 'Approuvées', 'Rejetées'];
                
                // Filter out zero values
                const filteredData = [];
                const filteredLabels = [];
                series.forEach((value, index) => {
                    if (value > 0) {
                        filteredData.push(value);
                        filteredLabels.push(labels[index]);
                    }
                });
                
                if (filteredData.length > 0) {
                    series = filteredData;
                    labels = filteredLabels;
                }
                
                console.log('Converted orderStatus data:', { series, labels });
            } else if (orderStatsData.series && orderStatsData.labels) {
                // Format: {series: [...], labels: [...]}
                series = orderStatsData.series;
                labels = orderStatsData.labels;
            }
            
            if (series.length > 0 && labels.length > 0) {
                renderChart('orderStatusChart', {
                    series: series,
                    chart: { type: 'pie', height: 350 },
                    labels: labels,
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: { width: 200 },
                            legend: { position: 'bottom' }
                        }
                    }]
                });
            } else {
                console.warn('Invalid data format for orderStatus chart', orderStatsData);
            }
        } else {
            console.warn('orderStatus data is missing or empty');
        }
        
        if (data.deliveredOrdersStats || data.deliveredOrders) {
            const deliveredOrdersData = data.deliveredOrdersStats || data.deliveredOrders;
            console.log('Rendering deliveredOrders chart with data:', deliveredOrdersData);
            
            // Ensure data is in the correct format
            let series = [];
            let labels = [];
            
            if (deliveredOrdersData.series && deliveredOrdersData.labels) {
                // Direct format with series and labels
                series = deliveredOrdersData.series;
                labels = deliveredOrdersData.labels;
                
                // Filter out zero values
                const filteredData = [];
                const filteredLabels = [];
                
                if (Array.isArray(series)) {
                    series.forEach((value, index) => {
                        if (value > 0) {
                            filteredData.push(value);
                            filteredLabels.push(labels[index]);
                        }
                    });
                    
                    if (filteredData.length > 0) {
                        series = filteredData;
                        labels = filteredLabels;
                    }
                }
                
                console.log('Processed deliveredOrders data:', { series, labels });
            }
            
            if (series.length > 0 && labels.length > 0) {
                renderChart('deliveredOrdersChart', {
                    series: series,
                    chart: { type: 'donut', height: 350 },
                    labels: labels,
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: { width: 200 },
                            legend: { position: 'bottom' }
                        }
                    }]
                });
            } else {
                console.warn('Invalid data format for deliveredOrders chart', deliveredOrdersData);
                
                // Fallback to default data
                renderChart('deliveredOrdersChart', {
                    series: [1],
                    chart: { type: 'donut', height: 350 },
                    labels: ['Aucune donnée'],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: { width: 200 },
                            legend: { position: 'bottom' }
                        }
                    }]
                });
            }
        } else {
            console.warn('deliveredOrders data is missing or empty');
        }
        
        if (data.deliveredProducts) {
            console.log('Rendering deliveredProducts chart with data:', data.deliveredProducts);
            
            let series = [];
            let labels = [];
            
            if (data.deliveredProducts.series && data.deliveredProducts.labels) {
                series = data.deliveredProducts.series;
                labels = data.deliveredProducts.labels;
                
                // Filter out zero values if needed
                if (Array.isArray(series)) {
                    const filteredData = [];
                    const filteredLabels = [];
                    
                    series.forEach((value, index) => {
                        if (value > 0) {
                            filteredData.push(value);
                            filteredLabels.push(labels[index]);
                        }
                    });
                    
                    if (filteredData.length > 0) {
                        series = filteredData;
                        labels = filteredLabels;
                    }
                }
                
                console.log('Processed deliveredProducts data:', { series, labels });
            }
            
            if (series.length > 0 && labels.length > 0) {
                renderChart('deliveredProductsChart', {
                    series: series,
                    chart: { type: 'pie', height: 350 },
                    labels: labels,
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: { width: 200 },
                            legend: { position: 'bottom' }
                        }
                    }]
                });
            } else {
                console.warn('Invalid data format for deliveredProducts chart', data.deliveredProducts);
            }
        } else {
            console.warn('deliveredProducts data is missing or empty');
        }
        
        if (data.orderTrends || data.ordersTrends) {
            const trendData = data.orderTrends || data.ordersTrends;
            console.log('Rendering orderTrends chart with data:', trendData);
            
            // Ensure data is in the correct format
            let series = [];
            let labels = [];
            
            if (trendData.series && trendData.labels) {
                series = trendData.series;
                labels = trendData.labels;
            } else if (trendData.labels && Array.isArray(trendData.labels)) {
                // Create a simple series if we have labels but no series
                labels = trendData.labels;
                // Create dummy data for demonstration
                series = [{
                    name: 'Commandes',
                    data: labels.map(() => Math.floor(Math.random() * 5) + 1) // Random data 1-5
                }];
            }
            
            console.log('Processed orderTrends data:', { series, labels });
            
            if (series.length > 0 && labels && labels.length > 0) {
                renderChart('orderTrendsChart', {
                    series: series,
                    chart: {
                        height: 350,
                        type: 'line',
                        zoom: { enabled: false }
                    },
                    dataLabels: { enabled: false },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    grid: {
                        row: {
                            colors: ['#f3f3f3', 'transparent'],
                            opacity: 0.5
                        }
                    },
                    xaxis: {
                        categories: labels
                    }
                });
            } else {
                console.warn('Invalid data format for orderTrends chart', trendData);
                
                // Fallback data
                renderChart('orderTrendsChart', {
                    series: [{
                        name: 'Commandes',
                        data: [3, 4, 6, 8, 7, 9, 5]
                    }],
                    chart: {
                        height: 350,
                        type: 'line',
                        zoom: { enabled: false }
                    },
                    dataLabels: { enabled: false },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    grid: {
                        row: {
                            colors: ['#f3f3f3', 'transparent'],
                            opacity: 0.5
                        }
                    },
                    xaxis: {
                        categories: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
                    }
                });
            }
        } else {
            console.warn('orderTrends data is missing or empty');
        }
        
        window.chartsInitialized = true;
    }
    
    function renderChart(elementId, options) {
        console.log(`Rendering chart: ${elementId}`);
        
        const element = document.getElementById(elementId);
        if (!element) {
            console.error(`Chart element not found: ${elementId}`);
            return;
        }
        
        // Log the options to help debug issues
        console.log(`Chart options for ${elementId}:`, options);
        
        // Validate series data
        if (!options.series || 
            (Array.isArray(options.series) && options.series.length === 0) ||
            (Array.isArray(options.series) && options.series[0] === 0) ||
            (Array.isArray(options.series) && Array.isArray(options.series[0]) && options.series[0].length === 0)) {
            console.warn(`Chart ${elementId} has no data in series`, options.series);
        }
        
        if (!options.labels || options.labels.length === 0 || (options.labels.length === 1 && options.labels[0] === 'Aucune donnée')) {
            console.warn(`Chart ${elementId} has no labels or only 'Aucune donnée'`, options.labels);
        }
        
        // Destroy existing chart if it exists
        if (charts[elementId]) {
            console.log(`Destroying existing chart: ${elementId}`);
            charts[elementId].destroy();
        }
        
        try {
            // Create new chart
            charts[elementId] = new ApexCharts(element, options);
            charts[elementId].render();
            console.log(`Chart rendered successfully: ${elementId                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   