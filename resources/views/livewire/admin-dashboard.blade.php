<!-- Remove CDN links and use locally installed libraries -->
<?php
// Ensure no whitespace before opening div
?><div x-data="{ loading: false }" class="w-full" id="admin-dashboard">
    
    <!-- Add links to the different dashboard implementations -->

    
    <!-- Script to clear cached data -->
    <script>
        // Clear any cached data to ensure fresh rendering
        window.administrationData = null;
        window.productDistributionChart = null;
        window.adminChartUpdateTimeout = null;
        window.currentAdminPage = 0;
        window.filteredAdminData = null;
        
        // Track chart instances to prevent duplication
        window.chartInstances = {
            orderStatus: null,
            deliveredOrders: null,
            deliveredProducts: null,
            administration: null,
            orderTrends: null,
            userDeliveryChart: null
        };
        
        // Create global ApexChartsInstances to store chart instances
        window.ApexChartsInstances = {};
        
        // Track if charts have been initialized
        window.chartsInitialized = false;
        
        // Definition of initChartData function that was missing
        function initChartData() {
            console.log('Initializing chart data...');
            
            // If unified dashboard manager is loaded, defer to it
            if (window.dashboardManager && typeof window.dashboardManager.initialize === 'function') {
                console.log('Using unified dashboard manager for chart initialization');
                window.dashboardManager.initialize();
                return;
            }
            
            // Otherwise use the default chart initialization
            console.log('Using default chart initialization');
            fetch('/admin/dashboard/data?nocache=' + new Date().getTime())
                .then(response => response.json())
                .then(data => {
                    console.log('Dashboard data received');
                    enhancedUpdateChartsWithFreshData(data);
                })
                .catch(error => {
                    console.error('Error fetching dashboard data:', error);
                });
        }
        
        // Ensure charts are initialized properly on page load - prevent duplicate initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Only initialize once
            if (window.chartsInitialized) {
                console.log('Charts already initialized, skipping duplicate initialization');
                return;
            }
            
            console.log('DOM Content Loaded - Initializing charts');
            window.chartsInitialized = true;
            
            // Initialize charts only once at page load
            initChartData();
            
            // Then force a refresh to get the latest data - but don't reinitialize everything
            setTimeout(() => {
                console.log('Forcing refresh to update chart data');
                
                // Clear localStorage/sessionStorage data
                localStorage.removeItem('admin_dashboard_data');
                localStorage.removeItem('administration_data');
                sessionStorage.removeItem('admin_dashboard_data');
                sessionStorage.removeItem('administration_data');
                
                // Get fresh data but don't reinitialize all charts
                fetch('/admin/dashboard/data?refresh=1&nocache=' + new Date().getTime())
                    .then(response => response.json())
                    .then(data => {
                        console.log('Fresh dashboard data received');
                        
                        // Only update the chart data without reinitializing
                        enhancedUpdateChartsWithFreshData(data);
                    })
                    .catch(error => {
                        console.error('Error fetching fresh data:', error);
                    });
            }, 1000);
        });
    </script>
    
    <!-- DEFINITIVE PAR DIRECTIONS CHART FIX -->
    <!-- This script provides a standalone solution for the Par Directions chart -->
    <script src="{{ asset('js/dashboard-unified.js') }}?v={{ time() }}"></script>
    
    <script>
        // Check if ApexCharts is available
        document.addEventListener('DOMContentLoaded', function() {
            console.log('ApexCharts imported: ' + (typeof ApexCharts !== 'undefined' ? 'Yes' : 'No'));
            console.log('Chartist imported: ' + (typeof Chartist !== 'undefined' ? 'Yes' : 'No'));
            console.log('Chart functions imported: ' + (typeof Chart !== 'undefined' ? Chart : 'No'));
        });
        
        // Check if we should use the unified dashboard manager
        document.addEventListener('DOMContentLoaded', function() {
            // If unified dashboard manager is loaded, defer to it
            if (window.dashboardManager && typeof window.dashboardManager.initialize === 'function') {
                console.log('Deferring chart initialization to unified dashboard manager');
                // Prevent default Livewire chart initialization
                window.useUnifiedDashboard = true;
            } else {
                console.log('Using Livewire chart initialization - unified manager not found');
                window.useUnifiedDashboard = false;
            }
        });
        
        // Function to update charts with fresh data without reinitializing
        function enhancedUpdateChartsWithFreshData(data) {
            console.log('Updating charts with fresh data');
            
            // If unified dashboard manager is loaded, use it
            if (window.dashboardManager && typeof window.dashboardManager.updateDashboard === 'function') {
                console.log('Using unified dashboard manager for chart updates');
                window.dashboardManager.updateDashboard(data);
                return;
            }
            
            // Otherwise dispatch an event for other scripts to handle
            console.log('Chart data updated event received');
            document.dispatchEvent(new CustomEvent('dashboardRefreshed', {
                detail: { data: data }
            }));
        }
        
        // This function is intentionally left empty - Par Directions chart is now managed by the definitive fix script
        function fixParDirectionsChart() {
            console.log('Original fixParDirectionsChart called - control passed to definitive fix script');
            // The unified dashboard script will handle everything
            return false;
        }

        function refreshParDirectionsChart() {
            // This function is intentionally left empty - Par Directions chart is now managed by the definitive fix script
        }
    </script>
    
    <!-- Script to map API data to expected format -->
    <script>
        // Normalize API response data to a consistent format the charts expect
        function normalizeApiData(apiResponse) {
            // Default empty response
            let normalized = {
                orderStats: {
                    labels: ['En attente', 'Approuvée', 'Rejetée'],
                    series: [[0, 0, 0]]
                },
                deliveredOrdersStats: {
                    labels: ['Livrée', 'Non Livrée'],
                    series: [[0, 0]]
                },
                deliveredProducts: {
                    labels: ['No Product Data'],
                    series: [[0]]
                },
                administrationStats: {
                    labels: [],
                    series: [],
                    all_data: [],
                    real_administrations: [],
                    realValues: false
                },
                userDistribution: {
                    labels: [],
                    series: [[]]
                },
                orderTrends: {
                    labels: Array.from({length: 7}, (_, i) => {
                        const date = new Date();
                        date.setDate(date.getDate() - (6 - i));
                        return date.toLocaleDateString('fr-FR', {day: '2-digit', month: '2-digit'});
                    }),
                    series: [[0, 0, 0, 0, 0, 0, 0]]
                }
            };

            // Handle order status stats
            if (apiResponse && apiResponse.orderStats) {
                normalized.orderStats = apiResponse.orderStats;
                console.log('Normalized order stats:', normalized.orderStats);
            }

            // Handle delivered orders stats
            if (apiResponse && apiResponse.deliveredOrdersStats) {
                normalized.deliveredOrdersStats = apiResponse.deliveredOrdersStats;
                console.log('Normalized delivered orders stats:', normalized.deliveredOrdersStats);
            }

            // Handle delivered products
            if (apiResponse && apiResponse.deliveredProducts) {
                normalized.deliveredProducts = apiResponse.deliveredProducts;
                
                // Ensure we have a proper series format for delivered products
                if (normalized.deliveredProducts.series && 
                    !Array.isArray(normalized.deliveredProducts.series[0])) {
                    // Convert to nested array format expected by ApexCharts
                    normalized.deliveredProducts.series = [normalized.deliveredProducts.series];
                }
                
                // Ensure we have fallback when there's no data
                if (!normalized.deliveredProducts.labels || normalized.deliveredProducts.labels.length === 0) {
                    normalized.deliveredProducts.labels = ['No Data'];
                    normalized.deliveredProducts.series = [[0]];
                }
                
                console.log('Normalized delivered products:', normalized.deliveredProducts);
            }

            // PRIORITY: Handle administration data - unify the two potential sources
            // First check administrationStats (new format)
            if (apiResponse && apiResponse.administrationStats) {
                console.log('Using administrationStats data (new format)');
                normalized.administrationStats = apiResponse.administrationStats;
            } 
            // Next check userDistribution (old format) if administrationStats is missing
            else if (apiResponse && apiResponse.userDistribution) {
                console.log('Using userDistribution as administrationStats (legacy format)');
                
                // Convert userDistribution to administrationStats format
                normalized.administrationStats = {
                    ...apiResponse.userDistribution,
                    realValues: true
                };
                
                // If userDistribution has real_administrations, use them
                if (apiResponse.userDistribution.real_administrations && 
                    Array.isArray(apiResponse.userDistribution.real_administrations)) {
                    normalized.administrationStats.real_administrations = 
                        apiResponse.userDistribution.real_administrations;
                }
                
                // Make sure we have all_data for pagination
                if (!normalized.administrationStats.all_data) {
                    normalized.administrationStats.all_data = [];
                    
                    // If we have labels and series, create all_data from them
                    if (apiResponse.userDistribution.labels && 
                        Array.isArray(apiResponse.userDistribution.labels) &&
                        apiResponse.userDistribution.series) {
                        
                        let series = apiResponse.userDistribution.series;
                        // Handle different series formats
                        if (Array.isArray(series[0])) {
                            series = series[0];
                        }
                        
                        // Create all_data entries
                        apiResponse.userDistribution.labels.forEach((label, index) => {
                            normalized.administrationStats.all_data.push({
                                administration: label,
                                delivered_orders: series[index] || 0
                            });
                        });
                    }
                }
            } else {
                console.warn('No administration data found in API response, using empty defaults');
            }
            
            // Always copy administrationStats to userDistribution for backward compatibility
            normalized.userDistribution = normalized.administrationStats;

            // Handle user delivery stats
            if (apiResponse && apiResponse.userDeliveryStats) {
                normalized.userDeliveryStats = apiResponse.userDeliveryStats;
                
                // Ensure all_data is properly formatted
                if (!normalized.userDeliveryStats.all_data) {
                    normalized.userDeliveryStats.all_data = [];
                    
                    // If we have labels and series, create all_data from them
                    if (normalized.userDeliveryStats.labels && 
                        Array.isArray(normalized.userDeliveryStats.labels) &&
                        normalized.userDeliveryStats.series) {
                        
                        let series = normalized.userDeliveryStats.series;
                        // Handle different series formats
                        if (Array.isArray(series[0])) {
                            series = series[0];
                        }
                        
                        // Create all_data entries
                        normalized.userDeliveryStats.labels.forEach((label, index) => {
                            normalized.userDeliveryStats.all_data.push({
                                user: label,
                                delivered_orders: series[index] || 0
                            });
                        });
                    }
                }
                
                console.log('Normalized user delivery stats:', normalized.userDeliveryStats);
            } else {
                // Create default user delivery stats if missing
                normalized.userDeliveryStats = {
                    labels: ['Utilisateur 1', 'Utilisateur 2', 'Utilisateur 3', 'Utilisateur 4'],
                    series: [[3, 2, 1, 1]],
                    all_data: [
                        {user: 'Utilisateur 1', delivered_orders: 3},
                        {user: 'Utilisateur 2', delivered_orders: 2},
                        {user: 'Utilisateur 3', delivered_orders: 1},
                        {user: 'Utilisateur 4', delivered_orders: 1}
                    ]
                };
            }

            // Handle order trends
            if (apiResponse && apiResponse.orderTrends) {
                normalized.orderTrends = apiResponse.orderTrends;
                // If no trends, generate default empty data
                if (!normalized.orderTrends.labels || normalized.orderTrends.labels.length === 0) {
                    normalized.orderTrends.labels = Array.from({length: 7}, (_, i) => {
                        const date = new Date();
                        date.setDate(date.getDate() - (6 - i));
                        return date.toLocaleDateString('fr-FR', {day: '2-digit', month: '2-digit'});
                    });
                    normalized.orderTrends.series = [[0, 0, 0, 0, 0, 0, 0]];
                }
            }

            return normalized;
        }
    </script>
    
    <!-- Dashboard header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center">
            <span class="text-sm text-gray-500 mr-2">Dernière mise à jour: {{ isset($lastUpdateTime) && $lastUpdateTime ? $lastUpdateTime->format('d/m/Y H:i:s') : 'Jamais' }}</span>
        </div>
    </div>

    <!-- Load unified chart styling -->
    <link rel="stylesheet" href="{{ asset('css/dashboard-charts-unified.css') }}?v={{ time() }}">

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <!-- Order Status Chart -->
        <div class="bg-white rounded-lg shadow-md p-3">
            <h2 class="text-lg font-semibold mb-1">Statut des commandes</h2>
            <div id="orderStatusChart" class="h-80 w-full"></div>
        </div>
        
        <!-- Delivered Orders Chart -->
        <div class="bg-white rounded-lg shadow-md p-3">
            <h2 class="text-lg font-semibold mb-1">Commandes livrées</h2>
            <div id="deliveredOrdersChart" class="h-80 w-full"></div>
        </div>

        <!-- Delivered Products Chart -->
        <div class="bg-white rounded-lg shadow-md p-3">
            <div class="flex justify-between items-center mb-1">
                <h2 class="text-lg font-semibold">Produits livrés</h2>
                <div class="flex flex-wrap items-center" style="gap: 2px;">
                    <button id="prevProductPage" class="chart-btn">
                        Précédent
                    </button>
                    <span id="productPageIndicator" class="page-indicator">Page 1</span>
                    <button id="nextProductPage" class="chart-btn">
                        Suivant
                    </button>
                    <button id="toggleProductsView" class="chart-btn chart-btn-primary ml-1">
                        Top 10
                    </button>
                    <select id="productSortOption" class="chart-select">
                        <option value="quantity">Par quantité</option>
                        <option value="alphabetical">Alphabétique</option>
                    </select>
                </div>
            </div>
            <div id="deliveredProductsChart" class="h-80 w-full"></div>
            <div class="flex justify-between items-center mt-1">
                <div class="text-xs text-gray-500">Produits commandés et livrés</div>
                <div id="products-chart-info" class="text-xs text-gray-500"></div>
            </div>
            <div id="products-chart-summary" class="mt-2 text-sm text-gray-700">
                {{-- Summary content will be loaded by JavaScript --}}
            </div>
        </div>
        
        <!-- Administration Chart - New Implementation -->
        <div class="bg-white rounded-lg shadow-md p-3">
            <div class="flex flex-col mb-1" style="gap: 2px;">
                <h2 class="text-lg font-semibold">Par Directions</h2>
                <div class="flex flex-wrap items-center" style="gap: 2px;">
                    <input id="adminSearchInput" type="text" placeholder="Rechercher..." class="chart-search" style="width: 100px;">
                    <div class="flex items-center" style="gap: 2px;">
                        <button id="prevAdminPage" class="chart-btn">
                            Précédent
                        </button>
                        <span id="adminPageIndicator" class="page-indicator">Page 1</span>
                        <button id="nextAdminPage" class="chart-btn">
                            Suivant
                        </button>
                    </div>
                    <div class="flex items-center" style="gap: 2px;">
                        <button id="toggleAdminView" class="chart-btn chart-btn-primary">
                            Top 10
                        </button>
                        <div class="flex items-center">
                            <span class="text-xs text-gray-600 mr-1" style="font-size: 0.7rem;">par page</span>
                            <select id="adminPageSize" class="chart-select">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div id="administrationChart" class="h-80 w-full"></div>
            <div class="flex justify-between items-center mt-1">
                <div class="text-xs text-gray-500"></div>
                <div id="admin-chart-info" class="text-xs text-gray-500"></div>
            </div>
        </div>
        
        <!-- Order Trends Chart -->
        <div class="bg-white rounded-lg shadow-md p-3">
            <h2 class="text-lg font-semibold mb-1">Tendances des commandes</h2>
            <div id="orderTrendsChart" class="h-80 w-full"></div>
        </div>

        <!-- User Delivery Statistics Chart -->
        <div class="bg-white rounded-lg shadow-md p-3">
            <div class="flex flex-col mb-1" style="gap: 2px;">
                <h2 class="text-lg font-semibold">Livraisons par Utilisateur</h2>
                <div class="flex items-center flex-nowrap" style="gap: 2px;">
                    <input id="userSearchInput" type="text" placeholder="Rechercher..." class="chart-search" style="width: 100px;">
                    <div class="flex items-center" style="gap: 2px;">
                        <button id="prevUserPage" class="chart-btn">
                            Précédent
                        </button>
                        <span id="userPageIndicator" class="page-indicator">Page 1</span>
                        <button id="nextUserPage" class="chart-btn">
                            Suivant
                        </button>
                    </div>
                    <div class="flex items-center flex-shrink-0" style="gap: 2px;">
                        <button id="toggleUserView" class="chart-btn chart-btn-primary">
                            Top 10
                        </button>
                        <div class="flex items-center">
                            <span class="text-xs text-gray-600 mr-1" style="font-size: 0.7rem;">par page</span>
                            <select id="userPageSize" class="chart-select">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div id="userDeliveryChart" class="h-80 w-full"></div>
            <div class="flex justify-between items-center mt-1">
                <div id="user-chart-info" class="text-xs text-gray-500"></div>
            </div>
        </div>
    </div>

    <!-- Loading indicator -->
    <div 
        id="chartLoadingIndicator" 
        x-show="loading"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-4"
        class="fixed bottom-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg"
    >
        <div class="flex items-center">
            <div class="chart-loading-spinner mr-3"></div>
            <span id="chartLoadingText">Chargement des graphiques...</span>
        </div>
    </div>

    <!-- Hidden input for data -->
    <input type="hidden" id="component-data" wire:ignore value="{{ json_encode($dashboardData ?? []) }}">
  
    <!-- Add this diagnostic info element -->
    <div id="delivered-orders-debug" style="display:none;" class="bg-gray-100 p-2 text-xs">
        <p>Delivered orders from Livewire: <span id="delivered-count" class="font-semibold"></span></p>
        <p>Not delivered orders from Livewire: <span id="not-delivered-count" class="font-semibold"></span></p>
    </div>

    @push('scripts')
    <script>
        // Initialize chart instances tracking
        window.chartInstances = {};
        
        // Main function to initialize all charts
        function initializeCharts(data) {
            // Ensure all chart containers exist
            if (!data) {
                console.error('No data available for charts');
                return;
            }
            
            // Initialize charts
            renderParDirectionsChart(data);
            renderDeliveredProductsChart(data);
            renderOrderTrendsChart(data);
            
            // Add any additional chart initialization here
        }
        
        // Initialize on Alpine init
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboard', () => ({
                loading: true,
                lastUpdated: null,
                
                init() {
                    this.fetchDashboardData();
                },
                
                fetchDashboardData() {
                    this.loading = true;
                    
                    // Add error handling for fetch
                    fetch('/admin/dashboard-data')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Update last updated time
                            this.lastUpdated = new Date().toLocaleTimeString('fr-FR');
                            
                            // Initialize all charts
                            initializeCharts(data);
                            
                            this.loading = false;
                        })
                        .catch(error => {
                            console.error('Error fetching dashboard data:', error);
                            this.loading = false;
                            
                            // Display error message in UI
                            document.querySelectorAll('.chart-container').forEach(container => {
                                container.innerHTML = `
                                    <div class="flex flex-col items-center justify-center h-full p-6">
                                        <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-500 text-center">Erreur lors du chargement des données</p>
                                    </div>
                                `;
                            });
                        });
                },
                
                refreshDashboard() {
                    this.fetchDashboardData();
                }
            }));
        });
        
        // ... existing chart rendering functions ...
    </script>
    @endpush

    <!-- Livewire Events Handling -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Listen for chart-data-updated event from the backend
            Livewire.on('chart-data-updated', (data) => {
                console.log('Chart data updated event received:', data);
                
                // Update the delivered orders chart
                if (data.deliveredOrdersStats && window.deliveredOrdersChart) {
                    window.deliveredOrdersChart.updateSeries([
                        {
                            name: 'Commandes',
                            data: data.deliveredOrdersStats.series || [0, 0]
                        }
                    ]);
                }
            });
            
            // Listen for chartDataUpdated event (from OrdersDelivered component)
            Livewire.on('chartDataUpdated', () => {
                console.log('Chart data update request received');
                // Force refresh of all charts
                if (typeof refreshDashboardData === 'function') {
                    refreshDashboardData();
                }
            });
        });
        
        // Function to manually refresh dashboard data
        function refreshDashboardData() {
            console.log('Manually refreshing dashboard data');
            // Create a loading indicator
            const loadingIndicator = document.getElementById('chartLoadingIndicator');
            if (loadingIndicator) loadingIndicator.style.display = 'flex';
            
            // Fetch fresh data from the API
            fetch('/admin/dashboard/data?refresh=1&t=' + new Date().getTime())
                .then(response => response.json())
                .then(data => {
                    console.log('Fresh dashboard data received:', data);
                    // Update charts with the new data
                    updateChartsWithFreshData(data);
                })
                .catch(error => {
                    console.error('Error refreshing dashboard data:', error);
                })
                .finally(() => {
                    // Hide loading indicator
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                });
        }
        
        // Enhanced function to update charts with fresh data
        function enhancedUpdateChartsWithFreshData(dashboardData) {
            console.log('[Enhanced] Updating charts with fresh data');
            
            // Ensure dashboardData is not null or undefined
            if (!dashboardData) {
                console.error('[Enhanced] Dashboard data is missing');
                return;
            }
            
            // Normalize data to ensure all expected properties exist
            const normalizedData = {
                ...dashboardData,
                orderStatusStats: dashboardData.orderStatusStats || { labels: [], series: [] },
                deliveredOrdersStats: dashboardData.deliveredOrdersStats || { labels: [], series: [] },
                deliveredProductsStats: dashboardData.deliveredProductsStats || { labels: [], series: [] },
                administrationStats: dashboardData.administrationStats || { labels: [], series: [] },
                userDeliveryStats: dashboardData.userDeliveryStats || { 
                    labels: [],
                    series: [],
                    all_data: []
                }
            };
            
            // Emit a custom event with the dashboard data
            // This helps our fix scripts know when real data is available
            document.dispatchEvent(new CustomEvent('dashboardDataLoaded', {
                detail: normalizedData
            }));
            
            // Log all available charts data for debugging
            console.log('[Enhanced] Available chart data:', Object.keys(normalizedData).filter(key => key.endsWith('Stats')));
            
            // Update the order status chart
            if (normalizedData.orderStatusStats && window.chartInstances.orderStatusChart) {
                console.log('[Enhanced] Updating order status chart');
                try {
                    window.chartInstances.orderStatusChart.updateOptions({
                        labels: normalizedData.orderStatusStats.labels,
                        series: normalizedData.orderStatusStats.series
                    });
                } catch (error) {
                    console.error('[Enhanced] Error updating order status chart:', error);
                }
            }
            
            // Update the delivered orders chart
            if (normalizedData.deliveredOrdersStats && window.chartInstances.deliveredOrdersChart) {
                console.log('[Enhanced] Updating delivered orders chart');
                try {
                    window.chartInstances.deliveredOrdersChart.updateOptions({
                        series: [{
                            name: 'Commandes livrées',
                            data: normalizedData.deliveredOrdersStats.series[0]
                        }],
                        xaxis: {
                            categories: normalizedData.deliveredOrdersStats.labels
                        }
                    });
                } catch (error) {
                    console.error('[Enhanced] Error updating delivered orders chart:', error);
                }
            }
            
            // Update delivered products chart
            if (normalizedData.deliveredProductsStats && window.chartInstances.deliveredProductsChart) {
                console.log('[Enhanced] Updating delivered products chart');
                try {
                    window.chartInstances.deliveredProductsChart.updateOptions({
                        series: [{
                            name: 'Produits livrés',
                            data: normalizedData.deliveredProductsStats.series[0]
                        }],
                        xaxis: {
                            categories: normalizedData.deliveredProductsStats.labels
                        }
                    });
                } catch (error) {
                    console.error('[Enhanced] Error updating delivered products chart:', error);
                }
            }
            
            // The user delivery chart update - ensure this happens regardless of backend data
            console.log('[Enhanced] Checking user delivery chart update');
            try {
                // Always attempt to initialize/update the userDeliveryChart
                if (window.chartInstances.userDeliveryChart) {
                    console.log('[Enhanced] Updating existing user delivery chart');
                    // Reinitialize with fresh data
                    initUserDeliveryChart(normalizedData);
                } else {
                    console.log('[Enhanced] Initializing new user delivery chart');
                    // First time initialization
                    initUserDeliveryChart(normalizedData);
                }
            } catch (error) {
                console.error('[Enhanced] Error with user delivery chart:', error);
                // Force reinitialize on error
                setTimeout(() => initUserDeliveryChart(normalizedData), 100);
            }
            
            console.log('[Enhanced] Charts update complete');
            
            // User delivery chart might need extra attention
            const userDeliveryChartEl = document.getElementById('userDeliveryChart');
            if (userDeliveryChartEl) {
                // If the chart is empty, try to reinitialize it specifically
                if (userDeliveryChartEl.innerHTML.trim() === '' || 
                    userDeliveryChartEl.innerHTML.includes('Erreur d\'affichage') ||
                    !window.chartInstances.userDeliveryChart) {
                    console.log('[Enhanced] User delivery chart needs reinitialization');
                    setTimeout(() => initUserDeliveryChart(normalizedData), 500);
                }
            }
        }
        
        // Update the existing updateChartsWithFreshData function with our enhanced version
        window.updateChartsWithFreshData = enhancedUpdateChartsWithFreshData;
    </script>

    <!-- Include dedicated fix scripts -->
    <script src="{{ asset('js/dashboard-unified.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/livraisons-utilisateurs-fix.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/dashboard-final-fix.js') }}?v={{ time() }}"></script>

    <script>
        // Add data validation function to ensure administration chart only shows administration data
        document.addEventListener('chart-data-updated', function(event) {
            const data = event.detail;
            console.log('Chart data updated event received');
            
            // Check if we have both administration and user data
            if (data.administrationStats && data.userDeliveryStats) {
                // Check if administration data looks like user data (contains matricules, etc.)
                const administrationLabels = data.administrationStats.labels || [];
                const hasUserDataInAdmin = administrationLabels.some(label => {
                    if (typeof label !== 'string') return false;
                    
                    // Check for user data patterns
                    return /\(\d+\)/.test(label) || // Numbers in parentheses (matricules)
                           /^[A-Z]\d+$/.test(label) || // Format like B1, A1
                           /utilisateur/i.test(label); // Contains "utilisateur"
                });
                
                if (hasUserDataInAdmin) {
                    console.warn('Detected user data in administration chart, fetching correct data');
                    
                    // Force fetch the correct administration data
                    fetch('/admin/debug-charts?nocache=' + Date.now())
                        .then(response => response.json())
                        .then(correctData => {
                            if (correctData && correctData.administration_data) {
                                console.log('Replacing incorrect administration data with correct data');
                                
                                // Replace the administration data
                                data.administrationStats = correctData.administration_data;
                                
                                // Re-emit the corrected data
                                initializeCharts(data);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching correct administration data:', error);
                        });
                }
            }
        });
        
        // Also add backup protection to ensure administration chart always shows proper data
        function ensureProperAdministrationData() {
            // Check if chart instance exists
            if (window.chartInstances && window.chartInstances.administrationChart) {
                const chart = window.chartInstances.administrationChart;
                
                // Check if chart shows user data
                if (chart.w && chart.w.globals && chart.w.globals.labels) {
                    const labels = chart.w.globals.labels;
                    const hasUserData = labels.some(label => {
                        if (typeof label !== 'string') return false;
                        
                        // Check for user data patterns
                        return /\(\d+\)/.test(label) || // Numbers in parentheses
                               /^[A-Z]\d+$/.test(label); // Format like B1, A1
                    });
                    
                    if (hasUserData) {
                        console.warn('Found user data in administration chart, fetching correct data');
                        
                        // Fetch correct administration data
                        fetch('/admin/debug-charts?nocache=' + Date.now())
                            .then(response => response.json())
                            .then(correctData => {
                                if (correctData && correctData.administration_data) {
                                    const adminData = correctData.administration_data;
                                    
                                    // Get the series data in correct format
                                    let seriesData = [];
                                    if (adminData.series && adminData.series[0] && adminData.series[0].data) {
                                        seriesData = adminData.series[0].data;
                                    } else if (adminData.series && Array.isArray(adminData.series[0])) {
                                        seriesData = adminData.series[0];
                                    }
                                    
                                    // Update the chart with proper data
                                    chart.updateOptions({
                                        series: [{
                                            name: 'Commandes livrées',
                                            data: seriesData
                                        }],
                                        xaxis: {
                                            categories: adminData.labels
                                        }
                                    });
                                    
                                    console.log('Administration chart data corrected');
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching administration data:', error);
                            });
                    }
                }
            }
        }
        
        // Run the check a few times to ensure proper data
        setTimeout(ensureProperAdministrationData, 2000);
        setTimeout(ensureProperAdministrationData, 4000);
        setTimeout(ensureProperAdministrationData, 6000);
    </script>

    <!-- Previous duplicate script load removed from here -->

    <!-- Trigger initial loading of the directions chart on page load -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener to the refresh button to also fix the user delivery chart
            const refreshBtn = document.getElementById('refresh-dashboard');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    // Reset real data loaded flag in livraisons-fix script
                    if (window.livraisons_fix) {
                        // Mark that we're expecting real data to come in
                        window.livraisons_fix.isWaitingForData = true;
                        
                        // Only use fix if real data doesn't arrive
                        setTimeout(function() {
                            if (window.livraisons_fix.isWaitingForData) {
                                console.log('[Dashboard] Real data didn\'t arrive after refresh, using fix script');
                                window.livraisons_fix.checkAndFixUserDeliveryChart();
                                window.livraisons_fix.isWaitingForData = false;
                            }
                        }, 3000); // Wait 3 seconds for real data
                    }
                });
            }
            
            // This is now handled by the dashboard-unified.js script
            console.log('Administration chart will be managed by dashboard manager');
        });
    </script>

    <!-- Modern chart theme script -->
    <script src="{{ asset('js/dashboard-modern-theme.js') }}?v={{ time() }}"></script>
    
    <!-- Enhanced charts with real administration names -->
    <script src="{{ asset('js/dashboard-enhanced-charts.js') }}?v={{ time() }}"></script>

    <!-- Order matters! First ApexCharts, then chart managers, then enhancements -->
    <!-- Make sure ApexCharts is loaded before any scripts that depend on it -->
    <script>
        // If ApexCharts is not loaded yet, we'll display a warning and add a flag to window
        if (typeof ApexCharts === 'undefined') {
            console.warn('ApexCharts not loaded! Checking again after a short delay...');
            window.apexChartsLoadingTimeout = setTimeout(function() {
                if (typeof ApexCharts === 'undefined') {
                    console.error('ApexCharts still not loaded after delay!');
                    window.apexChartsLoaded = false;
                } else {
                    console.log('ApexCharts loaded after delay!');
                    window.apexChartsLoaded = true;
                    // Trigger an event to notify other scripts
                    document.dispatchEvent(new Event('apexcharts-loaded'));
                }
            }, 2000);
        } else {
            window.apexChartsLoaded = true;
            console.log('ApexCharts already loaded and available!');
        }
    </script>

    <!-- Dashboard Scripts - Consolidated and ordered for optimal loading -->
    <!-- 1. Core Functionality -->
    <script src="{{ asset('js/dashboard-unified.js') }}?v={{ time() }}"></script>
    
    <!-- 2. Theme and Enhancement Scripts -->
    <script src="{{ asset('js/dashboard-modern-theme.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/dashboard-enhanced-charts.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/dashboard-trends-enhanced.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/dashboard-chart-improvements.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/enhanced-products-chart.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/delivered-orders-color-fix.js') }}?v={{ time() }}"></script>

    <!-- Script to store chart instances globally -->
    <script>
        // Create a global registry for ApexCharts instances
        window.ApexChartsInstances = window.ApexChartsInstances || {};

        // Track all chart instances for module access
        document.addEventListener('DOMContentLoaded', function() {
            // Make chart instances globally accessible
            if (window.chartInstances) {
                // If chart created in the unified dashboard system
                Object.keys(window.chartInstances).forEach(function(key) {
                    if (window.chartInstances[key]) {
                        // Register in global ApexChartsInstances for enhancement scripts
                        window.ApexChartsInstances[key] = window.chartInstances[key];
                    }
                });
            }
        });

        // Init user delivery chart function - previously missing
        function initUserDeliveryChart(data) {
            console.log('Initializing user delivery chart with data:', data);
            try {
                // Check if we have user delivery stats
                if (!data || !data.userDeliveryStats) {
                    console.warn('No user delivery stats data available');
                    return;
                }

                const chartElement = document.getElementById('userDeliveryChart');
                if (!chartElement) {
                    console.warn('User delivery chart element not found');
                    return;
                }

                // Clear previous chart instance if exists
                if (window.chartInstances && window.chartInstances.userDeliveryChart) {
                    window.chartInstances.userDeliveryChart.destroy();
                    window.chartInstances.userDeliveryChart = null;
                }

                // Format the data for the chart
                const userData = data.userDeliveryStats;
                const labels = userData.labels || [];
                let series = userData.series || [[0]];
                
                // Make sure series is in the right format
                if (!Array.isArray(series[0])) {
                    series = [series];
                }

                // Create chart options
                const options = {
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Commandes livrées',
                        data: series[0]
                    }],
                    xaxis: {
                        categories: labels,
                        labels: {
                            style: {
                                fontFamily: 'Inter, sans-serif',
                                cssClass: 'text-xs font-normal fill-gray-500'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Nombre de commandes',
                            style: {
                                fontSize: '14px',
                                fontFamily: 'Inter, sans-serif',
                                fontWeight: 500
                            }
                        },
                        labels: {
                            formatter: function(val) {
                                return val.toFixed(0);
                            }
                        }
                    },
                    colors: ['#2563eb'],
                    plotOptions: {
                        bar: {
                            borderRadius: 3,
                            horizontal: false,
                            columnWidth: '60%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    grid: {
                        show: true,
                        borderColor: '#f8fafc',
                        strokeDashArray: 0,
                        position: 'back'
                    },
                    fill: {
                        opacity: 1
                    }
                };

                // Create the chart
                const chart = new ApexCharts(chartElement, options);
                chart.render();

                // Store the chart instance
                if (window.chartInstances) {
                    window.chartInstances.userDeliveryChart = chart;
                }
                
                // Also register in global ApexChartsInstances for enhancement scripts
                window.ApexChartsInstances = window.ApexChartsInstances || {};
                window.ApexChartsInstances.userDeliveryChart = chart;
                
                // Register chart with Chart Registry if available
                if (window.ChartRegistry && typeof window.ChartRegistry.registerChart === 'function') {
                    window.ChartRegistry.registerChart('userDeliveryChart', chart);
                }
                
                console.log('User delivery chart initialized successfully');
                
                // Dispatch events to notify enhancement scripts
                document.dispatchEvent(new CustomEvent('userDeliveryChartInitialized', {
                    detail: { chart: chart, chartId: 'userDeliveryChart' }
                }));
                
                document.dispatchEvent(new CustomEvent('chartReady', {
                    detail: { chartId: 'userDeliveryChart' }
                }));
                
                return chart;
            } catch (error) {
                console.error('Error initializing user delivery chart:', error);
                return null;
            }
        }
    </script>
</div><!-- Close the main div x-data="{ loading: false }" that opened at the top of the file -->
</body>
</html> 