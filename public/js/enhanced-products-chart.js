/**
 * Enhanced Products Chart
 * This script adds advanced features to the Produits livrés chart including:
 * - Optimized handling of large datasets
 * - Enhanced summary section with statistics
 * - Improved visualization and performance
 * - Advanced sorting and filtering
 * Version 1.2.0
 */
(function() {
    console.log('[Products Chart] Initializing product chart enhancements...');
    
    // Configuration
    const config = {
        chartId: 'deliveredProductsChart',
        maxItemsPerPage: 12, // Default items per page
        summaryId: 'products-chart-summary',
        chartInfoId: 'products-chart-info',
        sortOptions: {
            quantity: { label: 'Par quantité', value: 'quantity' },
            alphabetical: { label: 'Alphabétique', value: 'alphabetical' },
            recent: { label: 'Plus récents', value: 'recent' }
        },
        viewModes: {
            top: { label: 'Top 10', value: 'top' },
            paginated: { label: 'Pagination', value: 'paginated' },
            all: { label: 'Tous', value: 'all' }
        },
        defaultSort: 'quantity',
        defaultView: 'top',
        maxRetryAttempts: 20,     // Increased maximum retry attempts
        retryDelay: 2000,         // Increased delay between retries
        initialDelay: 4000,       // Initial delay for better initialization chances
        animationEnabled: true,
        extendedTooltips: true
    };
    
    // State management
    const state = {
        rawData: null,
        processedData: null, 
        currentPage: 1,
        totalPages: 1,
        sortBy: config.defaultSort,
        viewMode: config.defaultView,
        searchTerm: '',
        itemsPerPage: config.maxItemsPerPage,
        isInitialized: false,
        lastUpdateTime: new Date(),
        fullData: null,
        displayedData: null,
        retryCount: 0           // Track number of retry attempts
    };
    
    /**
     * Validates if an object is a valid ApexCharts instance
     * @param {Object} chart - The chart object to validate
     * @return {Boolean} - Whether it's a valid ApexCharts instance
     */
    function isValidApexChartsInstance(chart) {
        try {
            return chart && 
                typeof chart === 'object' && 
                chart.w && 
                chart.w.globals && 
                typeof chart.updateOptions === 'function';
        } catch (e) {
            console.debug('[Products Chart] Chart validation error:', e);
            return false;
        }
    }
    
    /**
     * Enhanced function to find chart instance by ID with better fallback mechanisms
     * @param {String} chartId - The ID of the chart to find
     * @return {Object|null} - The chart instance or null if not found
     */
    function findChartInstance(chartId) {
        try {
            // First try the central chart registry (new method)
            if (window.ChartRegistry && typeof window.ChartRegistry.getChart === 'function') {
                const instance = window.ChartRegistry.getChart(chartId);
                if (isValidApexChartsInstance(instance)) {
                    console.log(`[Products Chart] Found chart instance for ${chartId} in ChartRegistry`);
                    return instance;
                }
            }
            
            // Then check window.ApexChartsInstances if available
            if (window.ApexChartsInstances && window.ApexChartsInstances[chartId]) {
                const instance = window.ApexChartsInstances[chartId];
                if (isValidApexChartsInstance(instance)) {
                    console.log(`[Products Chart] Found chart instance for ${chartId} in ApexChartsInstances`);
                    return instance;
                }
            }
            
            // Then check chartInstances if available
            if (window.chartInstances && window.chartInstances[chartId]) {
                const instance = window.chartInstances[chartId];
                if (isValidApexChartsInstance(instance)) {
                    // Auto-register in global registry if found
                    if (window.ChartRegistry && typeof window.ChartRegistry.registerChart === 'function') {
                        window.ChartRegistry.registerChart(chartId, instance);
                    }
                    window.ApexChartsInstances[chartId] = instance;
                    console.log(`[Products Chart] Found chart instance for ${chartId} in chartInstances`);
                    return instance;
                }
            }
            
            // Check for global variables with the chart name directly
            if (window[chartId] && isValidApexChartsInstance(window[chartId])) {
                if (window.ChartRegistry && typeof window.ChartRegistry.registerChart === 'function') {
                    window.ChartRegistry.registerChart(chartId, window[chartId]);
                }
                window.ApexChartsInstances[chartId] = window[chartId];
                console.log(`[Products Chart] Found chart instance for ${chartId} as global variable`);
                return window[chartId];
            }
            
            // As fallback, try to find by DOM ID
            if (document.getElementById(chartId)) {
                const chartElement = document.getElementById(chartId);
                
                // Try the ApexCharts.getChartByID method if available
                if (typeof ApexCharts !== 'undefined' && typeof ApexCharts.getChartByID === 'function') {
                    try {
                        const instance = ApexCharts.getChartByID(chartId);
                        if (isValidApexChartsInstance(instance)) {
                            if (window.ChartRegistry && typeof window.ChartRegistry.registerChart === 'function') {
                                window.ChartRegistry.registerChart(chartId, instance);
                            }
                            window.ApexChartsInstances[chartId] = instance;
                            console.log(`[Products Chart] Found chart instance for ${chartId} using ApexCharts.getChartByID`);
                            return instance;
                        }
                    } catch (e) {
                        console.debug(`[Products Chart] Error getting chart by ID for ${chartId}:`, e);
                    }
                }
                
                // Try to find ApexCharts instance from the DOM element
                if (chartElement.__proto__ && chartElement.__proto__.chart) {
                    const instance = chartElement.__proto__.chart;
                    if (isValidApexChartsInstance(instance)) {
                        if (window.ChartRegistry && typeof window.ChartRegistry.registerChart === 'function') {
                            window.ChartRegistry.registerChart(chartId, instance);
                        }
                        window.ApexChartsInstances[chartId] = instance;
                        console.log(`[Products Chart] Found chart instance for ${chartId} from DOM element prototype`);
                        return instance;
                    }
                }
            }
            
            // As a last resort, search through all ApexCharts instances if Apex global object exists
            if (typeof ApexCharts !== 'undefined' && ApexCharts.instances) {
                for (let id in ApexCharts.instances) {
                    const instance = ApexCharts.instances[id];
                    if (isValidApexChartsInstance(instance) && 
                        (instance.id === chartId || instance.el && instance.el.id === chartId)) {
                        if (window.ChartRegistry && typeof window.ChartRegistry.registerChart === 'function') {
                            window.ChartRegistry.registerChart(chartId, instance);
                        }
                        window.ApexChartsInstances[chartId] = instance;
                        console.log(`[Products Chart] Found chart instance for ${chartId} in ApexCharts.instances`);
                        return instance;
                    }
                }
            }
            
            // If we reach here, no chart instance was found
            console.log(`[Products Chart] Chart instance for ${chartId} not found`);
            return null;
        } catch (e) {
            console.debug('[Products Chart] Error finding chart instance:', e);
            return null;
        }
    }
    
    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Wait for chart registry to be potentially available
        setTimeout(init, config.initialDelay);
    });
    
    // Listen for chart ready events
    document.addEventListener('chartReady', function(e) {
        if (e.detail && e.detail.chartId === config.chartId) {
            console.log('[Products Chart] Received chartReady event for products chart');
            if (!state.isInitialized) {
                setTimeout(init, 500); // Small delay to ensure chart is fully initialized
            } else {
                setTimeout(refreshProductsChart, 500);
            }
        }
    });
    
    // Main initialization function
    function init() {
        console.log('[Products Chart] Attempting to initialize with chart instance');
        
        if (typeof ApexCharts === 'undefined') {
            console.warn('[Products Chart] ApexCharts not loaded, will retry...');
            if (state.retryCount < config.maxRetryAttempts) {
                state.retryCount++;
                setTimeout(init, config.retryDelay);
            } else {
                console.warn('[Products Chart] Maximum retry attempts reached. Unable to initialize product chart enhancements.');
                state.retryCount = 0; // Reset for future attempts
            }
            return;
        }
        
        // Reset retry count on success
        state.retryCount = 0;
        
        const chartInstance = findChartInstance(config.chartId);
        if (!chartInstance) {
            console.log('[Products Chart] Chart instance not found, will try again later');
            if (state.retryCount < config.maxRetryAttempts) {
                state.retryCount++;
                setTimeout(init, config.retryDelay);
            } else {
                console.warn('[Products Chart] Maximum retry attempts reached. Unable to find chart instance.');
                state.retryCount = 0; // Reset for future attempts
            }
            return;
        }
        
        // Now that we have the chart instance, check if it's valid
        if (!isValidApexChartsInstance(chartInstance)) {
            console.log('[Products Chart] Found chart instance but it doesn\'t appear to be a valid ApexCharts instance, will retry...');
            if (state.retryCount < config.maxRetryAttempts) {
                state.retryCount++;
                setTimeout(init, config.retryDelay);
            } else {
                console.warn('[Products Chart] Maximum retry attempts reached. Chart instance is not valid.');
                state.retryCount = 0;
            }
            return;
        }
        
        console.log('[Products Chart] Valid chart instance found, initializing enhancements...');
        
        // Continue with rest of initialization...
        enhanceProductsChart(chartInstance);
        
        // Listen for dashboard data loaded events
        document.addEventListener('dashboardDataLoaded', function(e) {
            console.log('[Products Chart] Dashboard data loaded event detected');
            setTimeout(() => refreshProductsChart(e.detail), 1000);
        });
        
        // Also listen for chart refresh events
        document.addEventListener('chartRefreshed', function(e) {
            if (e.detail && e.detail.chartId === config.chartId) {
                console.log('[Products Chart] Chart refresh event detected');
                setTimeout(() => refreshProductsChart(e.detail), 500);
            }
        });
        
        // Mark as initialized
        state.isInitialized = true;
        console.log('[Products Chart] Successfully initialized product chart enhancements');
    }
    
    // Function to enhance the products chart with new features
    function enhanceProductsChart(chartInstance) {
        if (!chartInstance || !isValidApexChartsInstance(chartInstance)) {
            console.warn('[Products Chart] Invalid chart instance provided to enhanceProductsChart');
            return;
        }
        
        try {
            console.log('[Products Chart] Applying enhancements to chart...');
            
            // Extract data from chart
            const chartData = extractChartData(chartInstance);
            if (!chartData) {
                console.warn('[Products Chart] No data available from chart instance');
                return;
            }
            
            // Store the data
            state.rawData = chartData;
            state.fullData = chartData;
            
            // Create enhanced controls
            createEnhancedControls(chartInstance);
            
            // Apply initial processing
            processChartData(chartData);
            
            console.log('[Products Chart] Chart enhancements applied successfully');
        } catch (error) {
            console.error('[Products Chart] Error enhancing products chart:', error);
        }
    }
    
    // Extract data from chart instance
    function extractChartData(chartInstance) {
        try {
            if (!chartInstance || !chartInstance.w || !chartInstance.w.globals) {
                return null;
            }
            
            const series = chartInstance.w.globals.series;
            const labels = chartInstance.w.globals.labels;
            
            if (!series || !labels || !series.length || !labels.length) {
                return null;
            }
            
            // Structure the data in a more usable format
            const data = labels.map((label, index) => {
                let value = 0;
                if (Array.isArray(series)) {
                    if (Array.isArray(series[0])) {
                        value = series[0][index] || 0;
                    } else {
                        value = series[index] || 0;
                    }
                }
                
                return {
                    label: label,
                    value: value,
                    index: index
                };
            });
            
            return {
                raw: {
                    series: series,
                    labels: labels
                },
                processed: data,
                timestamp: new Date()
            };
        } catch (error) {
            console.error('[Products Chart] Error extracting chart data:', error);
            return null;
        }
    }
    
    // Process chart data based on current state
    function processChartData(chartData) {
        try {
            if (!chartData || !chartData.processed || !chartData.processed.length) {
                console.warn('[Products Chart] No processed data available for processing');
                return;
            }
            
            let data = [...chartData.processed];
            
            // Apply sorting
            switch (state.sortBy) {
                case 'quantity':
                    data = data.sort((a, b) => b.value - a.value);
                    break;
                case 'alphabetical':
                    data = data.sort((a, b) => a.label.localeCompare(b.label));
                    break;
                case 'recent':
                    // Recent is just the original order
                    data = data.sort((a, b) => a.index - b.index);
                    break;
            }
            
            // Apply view mode
            let displayData = [...data];
            if (state.viewMode === 'top') {
                displayData = data.slice(0, 10);
            } else if (state.viewMode === 'paginated') {
                const startIndex = (state.currentPage - 1) * state.itemsPerPage;
                displayData = data.slice(startIndex, startIndex + state.itemsPerPage);
            }
            
            // Store processed data
            state.processedData = data;
            state.displayedData = displayData;
            state.totalPages = Math.ceil(data.length / state.itemsPerPage);
            
            // Update chart with processed data
            updateChartDisplay();
        } catch (error) {
            console.error('[Products Chart] Error processing chart data:', error);
        }
    }
    
    // Update the chart display with processed data
    function updateChartDisplay() {
        try {
            const chartInstance = findChartInstance(config.chartId);
            if (!chartInstance) {
                console.warn('[Products Chart] Chart instance not found for updating display');
                return;
            }
            
            if (!state.displayedData || !state.displayedData.length) {
                console.warn('[Products Chart] No display data available');
                return;
            }
            
            // Extract series and labels from displayed data
            const labels = state.displayedData.map(item => item.label);
            const series = [state.displayedData.map(item => item.value)];
            
            // Update chart with new data
            chartInstance.updateOptions({
                labels: labels,
                series: series
            });
            
            console.log('[Products Chart] Chart display updated with processed data');
        } catch (error) {
            console.error('[Products Chart] Error updating chart display:', error);
        }
    }
    
    // Create enhanced controls for the chart
    function createEnhancedControls(chartInstance) {
        try {
            // Find the chart container
            const chartElement = document.getElementById(config.chartId);
            if (!chartElement) {
                console.warn('[Products Chart] Chart element not found for creating controls');
                return;
            }
            
            const chartContainer = chartElement.closest('.chart-container') || chartElement.parentNode;
            if (!chartContainer) {
                console.warn('[Products Chart] Chart container not found');
                return;
            }
            
            // Create control container if it doesn't exist
            let controlsContainer = chartContainer.querySelector('.products-chart-controls');
            if (!controlsContainer) {
                controlsContainer = document.createElement('div');
                controlsContainer.className = 'products-chart-controls flex flex-wrap items-center space-x-2 mb-2';
                chartContainer.insertBefore(controlsContainer, chartElement);
            }
            
            // Create sort controls
            let sortControl = controlsContainer.querySelector('.products-chart-sort');
            if (!sortControl) {
                sortControl = document.createElement('select');
                sortControl.className = 'products-chart-sort chart-select text-xs bg-gray-100 border border-gray-300 rounded';
                
                // Add options
                Object.keys(config.sortOptions).forEach(key => {
                    const option = document.createElement('option');
                    option.value = key;
                    option.textContent = config.sortOptions[key].label;
                    option.selected = key === state.sortBy;
                    sortControl.appendChild(option);
                });
                
                // Add event listener
                sortControl.addEventListener('change', function() {
                    state.sortBy = this.value;
                    processChartData(state.rawData);
                });
                
                // Add to container
                const sortLabel = document.createElement('span');
                sortLabel.className = 'text-xs text-gray-600 mr-1';
                sortLabel.textContent = 'Trier:';
                
                const sortWrapper = document.createElement('div');
                sortWrapper.className = 'flex items-center';
                sortWrapper.appendChild(sortLabel);
                sortWrapper.appendChild(sortControl);
                
                controlsContainer.appendChild(sortWrapper);
            }
            
            // Create view mode controls
            let viewModeControl = controlsContainer.querySelector('.products-chart-view-mode');
            if (!viewModeControl) {
                viewModeControl = document.createElement('select');
                viewModeControl.className = 'products-chart-view-mode chart-select text-xs bg-gray-100 border border-gray-300 rounded';
                
                // Add options
                Object.keys(config.viewModes).forEach(key => {
                    const option = document.createElement('option');
                    option.value = key;
                    option.textContent = config.viewModes[key].label;
                    option.selected = key === state.viewMode;
                    viewModeControl.appendChild(option);
                });
                
                // Add event listener
                viewModeControl.addEventListener('change', function() {
                    state.viewMode = this.value;
                    processChartData(state.rawData);
                });
                
                // Add to container
                const viewLabel = document.createElement('span');
                viewLabel.className = 'text-xs text-gray-600 mr-1';
                viewLabel.textContent = 'Afficher:';
                
                const viewWrapper = document.createElement('div');
                viewWrapper.className = 'flex items-center';
                viewWrapper.appendChild(viewLabel);
                viewWrapper.appendChild(viewModeControl);
                
                controlsContainer.appendChild(viewWrapper);
            }
            
            // Create pagination controls
            let paginationContainer = chartContainer.querySelector('.products-chart-pagination');
            if (!paginationContainer) {
                paginationContainer = document.createElement('div');
                paginationContainer.className = 'products-chart-pagination flex justify-between items-center mt-2 text-xs';
                
                // Previous button
                const prevButton = document.createElement('button');
                prevButton.className = 'chart-btn prev-page bg-gray-100 border border-gray-300 rounded';
                prevButton.textContent = '← Précédent';
                prevButton.addEventListener('click', function() {
                    if (state.currentPage > 1) {
                        state.currentPage--;
                        processChartData(state.rawData);
                    }
                });
                
                // Page indicator
                const pageIndicator = document.createElement('div');
                pageIndicator.className = 'page-indicator';
                updatePageIndicator(pageIndicator);
                
                // Next button
                const nextButton = document.createElement('button');
                nextButton.className = 'chart-btn next-page bg-gray-100 border border-gray-300 rounded';
                nextButton.textContent = 'Suivant →';
                nextButton.addEventListener('click', function() {
                    if (state.currentPage < state.totalPages) {
                        state.currentPage++;
                        processChartData(state.rawData);
                    }
                });
                
                // Add to container
                paginationContainer.appendChild(prevButton);
                paginationContainer.appendChild(pageIndicator);
                paginationContainer.appendChild(nextButton);
                
                // Show/hide pagination based on view mode
                togglePaginationVisibility();
                
                // Add to chart container
                chartContainer.appendChild(paginationContainer);
            }
            
            // Create summary
            let summaryContainer = chartContainer.querySelector('.products-chart-summary');
            if (!summaryContainer) {
                summaryContainer = document.createElement('div');
                summaryContainer.id = config.summaryId;
                summaryContainer.className = 'products-chart-summary mt-2 p-2 bg-gray-50 border border-gray-200 rounded text-xs';
                updateSummary(summaryContainer);
                
                // Add to chart container
                chartContainer.appendChild(summaryContainer);
            }
            
            console.log('[Products Chart] Enhanced controls created successfully');
        } catch (error) {
            console.error('[Products Chart] Error creating enhanced controls:', error);
        }
    }
    
    // Helper to update the page indicator
    function updatePageIndicator(indicator) {
        if (!indicator) return;
        
        indicator.textContent = `Page ${state.currentPage} sur ${state.totalPages}`;
    }
    
    // Helper to toggle pagination visibility based on view mode
    function togglePaginationVisibility() {
        const paginationContainer = document.querySelector('.products-chart-pagination');
        if (!paginationContainer) return;
        
        if (state.viewMode === 'paginated') {
            paginationContainer.style.display = 'flex';
        } else {
            paginationContainer.style.display = 'none';
        }
    }
    
    // Update summary with chart statistics
    function updateSummary(container) {
        if (!container || !state.processedData) return;
        
        try {
            // Calculate statistics
            let total = 0;
            let max = { label: '', value: 0 };
            let min = { label: '', value: Number.MAX_SAFE_INTEGER };
            
            state.processedData.forEach(item => {
                total += item.value;
                
                if (item.value > max.value) {
                    max = { label: item.label, value: item.value };
                }
                
                if (item.value < min.value) {
                    min = { label: item.label, value: item.value };
                }
            });
            
            // Format summary
            container.innerHTML = `
                <div class="font-semibold">Résumé des produits livrés</div>
                <div class="grid grid-cols-3 gap-2 mt-1">
                    <div>
                        <div class="font-medium">Total</div>
                        <div>${total} unités</div>
                    </div>
                    <div>
                        <div class="font-medium">Le plus livré</div>
                        <div>${max.label} (${max.value})</div>
                    </div>
                    <div>
                        <div class="font-medium">Le moins livré</div>
                        <div>${min.label} (${min.value})</div>
                    </div>
                </div>
                <div class="text-xs text-gray-500 mt-1">Mise à jour: ${new Date().toLocaleTimeString()}</div>
            `;
        } catch (error) {
            console.error('[Products Chart] Error updating summary:', error);
        }
    }
    
    // Exposed public method for refreshing the chart
    function refreshProductsChart(data) {
        console.log('[Products Chart] Refreshing products chart');
        
        try {
            const chartInstance = findChartInstance(config.chartId);
            if (!chartInstance) {
                console.warn('[Products Chart] Chart instance not found for refresh');
                
                // If no chart instance but we have initialization triggered already, retry init
                if (!state.isInitialized && state.retryCount < config.maxRetryAttempts) {
                    console.log('[Products Chart] Retrying initialization...');
                    setTimeout(init, config.retryDelay);
                }
                return;
            }
            
            // Extract fresh data from chart
            const chartData = extractChartData(chartInstance);
            if (!chartData) {
                console.warn('[Products Chart] No data available from chart for refresh');
                return;
            }
            
            // Update state
            state.rawData = chartData;
            state.fullData = chartData;
            
            // Process the new data
            processChartData(chartData);
            
            // Update the summary
            const summaryContainer = document.getElementById(config.summaryId);
            if (summaryContainer) {
                updateSummary(summaryContainer);
            }
            
            // Update pagination controls
            const paginationContainer = document.querySelector('.products-chart-pagination');
            if (paginationContainer) {
                const pageIndicator = paginationContainer.querySelector('.page-indicator');
                if (pageIndicator) {
                    updatePageIndicator(pageIndicator);
                }
                togglePaginationVisibility();
            }
            
            console.log('[Products Chart] Refresh completed successfully');
        } catch (error) {
            console.error('[Products Chart] Error refreshing products chart:', error);
        }
    }
    
    // Make refreshProductsChart accessible from outside if needed
    window.refreshProductsChart = refreshProductsChart;
})(); 