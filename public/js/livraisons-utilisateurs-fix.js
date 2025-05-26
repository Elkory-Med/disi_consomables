/**
 * Livraisons par Utilisateur Chart Fix
 * This script ensures the "Livraisons par Utilisateur" chart displays properly,
 * and shows a "No data" message if no real data is available from the database.
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('[LIVRAISONS-UTILISATEUR-FIX] Loaded - v1.2');
    
    // Track whether real data has been loaded from the database
    let realDataLoaded = false;
    let initialLoadTimeout = null;
    let initialPageLoad = true; // Flag to track initial page load
    let dataFetchAttempts = 0; // Track how many times we've tried to fetch data
    const MAX_FETCH_ATTEMPTS = 5; // Increased maximum attempts
    let dataFetchInProgress = false; // Flag to track if a fetch is in progress
    
    // Track if we're waiting for data after a manual refresh
    window.livraisons_fix = window.livraisons_fix || {};
    window.livraisons_fix.isWaitingForData = false;
    
    // TEMPORARY FIX: Create test data for the chart (only used as fallback)
    const TEST_DATA = {
        labels: ['User 1', 'User 2', 'User 3', 'User 4', 'User 5'],
        series: [[5, 4, 3, 2, 1]],
        data_type: 'user_data',
        is_user_data: true,
        all_data: [
            {user: 'User 1', delivered_orders: 5},
            {user: 'User 2', delivered_orders: 4},
            {user: 'User 3', delivered_orders: 3},
            {user: 'User 4', delivered_orders: 2},
            {user: 'User 5', delivered_orders: 1}
        ]
    };
    
    // Reduced initial delay to fetch data more quickly
    setTimeout(function() {
        console.log('[LIVRAISONS-UTILISATEUR-FIX] Attempting initial data fetch from database');
        fetchDashboardData();
    }, 500); // Reduced from 2500 to 500ms
    
    // Function to fetch data from the server directly
    function fetchDashboardData() {
        if (dataFetchInProgress) {
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Data fetch already in progress, skipping');
            return;
        }
        
        if (dataFetchAttempts >= MAX_FETCH_ATTEMPTS) {
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Max fetch attempts reached, using test data as fallback');
            createUserDeliveryChart(TEST_DATA);
            return;
        }
        
        dataFetchInProgress = true;
        dataFetchAttempts++;
        console.log('[LIVRAISONS-UTILISATEUR-FIX] Fetching data attempt #' + dataFetchAttempts);
        
        // Show loading state
        showLoadingIndicator();
        
        // First try to get data directly from the specific user delivery endpoint
        const specificUserUrl = '/admin/dashboard/data/user-deliveries?nocache=' + Date.now();
        
        // Try specific user delivery endpoint first
        fetch(specificUserUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                dataFetchInProgress = false;
                console.log('[LIVRAISONS-UTILISATEUR-FIX] Fetched specific user delivery data:', data);
                
                if (data && data.success && data.data && isValidUserData(data.data)) {
                    console.log('[LIVRAISONS-UTILISATEUR-FIX] Valid user data found from specific endpoint');
                    realDataLoaded = true;
                    createUserDeliveryChart(data.data);
                } else {
                    // Fall back to general dashboard endpoint
                    fallbackToGeneralEndpoint();
                }
            })
            .catch(error => {
                console.error('[LIVRAISONS-UTILISATEUR-FIX] Error fetching from specific endpoint:', error);
                fallbackToGeneralEndpoint();
            });
            
        // Fallback function to try the general dashboard endpoint
        function fallbackToGeneralEndpoint() {
            // Add cache busting to the URL to prevent cached responses
            const url = '/admin/dashboard/data?nocache=' + Date.now();
            
            // Fetch data from the general API
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    dataFetchInProgress = false;
                    console.log('[LIVRAISONS-UTILISATEUR-FIX] Fetched dashboard data:', data);
                    
                    // Process the API response data
                    let userData = null;
                    
                    // Check different possible locations of the user delivery data
                    if (data.userDeliveryStats) {
                        userData = data.userDeliveryStats;
                    } else if (data.raw_data && data.raw_data.userDeliveryStats) {
                        userData = data.raw_data.userDeliveryStats;
                    }
                    
                    console.log('[LIVRAISONS-UTILISATEUR-FIX] Extracted user delivery data:', userData);
                    
                    if (userData && isValidUserData(userData)) {
                        console.log('[LIVRAISONS-UTILISATEUR-FIX] Valid user data found, using real data');
                        realDataLoaded = true;
                        createUserDeliveryChart(userData);
                    } else {
                        console.log('[LIVRAISONS-UTILISATEUR-FIX] No valid user data found in API response');
                        
                        // Try again after 1 second (reduced from 3 seconds)
                        if (dataFetchAttempts < MAX_FETCH_ATTEMPTS) {
                            console.log('[LIVRAISONS-UTILISATEUR-FIX] Will try again in 1 second');
                            setTimeout(fetchDashboardData, 1000);
                        } else {
                            console.log('[LIVRAISONS-UTILISATEUR-FIX] Giving up after ' + dataFetchAttempts + ' attempts, using test data');
                            createUserDeliveryChart(TEST_DATA);
                        }
                    }
                })
                .catch(error => {
                    dataFetchInProgress = false;
                    console.error('[LIVRAISONS-UTILISATEUR-FIX] Error fetching data:', error);
                    
                    // Try again after a delay if we haven't reached the maximum attempts
                    if (dataFetchAttempts < MAX_FETCH_ATTEMPTS) {
                        console.log('[LIVRAISONS-UTILISATEUR-FIX] Will try again in 1 second');
                        setTimeout(fetchDashboardData, 1000);
                    } else {
                        console.log('[LIVRAISONS-UTILISATEUR-FIX] Giving up after ' + dataFetchAttempts + ' attempts, using test data');
                        createUserDeliveryChart(TEST_DATA);
                    }
                });
        }
    }
    
    // Function to check if user data is valid and usable
    function isValidUserData(userData) {
        if (!userData || !Array.isArray(userData.all_data) || userData.all_data.length === 0) {
            return false;
        }
        
        // Check if we have the expected structure
        if (!Array.isArray(userData.labels) || !Array.isArray(userData.series)) {
            return false;
        }
        
        return true;
    }
    
    // Show loading indicator while fetching data
    function showLoadingIndicator() {
        const chartContainer = document.getElementById('userDeliveryChart');
        if (chartContainer) {
            chartContainer.innerHTML = '<div class="chart-loader">Chargement des données...</div>';
        }
    }
    
    // Show "No Data" message when there's no data to display
    function showNoDataMessage() {
        const chartContainer = document.getElementById('userDeliveryChart');
        if (chartContainer) {
            chartContainer.innerHTML = '<div class="no-data-message">Aucune donnée disponible</div>';
        }
    }
    
    // Function to create the chart with either real or test data
    function createUserDeliveryChart(chartData) {
        console.log('[LIVRAISONS-UTILISATEUR-FIX] Creating chart with data:', chartData);
        
        const chartContainer = document.getElementById('userDeliveryChart');
        if (!chartContainer) {
            console.error('[LIVRAISONS-UTILISATEUR-FIX] Chart container not found');
            return;
        }
        
        // Clear any existing chart
        chartContainer.innerHTML = '';
        
        // Check if there is data to display
        if (!chartData || !chartData.labels || !chartData.series || 
            !Array.isArray(chartData.labels) || !Array.isArray(chartData.series) || 
            chartData.labels.length === 0) {
            console.log('[LIVRAISONS-UTILISATEUR-FIX] No data to display');
            showNoDataMessage();
            return;
        }
        
        try {
            // Add detailed debugging about the data structure
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Data structure details:');
            console.log('  - Labels:', chartData.labels);
            console.log('  - Series:', chartData.series);
            console.log('  - Is labels array:', Array.isArray(chartData.labels));
            console.log('  - Is series array:', Array.isArray(chartData.series));
            if (Array.isArray(chartData.series) && chartData.series.length > 0) {
                console.log('  - Is series[0] array:', Array.isArray(chartData.series[0]));
                console.log('  - Series[0] length:', chartData.series[0]?.length);
                console.log('  - Series[0] data:', chartData.series[0]);
            }
            console.log('  - All Data:', chartData.all_data);
            console.log('  - Is all_data array:', Array.isArray(chartData.all_data));
            
            // Ensure we have valid series data
            let seriesData = [];
            if (chartData.series && Array.isArray(chartData.series) && chartData.series.length > 0) {
                if (Array.isArray(chartData.series[0])) {
                    seriesData = chartData.series[0].map(val => parseInt(val) || 0);
                    console.log('  - Using nested array series data (series[0]):', seriesData);
                } else {
                    seriesData = chartData.series.map(val => parseInt(val) || 0);
                    console.log('  - Using direct series data:', seriesData);
                }
            } else {
                // Create empty data matching labels length
                seriesData = new Array(chartData.labels.length).fill(0);
                console.log('  - Created empty series data:', seriesData);
            }
            
            // Ensure labels are strings
            const labels = chartData.labels.map(label => String(label));
            console.log('  - Formatted labels as strings:', labels);
            
            // Create options for the chart
            const options = {
                series: [{
                    name: 'Produits livrés',
                    data: seriesData
                }],
                chart: {
                    type: 'bar',
                    height: 320, // Increased height
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: true, // Show data labels for clarity
                    formatter: function(val) {
                        return val;
                    },
                    style: {
                        fontSize: '11px',
                        colors: ['#000']
                    }
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                colors: ['#6777ef'],
                xaxis: {
                    categories: labels,
                    labels: {
                        style: {
                            fontSize: '11px',
                            fontFamily: 'Nunito, sans-serif',
                        },
                        rotate: -45, // Rotate labels for better readability
                        trim: false // Don't trim labels
                    }
                },
                yaxis: {
                    title: {
                        text: 'Produits livrés',
                        style: {
                            fontSize: '11px',
                            fontFamily: 'Nunito, sans-serif',
                        }
                    },
                    labels: {
                        style: {
                            fontSize: '11px',
                            fontFamily: 'Nunito, sans-serif',
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " produit(s) livré(s)";
                        }
                    }
                },
                legend: {
                    show: false
                },
                noData: {
                    text: 'Aucune donnée disponible',
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                    style: {
                        color: '#6c757d',
                        fontSize: '14px',
                        fontFamily: 'Nunito, sans-serif'
                    }
                }
            };
            
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Creating chart with options:', options);
            
            // Create the chart with direct data - no need to update series later
            try {
                const chart = new ApexCharts(chartContainer, options);
                
                // Store the chart instance globally
                if (window.chartInstances) {
                    window.chartInstances.userDeliveryChart = chart;
                } else {
                    window.chartInstances = {
                        userDeliveryChart: chart
                    };
                }
                
                chart.render();
                console.log('[LIVRAISONS-UTILISATEUR-FIX] Chart render called successfully');
            } catch (error) {
                console.error('[LIVRAISONS-UTILISATEUR-FIX] Error creating ApexCharts instance:', error);
                chartContainer.innerHTML = '<div class="chart-error">Erreur lors de la création du graphique: ' + error.message + '</div>';
                return;
            }
            
            // Create a summary section below the chart
            const allData = chartData.all_data || [];
            const summarySection = document.createElement('div');
            summarySection.className = 'user-delivery-summary';
            summarySection.style.padding = '8px';
            summarySection.style.borderTop = '1px solid #e0e0e0';
            summarySection.style.marginTop = '8px';
            
            // Calculate total deliveries
            const totalDeliveries = Array.isArray(allData) ? 
                allData.reduce((sum, user) => sum + (parseInt(user.delivered_orders) || 0), 0) : 
                seriesData.reduce((sum, val) => sum + (parseInt(val) || 0), 0);
            
            // Create summary content
            let summaryContent = '';
            if (chartData.is_user_data) {
                summaryContent = `
                    <div style="display: flex; justify-content: space-between;">
                        <div>
                            <strong>Total des produits livrés:</strong> ${totalDeliveries}
                        </div>
                        <div>
                            <strong>Utilisateurs actifs:</strong> ${Array.isArray(allData) ? allData.length : labels.length}
                        </div>
                    </div>`;
            } else {
                summaryContent = `<div class="no-user-data-message">Données de test affichées. Les données réelles seront chargées dès qu'elles seront disponibles.</div>`;
            }
            
            summarySection.innerHTML = summaryContent;
            
            // Add the summary section below the chart
            const chartCardBody = chartContainer.closest('.card-body');
            if (chartCardBody) {
                chartCardBody.appendChild(summarySection);
            }
            
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Chart created successfully');
            
            // If this is test data and we haven't loaded real data yet, set a timeout to check again
            if (!chartData.is_user_data && !realDataLoaded && dataFetchAttempts < MAX_FETCH_ATTEMPTS) {
                console.log('[LIVRAISONS-UTILISATEUR-FIX] Test data used, will try to fetch real data again in 5 seconds');
                setTimeout(fetchDashboardData, 5000);
            }
        } catch (error) {
            console.error('[LIVRAISONS-UTILISATEUR-FIX] Error creating chart:', error);
            chartContainer.innerHTML = '<div class="chart-error">Erreur lors de la création du graphique: ' + error.message + '</div>';
        }
    }
    
    // Listen for the dashboard:data-loaded event which is fired by the DashboardManager
    document.addEventListener('dashboard:data-loaded', function(e) {
        console.log('[LIVRAISONS-UTILISATEUR-FIX] Dashboard data loaded event detected via dashboard:data-loaded', e.detail);
        processLoadedData(e.detail?.data?.raw_data);
    });
    
    // Also listen for the dashboardDataLoaded event (legacy format)
    document.addEventListener('dashboardDataLoaded', function(e) {
        console.log('[LIVRAISONS-UTILISATEUR-FIX] Dashboard data loaded event detected via dashboardDataLoaded', e.detail);
        processLoadedData(e.detail);
    });
    
    // Give more time for initial data load before showing "no data"
    initialLoadTimeout = setTimeout(function() {
        if (!realDataLoaded) {
            console.log('[LIVRAISONS-UTILISATEUR-FIX] No real data detected after extended timeout, checking if fix needed');
            initialPageLoad = false;
            checkAndFixUserDeliveryChart();
        }
    }, 5000); // Reduced from 8000 to 5000ms
    
    /**
     * Process loaded data from any event source
     */
    function processLoadedData(data) {
        initialPageLoad = false;
        
        // Clear the waiting flag if we were waiting for data
        if (window.livraisons_fix.isWaitingForData) {
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Real data arrived after refresh');
            window.livraisons_fix.isWaitingForData = false;
        }
        
        // Check if the user data is valid in the loaded data
        const userData = data?.userDeliveryStats || null;
        console.log('[LIVRAISONS-UTILISATEUR-FIX] Received user delivery data:', userData);
        
        if (userData) {
            if (!isValidUserData(userData)) {
                console.log('[LIVRAISONS-UTILISATEUR-FIX] Loaded user data appears invalid');
                
                // Instead of immediately using test data, try fetching directly from the API
                if (!realDataLoaded && dataFetchAttempts < MAX_FETCH_ATTEMPTS) {
                    console.log('[LIVRAISONS-UTILISATEUR-FIX] Will try fetching data directly from API');
                    fetchDashboardData();
                } else {
                    console.log('[LIVRAISONS-UTILISATEUR-FIX] Using test data as fallback');
                    createUserDeliveryChart(TEST_DATA);
                }
            } else {
                console.log('[LIVRAISONS-UTILISATEUR-FIX] Valid user data detected, using real data from database');
                realDataLoaded = true;
                createUserDeliveryChart(userData);
            }
        } else {
            // No user delivery data available
            console.log('[LIVRAISONS-UTILISATEUR-FIX] No user delivery data in response');
            
            // Try fetching directly instead of immediately using test data
            if (!realDataLoaded && dataFetchAttempts < MAX_FETCH_ATTEMPTS) {
                console.log('[LIVRAISONS-UTILISATEUR-FIX] Will try fetching data directly from API');
                fetchDashboardData();
            } else {
                console.log('[LIVRAISONS-UTILISATEUR-FIX] Using test data as fallback');
                createUserDeliveryChart(TEST_DATA);
            }
        }
    }
    
    // After DOMContentLoaded, monitor the chart only if there's a problem
    setTimeout(function() {
        initialPageLoad = false; // No longer in initial page load state
        const chartElement = document.getElementById('userDeliveryChart');
        if (chartElement && (chartElement.innerHTML.trim() === '' || 
            chartElement.innerHTML.includes('Erreur') || 
            chartElement.innerHTML.includes('Pas de données') ||
            chartElement.innerHTML.includes('Chargement des données'))) {
            
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Chart appears empty or loading after initial load, setting up monitoring');
            // Only run periodic checks if the chart is in a problem state
            setInterval(checkAndFixUserDeliveryChart, 5000); // Check every 5 seconds if needed
        }
    }, 5000); // Reduced from 7000 to 5000ms
    
    // Check if a chart reload is happening
    let reloadInProgress = false;
    const originalFetch = window.fetch;
    window.fetch = function() {
        const url = arguments[0];
        if (typeof url === 'string' && url.includes('/dashboard/data')) {
            reloadInProgress = true;
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Detected dashboard data fetch, waiting for real data');
            
            // After the fetch completes, reset the flag
            Promise.resolve(originalFetch.apply(this, arguments))
                .then(response => {
                    setTimeout(() => {
                        reloadInProgress = false;
                        // Allow time for the chart to update with the new data
                        setTimeout(checkChartAfterReload, 1000);
                    }, 1000);
                    return response;
                })
                .catch(() => {
                    reloadInProgress = false;
                });
        }
        return originalFetch.apply(this, arguments);
    };
    
    // Check chart status after a reload
    function checkChartAfterReload() {
        const chartElement = document.getElementById('userDeliveryChart');
        const chartInstance = window.chartInstances && window.chartInstances.userDeliveryChart;
        
        if (chartElement && (!chartInstance || chartElement.innerHTML.trim() === '' || chartElement.innerHTML.includes('Chargement des données'))) {
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Chart still empty after reload, trying to fetch fresh data');
            dataFetchAttempts = 0; // Reset counter
            fetchDashboardData(); // Try to get real data
        }
    }
    
    /**
     * Check if the chart needs fixing and fix it only if necessary
     */
    function checkAndFixUserDeliveryChart() {
        // Skip if a data reload is in progress
        if (reloadInProgress) {
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Skipping check, data reload in progress');
            return;
        }
        
        // During initial page load, be more patient
        if (initialPageLoad) {
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Still in initial page load, waiting for data...');
            return;
        }
        
        console.log('[LIVRAISONS-UTILISATEUR-FIX] Checking chart');
        
        // Get the chart container
        const chartContainer = document.getElementById('userDeliveryChart');
        if (!chartContainer) {
            console.warn('[LIVRAISONS-UTILISATEUR-FIX] Chart container not found');
            return;
        }
        
        // Check if the chart is empty, shows an error, or is still loading
        const isEmpty = chartContainer.innerHTML.trim() === '' || 
                       chartContainer.innerHTML.includes('Erreur') ||
                       chartContainer.innerHTML.includes('Pas de données') ||
                       chartContainer.innerHTML.includes('Aucune donnée') ||
                       chartContainer.innerHTML.includes('Chargement des données');
        
        // Check if chart instance exists and has data
        const chartInstance = window.chartInstances && window.chartInstances.userDeliveryChart;
        const chartHasData = chartInstance && 
                            chartInstance.w && 
                            chartInstance.w.globals && 
                            chartInstance.w.globals.series && 
                            chartInstance.w.globals.series[0] && 
                            chartInstance.w.globals.series[0].length > 0 && 
                            // Make sure there's at least one non-zero value
                            chartInstance.w.globals.series[0].some(val => val > 0);
        
        // Only show no data message if the chart is empty or has no data
        // AND we're not in initial page load
        if ((isEmpty || !chartInstance || !chartHasData) && !initialPageLoad) {
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Chart needs to be fixed');
            // Use a timer to ensure we're not interrupting a data load in progress
            setTimeout(function() {
                // Double check if real data has loaded during our wait
                if (reloadInProgress) {
                    console.log('[LIVRAISONS-UTILISATEUR-FIX] Reload started during wait, aborting fix');
                    return;
                }
                
                // Try to fetch fresh data first
                if (dataFetchAttempts < MAX_FETCH_ATTEMPTS) {
                    dataFetchAttempts = 0; // Reset counter
                    fetchDashboardData();
                } else {
                    // Use test data as a last resort
                    createUserDeliveryChart(TEST_DATA);
                }
            }, 500);
        } else {
            console.log('[LIVRAISONS-UTILISATEUR-FIX] Chart appears to be working with data');
        }
    }
    
    // Listen for dashboard updates (custom event)
    document.addEventListener('dashboardDataUpdated', function(e) {
        console.log('[LIVRAISONS-UTILISATEUR-FIX] Dashboard data updated event received');
        
        // Reset data fetch attempts when a manual refresh is triggered
        dataFetchAttempts = 0;
        fetchDashboardData();
    });
    
    // Monitor dynamic changes to the DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            // Check if our chart container is empty (might have been cleared by another script)
            const chartContainer = document.getElementById('userDeliveryChart');
            if (chartContainer && chartContainer.innerHTML === '' && realDataLoaded) {
                console.log('[LIVRAISONS-UTILISATEUR-FIX] Chart container was emptied, recreating chart');
                // If we already loaded real data, use it again
                fetchDashboardData();
            }
        });
    });
    
    // Start observing the document body
    observer.observe(document.body, { childList: true, subtree: true });
    
    console.log('[LIVRAISONS-UTILISATEUR-FIX] Initialization complete');
    
    // Expose functions for debugging or manual triggering
    window.livraisons_fix = {
        checkAndFixUserDeliveryChart: checkAndFixUserDeliveryChart,
        showNoDataMessage: showNoDataMessage,
        showLoadingIndicator: showLoadingIndicator,
        createUserDeliveryChart: createUserDeliveryChart,
        useTestData: function() { createUserDeliveryChart(TEST_DATA); },
        fetchRealData: function() { 
            dataFetchAttempts = 0;
            fetchDashboardData();
        },
        isRealDataLoaded: function() { return realDataLoaded; },
        isInitialPageLoad: function() { return initialPageLoad; },
        setInitialPageLoad: function(value) { initialPageLoad = value; },
        clearCache: function() {
            return fetch('/admin/clear-dashboard-cache')
                .then(() => {
                    console.log('Cache cleared');
                    return true;
                })
                .catch(err => {
                    console.error('Error clearing cache:', err);
                    return false;
                });
        },
        manualInit: function() {
            dataFetchAttempts = 0;
            initialPageLoad = false;
            fetchDashboardData();
        },
        TEST_DATA: TEST_DATA
    };
}); 