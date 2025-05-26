/**
 * UNIFIED DASHBOARD CHARTS MANAGER
 * A clean, consolidated solution for all dashboard charts with focus on the Par Directions chart
 */

(function() {
    // Check if the emergency fix is loaded and defer to it if found
    let emergencyFixActive = false;
    
    // Function to check if the emergency fix is active
    function checkEmergencyFixActive() {
        return (
            typeof window.parDirectionsFix !== 'undefined' ||
            document.querySelector('script[src*="par-directions-fix.js"]') !== null
        );
    }
    
    // Schedule a check for the emergency fix
    setTimeout(function() {
        emergencyFixActive = checkEmergencyFixActive();
        console.log('Emergency Par Directions fix active:', emergencyFixActive);
        
        if (emergencyFixActive) {
            console.log('Deferring Par Directions chart handling to emergency fix');
        }
    }, 100);
    
    // Mark the dashboard manager as active immediately
    window.dashboardManagerActive = true;
    console.log('Unified Dashboard Charts Manager loaded');
    
    // Configuration options
    const CONFIG = {
        refreshThrottle: 2000, // Minimum time between refreshes in ms
        dataEndpoint: '/admin/dashboard/data',
        containerIds: {
            parDirections: ['userDistributionChart', 'administrationChart', 'parDirectionsChart'],
            orderStatus: 'orderStatusChart',
            deliveredOrders: 'deliveredOrdersChart',
            orderTrends: 'orderTrendsChart',
            deliveredProducts: 'deliveredProductsChart'
        },
        userPatterns: [
            /\(\d{4,}\)/,                // Matches (1234) with 4+ digits - user IDs in parentheses
            /\butilisateur\s\d+\b/i,     // Matches "utilisateur 12345" but not standalone "utilisateur"
            /^[A-Z]\d{4,}$/,             // Matches user IDs like A12345 with 4+ digits
            /\b[A-Z]\d{5,}\b/,           // Matches matricule patterns within text
            /\(\w+\s\d{4,}\)/,           // Matches (Name 12345) pattern
            /\d{4,}\b/,                  // Matches standalone numbers with 4+ digits
            /matricule/i,                // Matches any text with 'matricule'
            /\b[A-Z]{1,3}\d{3,}\b/,      // Matches common matricule patterns like AB123, CD1234
            /\b\d{3,}[A-Z]\b/,           // Matches reverse matricule patterns like 123A
            /utilisateur|user/i          // Matches words 'utilisateur' or 'user'
        ]
    };

    // Track chart instances and last refresh time
    const state = {
        charts: {},
        paginationStates: {}, // NEW: Store pagination state { chartId: { currentPage: 1, totalPages: 1, limit: 10, allData: [], searchTerm: '', filteredData: [] } }
        lastRefreshTime: 0,
        isInitialized: false
    };

    // --- MODIFICATION: Defer Initialization until ApexCharts is loaded ---
    let initAttempts = 0;
    const MAX_INIT_ATTEMPTS = 10; // Try for 5 seconds (10 * 500ms)

    function attemptInitialization() {
        console.log(`Attempting dashboard initialization (Attempt ${initAttempts + 1})...`);
        if (typeof ApexCharts !== 'undefined') {
            console.log('ApexCharts found. Proceeding with initialization.');
            initialize(); // Call the original initialization logic
        } else if (initAttempts < MAX_INIT_ATTEMPTS) {
            initAttempts++;
            console.warn(`ApexCharts not found. Retrying initialization in 500ms...`);
            setTimeout(attemptInitialization, 500);
        } else {
            console.error('Failed to initialize dashboard: ApexCharts did not load after multiple attempts.');
            // Optionally display an error message to the user here
        }
    }
    // --- END MODIFICATION ---

    // Main initialization function
    function initialize() {
        console.log('Unified Dashboard Charts Manager initializing...');
        
        // Initialize all standard charts
        initializeStandardCharts();
        
        // Special handling for Par Directions chart
        initializeParDirectionsChart();
        
        // Set up event listeners
        setupEventListeners();
        
        // Mark as initialized
        state.isInitialized = true;
        
        console.log('Dashboard Charts Manager initialization complete');
    }
    
    // Initialize all standard charts that don't need special handling
    function initializeStandardCharts() {
        // Each chart will be initialized through the updateDashboard function
        // when data is received from the server
        fetchDashboardData(true).then(data => {
            if (!data) return;
            updateDashboard(data);
        });
    }
    
    // Initialize the Par Directions chart with special handling
    function initializeParDirectionsChart() {
        // Check if emergency fix is active
        if (emergencyFixActive || checkEmergencyFixActive()) {
            console.log('Skipping Par Directions chart initialization - handled by emergency fix');
            return;
        }
        
        // First locate or create the chart container
        const container = findOrCreateChartContainer();
        if (!container) {
            console.warn('Unable to locate or create Par Directions chart container');
            return;
        }
        
        // Fetch data specifically for this chart
        fetchDashboardData(true).then(data => {
            console.log('DEBUG: Full dashboard data:', data);
            
            if (!data) {
                console.warn('No dashboard data available');
                showEmptyChart(container);
                return;
            }
            
            // DEFINITIVE FIX: Always use administrationStats which is explicitly marked for the Par Directions chart
            // This completely eliminates any ambiguity about which data to use
            if (data.administrationStats && data.administrationStats.for_par_directions_chart === true) {
                console.log('Using administrationStats explicitly marked for Par Directions chart');
                renderParDirectionsChart(container, data.administrationStats);
            } 
            else if (data.userDistribution && data.userDistribution.for_par_directions_chart === true) {
                console.log('Using userDistribution explicitly marked for Par Directions chart');
                renderParDirectionsChart(container, data.userDistribution);
            }
            else if (data.administrationStats) {
                console.log('Using administrationStats as fallback for Par Directions chart');
                renderParDirectionsChart(container, data.administrationStats);
            } 
            else {
                console.warn('No suitable administration data available');
                showEmptyChart(container);
            }
        });
    }
    
    // Find or create the Par Directions chart container
    function findOrCreateChartContainer() {
        // Try all possible container IDs
        for (const id of CONFIG.containerIds.parDirections) {
            const container = document.getElementById(id);
            if (container) {
                console.log(`Found Par Directions chart container with ID: ${id}`);
                return container;
            }
        }
        
        // If not found, look for the card with "Par Directions" in its header
        console.log('Looking for Par Directions card based on header text');
        const cards = document.querySelectorAll('.card');
        for (const card of cards) {
            const header = card.querySelector('.card-header');
            if (header && 
                (header.textContent.includes('Répartition des utilisateurs') || 
                 header.textContent.includes('Par Directions'))) {
                
                console.log('Found "Répartition des utilisateurs/Par Directions" card');
                let chartContainer = card.querySelector('.card-body > div');
                
                if (!chartContainer) {
                    // Create a container if it doesn't exist
                    const cardBody = card.querySelector('.card-body');
                    if (cardBody) {
                        chartContainer = document.createElement('div');
                        chartContainer.id = 'parDirectionsChart';
                        chartContainer.style.height = '320px';
                        cardBody.appendChild(chartContainer);
                        console.log('Created new Par Directions chart container');
                    }
                } else if (!chartContainer.id) {
                    // Assign an ID if the container doesn't have one
                    chartContainer.id = 'parDirectionsChart';
                }
                
                return chartContainer;
            }
        }
        
        console.warn('Unable to find Par Directions chart container');
        return null;
    }
    
    // Set up event listeners for dashboard events
    function setupEventListeners() {
        // Listen for dashboard refreshed event from other scripts
        document.addEventListener('dashboardRefreshed', function(e) {
            console.log('Received dashboardRefreshed event');
            if (e.detail && e.detail.data) {
                updateDashboard(e.detail.data);
            }
        });
        
        // Handle refresh button click
        const refreshButton = document.getElementById('refresh-dashboard-btn');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                console.log('Refresh button clicked');
                refreshDashboard();
            });
        }
        
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // --- MODIFICATION: Call the attemptInitialization wrapper --- 
            if (!state.isInitialized) { // Check isInitialized flag here too
                 attemptInitialization();
                 // --- REMOVE Call setupSearchListeners --- 
                 // setupSearchListeners(); // Call directly now
            }
            // --- END MODIFICATION ---
        });
    }
    
    // --- NEW: Setup listeners for search inputs --- 
    // --- REVISED for Event Delegation --- 
    function setupSearchListeners() {
        console.log('Setting up delegated search listener on document body.');
        document.body.addEventListener('input', (event) => {
            // Check if the event target is a chart search input
            const inputElement = event.target;
            if (inputElement.matches('.chart-search-input')) {
                const chartId = inputElement.dataset.chartTarget;
                const searchTerm = inputElement.value;
                
                if (chartId) {
                     console.log(`Input event detected via delegation for chart: ${chartId}, Term: '${searchTerm}'`);
                     // Apply debounce
                     clearTimeout(inputElement.debounceTimer);
                     inputElement.debounceTimer = setTimeout(() => {
                         handleSearch(searchTerm, chartId);
                     }, 250); // 250ms debounce
                 } else {
                     console.warn('Chart search input missing data-chart-target:', inputElement);
                 }
            }
        });
    }
    // --- END NEW ---
    // --- END REVISED ---

    // --- Ensure search listener setup runs after current script execution --- 
    setTimeout(setupSearchListeners, 0);

    // Fetch dashboard data from the server
    function fetchDashboardData(bypassCache = false) {
        console.log('Fetching dashboard data...');
        
        // Show loading indicator
        const loadingElement = document.getElementById('dashboard-loading');
        if (loadingElement) {
            loadingElement.style.display = 'flex';
        }
        
        // Build URL with cache-busting parameters if needed
        let url = CONFIG.dataEndpoint;
        if (bypassCache) {
            url += `?refresh=1&nocache=${Date.now()}`;
        }
        
        // Make the request
        return fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            cache: 'no-store'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Hide loading indicator
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            // Ensure consistency in data keys
            if (data.userDistribution && !data.administrationStats) {
                data.administrationStats = data.userDistribution;
            } else if (data.administrationStats && !data.userDistribution) {
                data.userDistribution = data.administrationStats;
            }
            
            return data;
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            
            // Hide loading indicator
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            // Show error message
            const errorElement = document.getElementById('dashboard-error');
            if (errorElement) {
                errorElement.style.display = 'block';
                errorElement.textContent = 'Erreur de chargement: ' + error.message;
            }
            
            return null;
        });
    }
    
    // Public refresh function that can be called from other scripts
    function refreshDashboard() {
        const now = Date.now();
        
        // Throttle refreshes to prevent flooding
        if (now - state.lastRefreshTime < CONFIG.refreshThrottle) {
            console.log('Refresh throttled - too soon since last refresh');
            return Promise.resolve(null);
        }
        
        state.lastRefreshTime = now;
        console.log('Refreshing dashboard...');
        
        return fetchDashboardData(true).then(data => {
            if (!data) return null;
            
            updateDashboard(data);
            return data;
        });
    }
    
    // Update all dashboard charts with new data
    function updateDashboard(data) {
        console.log('Updating dashboard with new data');
        
        // Update summary cards if present
        updateSummaryNumbers(data);
        
        // Update standard charts
        updateStandardCharts(data);
        
        // DEFINITIVE FIX: Special handling for Par Directions chart
        // Only use data explicitly marked for this chart
        const container = findOrCreateChartContainer();
        if (container) {
            // Always check for the explicit flag first
            if (data.administrationStats && data.administrationStats.for_par_directions_chart === true) {
                console.log('Updating chart with administrationStats explicitly marked for Par Directions');
                renderParDirectionsChart(container, data.administrationStats);
            }
            else if (data.userDistribution && data.userDistribution.for_par_directions_chart === true) {
                console.log('Updating chart with userDistribution explicitly marked for Par Directions');
                renderParDirectionsChart(container, data.userDistribution);
            }
            else if (data.administrationStats) {
                console.log('Updating chart with administrationStats as fallback');
                renderParDirectionsChart(container, data.administrationStats);
            }
            // Do not render if no suitable data source is available
        }
        
        // Hide any error messages
        const errorElement = document.getElementById('dashboard-error');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
    
    // Update summary numbers in the dashboard header cards
    function updateSummaryNumbers(data) {
        // Update total orders
        const totalOrdersElement = document.getElementById('total-orders');
        if (totalOrdersElement && data.revenue && data.revenue.totalOrders) {
            totalOrdersElement.textContent = data.revenue.totalOrders;
        }
        
        // Update delivered orders
        const deliveredOrdersElement = document.getElementById('delivered-orders');
        if (deliveredOrdersElement && data.revenue && data.revenue.deliveredOrders) {
            deliveredOrdersElement.textContent = data.revenue.deliveredOrders;
        }
        
        // Update total users
        const totalUsersElement = document.getElementById('total-users');
        if (totalUsersElement && data.userDistribution && data.userDistribution.totalUsers) {
            totalUsersElement.textContent = data.userDistribution.totalUsers;
        }
    }
    
    // Update standard charts (excluding Par Directions)
    function updateStandardCharts(data) {
        // Order Status Chart
        if (data.orderStats) {
            updateChart(
                CONFIG.containerIds.orderStatus,
                data.orderStats,
                {
                    type: 'pie',
                    height: 320,
                    seriesName: 'Commandes',
                    colors: ['#FFB64D', '#10B981', '#FF5370', '#4680FF'],
                    // Add data label formatter to show raw value
                    dataLabels: {
                        formatter: function(val, opts) {
                            // For pie charts, return the series value directly
                            return opts.w.globals.series[opts.seriesIndex];
                        }
                    },
                    // Add tooltip formatter to show raw value
                    tooltipYFormatter: function(val) { return val; }
                }
            );
        }
        
        // Delivered Orders Chart
        if (data.deliveredOrdersStats) {
            updateChart(
                CONFIG.containerIds.deliveredOrders,
                data.deliveredOrdersStats,
                {
                    type: 'pie',
                    height: 320,
                    colors: ['#10B981', '#FF5370'], // Swapped colors: Green first for 'Livrée'
                    // Add data label formatter to show raw value
                    dataLabels: {
                        formatter: function(val, opts) {
                            // For pie charts, return the series value directly
                            return opts.w.globals.series[opts.seriesIndex];
                        }
                    },
                    // Add tooltip formatter to show raw value
                    tooltipYFormatter: function(val) { return val; }
                }
            );
        }
        
        // Order Trends Chart
        if (data.orderTrends) {
            updateChart(
                CONFIG.containerIds.orderTrends, 
                data.orderTrends, 
                {
                    type: 'line',
                    height: 320,
                    seriesName: 'Commandes',
                    colors: ['#4680FF']
                }
            );
        }
        
        // Delivered Products Chart
        if (data.deliveredProducts) {
            updateChart(
                CONFIG.containerIds.deliveredProducts, 
                data.deliveredProducts, 
                {
                    type: 'bar',
                    height: 350,
                    seriesName: 'Quantité',
                    horizontal: true,
                    distributed: true,
                    colors: ['#10B981', '#4680FF', '#FFB64D', '#FF5370', '#7759de']
                }
            );
        }
    }
    
    // Generic chart update function
    function updateChart(containerId, chartData, options) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn(`Chart container '${containerId}' not found`);
            return;
        }
        
        // --- START PAGINATION HANDLING in updateChart ---
        if (chartData.pagination && chartData.pagination.enabled) {
            console.log(`Pagination enabled for ${containerId}`);
            const pagination = chartData.pagination;
            const totalPages = Math.ceil(pagination.total / pagination.limit);
            
            // Store pagination state
            state.paginationStates[containerId] = {
                currentPage: 1,
                totalPages: totalPages,
                limit: pagination.limit,
                allData: pagination.all_data, // Expecting array of {label: '...', value: ...}
                searchTerm: '', // Reset search term on data update
                filteredData: [] // Reset filtered data
            };

            // Initial data slice for page 1 of ALL data
            const initialPageData = pagination.all_data.slice(0, pagination.limit);
            const initialLabels = initialPageData.map(item => item.label);
            const initialSeries = initialPageData.map(item => item.value);

            // Setup controls (do this *after* potential chart update/creation)
            // We pass the data needed for the initial display
            setupPaginationControls(containerId, container, 1, totalPages);

            // Update the chart with the first page data
            try {
                if (state.charts[containerId]) {
                     state.charts[containerId].updateOptions({
                        series: [{
                            // name: options.seriesName || 'Value', // Keep original name if possible
                            data: initialSeries
                        }],
                        xaxis: {
                            categories: initialLabels
                        }
                    });
                    console.log(`Updated ${containerId} chart with page 1`);
                    return; // Exit after handling pagination update
                } else {
                    // If chart doesn't exist yet, create it with page 1 data
                    // Pass the sliced data to createChart
                    createChart(containerId, container, initialLabels, initialSeries, options, chartData.pagination); 
                    return; // Exit after handling pagination creation
                }
            } catch (error) {
                console.error(`Error updating ${containerId} chart with pagination:`, error);
                // Fallback: try to create chart without pagination if update fails?
                 createChart(containerId, container, initialLabels, initialSeries, options, chartData.pagination); 
                 return;
            }
        } else {
             // If pagination is NOT enabled, remove any existing controls
             removePaginationControls(container);
             // Reset pagination state if it existed
             if (state.paginationStates[containerId]) {
                 delete state.paginationStates[containerId];
                 // Also clear any associated search input
                 const searchInput = document.getElementById(`${containerId}-searchInput`);
                 if (searchInput) searchInput.value = '';
             }
        }
        // --- END PAGINATION HANDLING in updateChart ---
        
        // Original update logic (if no pagination or if fallback needed)
        const labels = chartData.labels || [];
        let seriesData = [];
        
        if (chartData.series && Array.isArray(chartData.series[0])) {
            seriesData = chartData.series[0];
        } else if (chartData.series && Array.isArray(chartData.series)) {
            seriesData = chartData.series;
        }
        
        // Check if we already have a chart instance
        if (state.charts[containerId]) {
            // Update existing chart
            try {
                if (typeof ApexCharts === 'undefined') {
                    console.error(`Error updating ${containerId} chart: ApexCharts library not loaded.`);
                    return; 
                }
                if (options.type === 'pie') {
                    state.charts[containerId].updateOptions({
                        labels: labels,
                        series: seriesData
                    });
                } else {
                    state.charts[containerId].updateOptions({
                        series: [{
                            name: options.seriesName || 'Value',
                            data: seriesData
                        }],
                        xaxis: {
                            categories: labels
                        }
                    });
                }
                console.log(`Updated ${containerId} chart`);
            } catch (error) {
                console.error(`Error updating ${containerId} chart:`, error);
                // Recreate the chart if update fails
                container.innerHTML = '';
                // --- Add check for ApexCharts before recreating ---
                if (typeof ApexCharts !== 'undefined') {
                    createChart(containerId, container, labels, seriesData, options);
                } else {
                     console.error(`Cannot recreate ${containerId} chart: ApexCharts library not loaded.`);
                     container.innerHTML = `<div class="alert alert-danger">Erreur: Librairie graphique (ApexCharts) non chargée.</div>`;
                }
                // --- End check ---
            }
        } else {
            // Create new chart
            createChart(containerId, container, labels, seriesData, options);
        }
    }
    
    // Create a new chart
    function createChart(id, container, labels, seriesData, options, paginationData = null) { // Added paginationData param
        // Clear the container
        container.innerHTML = ''; // Clear previous content, including old pagination
        
        // --- START PAGINATION HANDLING in createChart ---
        let initialLabels = labels;
        let initialSeries = seriesData;

        if (paginationData && paginationData.enabled) {
            console.log(`Pagination enabled during creation for ${id}`);
            const totalPages = Math.ceil(paginationData.total / paginationData.limit);
            
            // Store pagination state
            state.paginationStates[id] = {
                currentPage: 1,
                totalPages: totalPages,
                limit: paginationData.limit,
                allData: paginationData.all_data,
                searchTerm: '', // Reset search term
                filteredData: [] // Reset filtered data
            };

            // Get data for page 1 (should already be passed in labels/seriesData if called from updateChart)
            // If called directly, slice here
            if (labels.length !== paginationData.limit && paginationData.all_data.length > 0) {
                 const page1Data = paginationData.all_data.slice(0, paginationData.limit);
                 initialLabels = page1Data.map(item => item.label);
                 initialSeries = page1Data.map(item => item.value);
            }
             // Setup controls after chart renders
             // We'll call setupPaginationControls *after* chart.render()
        } else {
             // Ensure no pagination state exists if not enabled
             if (state.paginationStates[id]) {
                 delete state.paginationStates[id];
                 // Also clear any associated search input
                 const searchInput = document.getElementById(`${id}-searchInput`);
                 if (searchInput) searchInput.value = '';
             }
        }
        // --- END PAGINATION HANDLING in createChart ---

        // Base chart configuration
        let chartOptions = {
            chart: {
                type: options.type,
                height: options.height || 350,
                toolbar: { show: false }
            },
            colors: options.colors || ['#4680FF']
            // dataLabels and tooltip will be added based on options
        };

        // Merge specific options passed in
        if (options.dataLabels) {
            // Ensure dataLabels object exists and merge formatter
            chartOptions.dataLabels = {
                 enabled: true, // Make sure labels are enabled
                ...(options.dataLabels || {}) // Merge passed dataLabels options
            };
        } else {
             // Default data label settings if none provided in options
             chartOptions.dataLabels = { enabled: true };
        }


        // Configure tooltip, using the provided formatter if available
        chartOptions.tooltip = {
            y: {
                formatter: options.tooltipYFormatter || function(val) { return val; } // Use passed formatter or default to raw value
            }
        };


        // Add series data based on chart type
        if (options.type === 'pie') {
            chartOptions.series = initialSeries;
            chartOptions.labels = initialLabels;
            // Add plotOptions for pie chart labels if needed (e.g., total)
            // chartOptions.plotOptions = {
            //     pie: {
            //         donut: { // or pie
            //             labels: {
            //                 show: true,
            //                 value: {
            //                     formatter: function (val) { return val } // Already handled by dataLabels? Check ApexCharts docs.
            //                 }
            //             }
            //         }
            //     }
            // };
        } else {
            chartOptions.series = [{
                name: options.seriesName || 'Value',
                data: initialSeries
            }];
            chartOptions.xaxis = {
                categories: initialLabels
            };
            
            // Add bar chart specific options
            if (options.type === 'bar') {
                chartOptions.plotOptions = {
                    bar: {
                        horizontal: options.horizontal || false,
                        distributed: options.distributed || false
                    }
                };
            }
        }
        
        // Create the chart
        try {
            // --- Add check for ApexCharts --- 
            if (typeof ApexCharts === 'undefined') {
                console.error(`Error creating ${id} chart: ApexCharts library not loaded.`);
                container.innerHTML = `<div class="alert alert-danger">Erreur: Librairie graphique (ApexCharts) non chargée.</div>`;
                return; // Stop execution if library missing
            }
            // --- End check --- 

            state.charts[id] = new ApexCharts(container, chartOptions);
            state.charts[id].render().then(() => {
                 // Setup pagination AFTER chart is rendered
                 if (paginationData && paginationData.enabled) {
                     setupPaginationControls(id, container, 1, state.paginationStates[id].totalPages);
                 }
            });
            console.log(`Created new ${id} chart`);
        } catch (error) {
            console.error(`Error creating ${id} chart:`, error);
            container.innerHTML = `<div class="alert alert-warning">Unable to render chart</div>`;
        }
    }
    
    // Render the Par Directions chart with special processing
    function renderParDirectionsChart(container, data) {
        if (!container) return;
        
        console.log('Rendering Par Directions chart with data:', data);
        
        // Add additional debug information to help diagnose issues
        if (data && data.labels) {
            console.log(`Data has ${data.labels.length} labels:`, data.labels.slice(0, 5));
            console.log('Data type:', data.data_type || 'not specified');
            console.log('Is user data:', data.is_user_data || false);
        }
        
        // Specific check for "Aucun utilisateur" - replace it with "Aucune direction"
        if (data && data.labels && data.labels.length === 1 && data.labels[0] === 'Aucun utilisateur') {
            console.log('Replacing "Aucun utilisateur" with "Aucune direction"');
            showEmptyChart(container);
            return;
        }
        
        // Validate data first - detect user data by pattern matching
        if (!isValidAdministrationData(data)) {
            console.warn('Invalid administration data detected - using empty chart');
            showEmptyChart(container);
            return;
        }
        
        // Special case for empty data (already validated as a valid empty state)
        if (data.labels.length === 1 && 
            (data.labels[0].includes('Aucun') || 
             data.labels[0].includes('Non spécifié'))) {
            console.log('Using empty chart for valid empty state');
            showEmptyChart(container);
            return;
        }
        
        // Get the data in the right format
        let labels = data.labels || [];
        let seriesData = [];
        
        // Extract series data
        if (data.series && Array.isArray(data.series[0])) {
            seriesData = data.series[0].map(val => parseInt(val) || 0);
        } else if (data.series && Array.isArray(data.series)) {
            seriesData = data.series.map(val => parseInt(val) || 0);
        }
        
        // Check if we have at least one item with data
        if (labels.length === 0 || seriesData.length === 0) {
            console.warn('No labels or series data found');
            showEmptyChart(container);
            return;
        }
        
        // Create or update the chart
        const chartId = container.id;
        const options = {
            type: 'bar',
            height: 320,
            seriesName: 'Commandes livrées',
            distributed: true,
            colors: ['#4680FF', '#10B981', '#FFB64D', '#FF5370', '#7759de', '#6610f2']
        };
        
        // Check if we already have a chart instance
        if (state.charts[chartId]) {
            // Update existing chart
            try {
                state.charts[chartId].updateOptions({
                    series: [{
                        name: 'Commandes livrées',
                        data: seriesData
                    }],
                    xaxis: {
                        categories: labels
                    }
                });
                console.log('Updated Par Directions chart');
            } catch (error) {
                console.error('Error updating Par Directions chart:', error);
                // Recreate the chart
                container.innerHTML = '';
                createChart(chartId, container, labels, seriesData, options);
            }
        } else {
            // Create new chart
            createChart(chartId, container, labels, seriesData, options);
        }
        
        // Add summary section after updating the chart
        createSummarySection(container, labels, seriesData);
    }
    
    // Check if the data appears to be valid administration data
    function isValidAdministrationData(data) {
        // First check if emergency fix is active and defer to it
        if ((emergencyFixActive || checkEmergencyFixActive()) && 
            window.parDirectionsFix && 
            typeof window.parDirectionsFix.isValidAdministrationData === 'function') {
            console.log('Delegating validation to emergency fix');
            return window.parDirectionsFix.isValidAdministrationData(data);
        }
        
        // First check if we have any data at all
        if (!data || !data.labels || !Array.isArray(data.labels) || data.labels.length === 0) {
            console.warn('No valid data or labels found');
            return false;
        }

        // DEFINITIVE FIX: First check the explicit flag before any other checks
        // If the data is explicitly marked for the Par Directions chart, it's valid
        if (data.for_par_directions_chart === true) {
            console.log('Data is explicitly marked for Par Directions chart');
            return true;
        }
        
        // If the data is explicitly NOT for the Par Directions chart, it's invalid
        if (data.for_par_directions_chart === false) {
            console.warn('Data is explicitly marked as NOT for Par Directions chart');
            return false;
        }

        // CRITICAL FIX: Explicit data type checking with strongest priority
        // If the data is explicitly marked as administration data, it's valid
        if (data.data_type === 'administration_data' || data.is_user_data === false) {
            return true;
        }
        
        // If we have explicit indicators that this is user data, it's invalid
        if (data.data_type === 'user_data' || data.is_user_data === true) {
            console.warn('Data explicitly marked as user data, not using for administration chart');
            return false;
        }
        
        // If the first label contains "Aucun" or there are no administrations, show the empty state
        if (data.labels.length === 1 && 
            (data.labels[0].includes('Aucun') || 
             data.labels[0].includes('Non spécifié'))) {
            // This is a normal "no data" state, not an error
            console.log('No administration data available (empty state)');
            return true;
        }
        
        // ENHANCED DETECTION: Check for specific user patterns
        // Specific check for the exact user pattern seen in screenshots - names with IDs in parentheses
        const exactProblemRegex = /^.+\s\(\d+\)$/;
        if (data.labels.some(label => exactProblemRegex.test(label))) {
            console.warn('CRITICAL: Detected name(id) pattern in labels');
            return false;
        }
        
        // IMPROVED DETECTION: Additional user patterns to check
        const userPatterns = [
            /\(\d+\)/,       // Any number in parentheses like (2025), (8889)
            /utilisateur/i,  // Case insensitive "utilisateur"
            /user(\s|_)/i,   // "user" followed by space or underscore  
            /matricule/i,    // Any reference to "matricule"
            /\b[A-Z]\d+/,    // Format like B1, A12345
            /\d{3,}/,        // Any 3+ consecutive digits (likely a user ID)
            /\s\d{2,}/,      // Space followed by 2+ digits
            /^\w{1,3}$/,     // Single letters or very short codes
        ];
        
        // Check EVERY label against patterns
        for (const label of data.labels) {
            if (typeof label !== 'string') continue;
            
            for (const pattern of userPatterns) {
                if (pattern.test(label)) {
                    console.warn(`Label "${label}" matched user pattern ${pattern}`);
                    return false;
                }
            }
        }
        
        return true;
    }
    
    // Show empty chart with "No data" message
    function showEmptyChart(container) {
        if (!container) return;
        
        // Clear any existing chart
        if (state.charts[container.id]) {
            try {
                state.charts[container.id].destroy();
            } catch (e) {
                console.warn('Error destroying chart:', e);
            }
            delete state.charts[container.id];
        }
        
        // Clear the container
        container.innerHTML = '';
        
        // Create empty chart with "No data" message
        const options = {
            series: [{
                name: 'Commandes livrées',
                data: [0]
            }],
            chart: { 
                type: 'bar', 
                height: 320,
                toolbar: { show: false }
            },
            plotOptions: { bar: { distributed: true } },
            xaxis: { 
                categories: ['Aucune direction'] // Always use "Aucune direction" for empty state
            },
            colors: ['#4680FF'],
            noData: {
                text: 'Aucune données disponible',
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0,
                style: {
                    color: '#6c757d',
                    fontSize: '16px',
                    fontFamily: 'Helvetica, Arial, sans-serif'
                }
            }
        };
        
        try {
            state.charts[container.id] = new ApexCharts(container, options);
            state.charts[container.id].render();
            console.log('Created empty Par Directions chart with "No data" message');
            
            // Add summary section
            createSummarySection(container, ['Aucune direction'], [0]);
        } catch (e) {
            console.error('Error creating empty chart:', e);
            container.innerHTML = '<div class="alert alert-warning m-3">Impossible de charger le graphique des directions</div>';
        }
    }
    
    // Create summary section below the chart
    function createSummarySection(container, labels, seriesData) {
        // Calculate summary data
        const totalDirections = labels.length;
        const totalCommandes = seriesData.reduce((sum, val) => sum + val, 0);
        const directionsWithData = seriesData.filter(val => val > 0).length;
        
        // Remove any existing summary
        const existingSummary = document.getElementById('parDirectionsSummary');
        if (existingSummary) {
            existingSummary.remove();
        }
        
        // Create summary container
        const summaryContainer = document.createElement('div');
        summaryContainer.id = 'parDirectionsSummary';
        summaryContainer.style.display = 'flex';
        summaryContainer.style.justifyContent = 'space-between';
        summaryContainer.style.padding = '8px 0';
        summaryContainer.style.borderTop = '1px solid #e5e7eb';
        summaryContainer.style.marginTop = '10px';
        summaryContainer.style.fontSize = '12px';
        summaryContainer.style.color = '#374151';
        
        // Create summary text - dynamic based on data
        let displayText;
        
        if (labels.length === 1 && labels[0] === 'Aucune direction') {
            // No data available
            displayText = `
                <div>
                    <span style="color: #4b5563; font-weight: 500;">Directions et commandes</span>
                    <br>
                    <span>Aucune donnée disponible dans la base de données</span>
                </div>
                <div></div>
                <div></div>
            `;
        } else {
            // Normal case with real data
            displayText = `
                <div>
                    <span style="color: #4b5563; font-weight: 500;">Directions et commandes</span>
                    <br>
                    <span>Total directions: ${totalDirections}</span>
                </div>
                <div>
                    <span>Quantité totale: ${totalCommandes}</span>
                </div>
                <div>
                    <span>Affichage de ${directionsWithData} sur ${totalDirections} directions</span>
                    <br>
                    <span>Types distincts: ${totalDirections}</span>
                </div>
            `;
        }
        
        // Add summary data
        summaryContainer.innerHTML = displayText;
        
        // Add summary below the chart
        const chartParent = container.parentNode;
        chartParent.appendChild(summaryContainer);
    }
    
    // --- NEW PAGINATION FUNCTIONS ---

    // Function to create and manage pagination controls
    function setupPaginationControls(chartId, container, currentPage, totalPages) {
        removePaginationControls(container); // Remove old controls first

        // --- MODIFICATION: Get total pages based on search filter ---
        const paginationState = state.paginationStates[chartId];
        let actualTotalPages = totalPages;
        if (paginationState && paginationState.searchTerm) {
            actualTotalPages = Math.ceil(paginationState.filteredData.length / paginationState.limit);
        }
        // --- END MODIFICATION ---

        if (actualTotalPages <= 1) return; // No controls needed for 1 page or filtered to 1 page

        const controlsContainer = document.createElement('div');
        controlsContainer.className = 'chart-pagination-controls flex justify-center items-center gap-2 mt-3';
        controlsContainer.style.padding = '0 1rem'; // Add some padding

        const createButton = (text, page, isDisabled = false) => {
            const button = document.createElement('button');
            button.textContent = text;
            button.className = 'chart-btn text-xs'; // Use existing button style
            button.disabled = isDisabled;
            button.addEventListener('click', () => handlePageChange(chartId, page));
            return button;
        };

        const pageIndicator = document.createElement('span');
        pageIndicator.className = 'page-indicator text-xs'; // Use existing style
        pageIndicator.textContent = `Page ${currentPage} / ${actualTotalPages}`; // Use actualTotalPages
        pageIndicator.id = `${chartId}-page-indicator`;

        // Buttons (disable based on actualTotalPages)
        const firstBtn = createButton('<< First', 1, currentPage === 1);
        const prevBtn = createButton('< Prev', currentPage - 1, currentPage === 1);
        const nextBtn = createButton('Next >', currentPage + 1, currentPage === actualTotalPages);
        const lastBtn = createButton('Last >>', actualTotalPages, currentPage === actualTotalPages);

        controlsContainer.appendChild(firstBtn);
        controlsContainer.appendChild(prevBtn);
        controlsContainer.appendChild(pageIndicator);
        controlsContainer.appendChild(nextBtn);
        controlsContainer.appendChild(lastBtn);

        // Insert controls - try inserting *after* the chart element itself if possible
        if (container.firstChild && container.firstChild.classList && container.firstChild.classList.contains('apexcharts-canvas')) {
             container.insertBefore(controlsContainer, container.firstChild.nextSibling);
        } else {
            container.appendChild(controlsContainer); // Fallback: append at the end
        }
    }

    // Function to remove existing pagination controls
    function removePaginationControls(container) {
        const existingControls = container.querySelector('.chart-pagination-controls');
        if (existingControls) {
            existingControls.remove();
        }
    }

    // Function to handle page changes
    function handlePageChange(chartId, targetPage) {
        // Use the new helper function to update chart view
        updateChartWithPageData(chartId, targetPage);
    }

    // --- NEW: Handle Search Input ---
    function handleSearch(searchTerm, chartId) {
        const paginationState = state.paginationStates[chartId];
        if (!paginationState) {
            console.warn(`Search initiated for ${chartId}, but no pagination state found.`);
            return;
        }

        console.log(`Handling search for chart: ${chartId}, Term: '${searchTerm}'`);
        // Log the structure of the first item in allData for debugging
        if (paginationState.allData && paginationState.allData.length > 0) {
            console.log('Sample allData item:', paginationState.allData[0]);
        }

        // Normalize search term
        const normalizedSearchTerm = searchTerm.trim().toLowerCase();
        paginationState.searchTerm = normalizedSearchTerm;

        if (normalizedSearchTerm === '') {
            paginationState.filteredData = []; // Clear filter
        } else {
            // Filter allData based on label match
            paginationState.filteredData = paginationState.allData.filter(item => {
                // --- MODIFICATION: Filter by administration name --- 
                const adminName = (item.administration || item.name || '').toLowerCase();
                // Log the comparison for the first few items
                if (paginationState.allData.indexOf(item) < 5) { // Log first 5 items
                    console.log(` Comparing: '${adminName}' includes '${normalizedSearchTerm}' ?`, adminName.includes(normalizedSearchTerm));
                }
                return adminName.includes(normalizedSearchTerm);
                // --- END MODIFICATION ---
            });
        }

        // Update the chart to show the first pag                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                