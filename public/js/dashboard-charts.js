/**
 * DASHBOARD CHARTS MANAGER
 * Optimized and production-ready charts renderer
 * Handles large datasets and provides consistent visuals
 * 
 * @version 1.0.0
 */

class DashboardCharts {
    /**
     * Initialize the Dashboard Charts Manager
     * @param {Object} options - Configuration options
     */
    constructor(options = {}) {
        // Configuration
        this.config = {
            dataEndpoint: options.dataEndpoint || '/admin/dashboard/data',
            refreshInterval: options.refreshInterval || null, // In milliseconds, null for no auto-refresh
            refreshThrottle: options.refreshThrottle || 2000, // Minimum time between refreshes
            debugMode: options.debugMode || false,
            containerIds: {
                orderStatus: options.orderStatusContainer || 'orderStatusChart',
                deliveredOrders: options.deliveredOrdersContainer || 'deliveredOrdersChart',
                orderTrends: options.orderTrendsContainer || 'orderTrendsChart',
                deliveredProducts: options.deliveredProductsContainer || 'deliveredProductsChart',
                userDistribution: options.userDistributionContainer || 'userDistributionChart',
            }
        };
        
        // State management
        this.state = {
            charts: {},
            data: null,
            configs: null,
            lastRefreshTime: 0,
            isLoading: false,
            isInitialized: false,
            error: null
        };
        
        // Make this instance globally available
        window.dashboardManager = this;
        
        // Log info if in debug mode
        this.log('Dashboard Charts Manager initialized');
    }
    
    /**
     * Initialize all charts
     */
    async initialize() {
        if (this.state.isInitialized) {
            this.log('Already initialized');
            return;
        }
        
        this.log('Initializing Dashboard Charts Manager');
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Load data and create charts
        try {
            // Fetch fresh data
            await this.refreshData(true);
            
            // Set up auto-refresh if configured
            if (this.config.refreshInterval) {
                this.startAutoRefresh();
            }
            
            this.state.isInitialized = true;
            this.log('Dashboard Charts Manager initialization complete');
            
            // Announce that charts are initialized
            document.dispatchEvent(new CustomEvent('dashboardChartsInitialized', {
                detail: { charts: Object.keys(this.state.charts) }
            }));
        } catch (error) {
            console.error('Error initializing charts:', error);
            this.state.error = error.message;
            this.showErrorMessage('Failed to initialize charts: ' + error.message);
        }
    }
    
    /**
     * Set up event listeners for the dashboard
     */
    setupEventListeners() {
        // Handle refresh button click
        const refreshButton = document.getElementById('refresh-dashboard-btn');
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                this.log('Refresh button clicked');
                this.refreshData(true);
            });
        }
        
        // Listen for external refresh requests (from other scripts)
        document.addEventListener('refreshDashboard', (e) => {
            this.log('Received refreshDashboard event');
            const bypassCache = e.detail?.bypassCache ?? true;
            this.refreshData(bypassCache);
        });
        
        // Support for Livewire-based dashboard
        document.addEventListener('chart-data-updated', (e) => {
            this.log('Received chart-data-updated event from Livewire');
            if (e.detail) {
                this.state.data = e.detail;
                this.updateSummaryNumbers(this.state.data);
                this.updateAllCharts();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', this.debounce(() => {
            this.log('Window resized, updating charts');
            this.updateAllCharts();
        }, 250));
    }
    
    /**
     * Fetch data from the server and update charts
     * @param {boolean} bypassCache - Whether to bypass cache
     * @returns {Promise} Promise that resolves when data is fetched and charts are updated
     */
    async refreshData(bypassCache = false) {
        const now = Date.now();
        
        // Throttle refreshes to prevent flooding
        if (now - this.state.lastRefreshTime < this.config.refreshThrottle) {
            this.log('Refresh throttled - too soon since last refresh');
            return Promise.resolve(null);
        }
        
        // Show loading state
        this.showLoadingState(true);
        this.state.isLoading = true;
        this.state.lastRefreshTime = now;
        
        try {
            this.log('Fetching dashboard data...');
            
            // Build URL with cache-busting parameters if needed
            let url = this.config.dataEndpoint;
            if (bypassCache) {
                const cacheBuster = `nocache=${Date.now()}`;
                url += url.includes('?') ? `&${cacheBuster}` : `?${cacheBuster}`;
                
                // Add refresh parameter
                url += '&refresh=1';
            }
            
            // Make the request
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Cache-Control': bypassCache ? 'no-cache' : 'default'
                },
                cache: bypassCache ? 'no-store' : 'default'
            });
            
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status}`);
            }
            
            const responseData = await response.json();
            
            // Store the data and configs
            this.state.data = responseData.raw_data;
            this.state.configs = responseData.chart_configs;
            
            // Update summary numbers
            this.updateSummaryNumbers(this.state.data);
            
            // Update all charts
            this.updateAllCharts();
            
            // Hide error message if it was shown
            this.hideErrorMessage();
            
            // Log caching info
            this.log(`Data fetched successfully (cache ${responseData.cache_bypassed ? 'bypassed' : 'used'})`);
            
            // Announce that data has been refreshed
            document.dispatchEvent(new CustomEvent('dashboardDataRefreshed', {
                detail: { 
                    timestamp: responseData.timestamp,
                    cacheBypassed: responseData.cache_bypassed
                }
            }));
            
            return responseData;
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
            this.state.error = error.message;
            this.showErrorMessage(`Failed to fetch dashboard data: ${error.message}`);
            return null;
        } finally {
            // Hide loading state
            this.showLoadingState(false);
            this.state.isLoading = false;
        }
    }
    
    /**
     * Update all charts with the latest data
     */
    updateAllCharts() {
        if (!this.state.configs) {
            this.log('No chart configurations available');
            return;
        }
        
        this.log('Updating all charts with new configurations');
        
        // Update each chart
        this.updateChart(this.config.containerIds.orderStatus, this.state.configs.orderStatusChart);
        this.updateChart(this.config.containerIds.deliveredOrders, this.state.configs.deliveredOrdersChart);
        this.updateChart(this.config.containerIds.orderTrends, this.state.configs.orderTrendsChart);
        this.updateChart(this.config.containerIds.deliveredProducts, this.state.configs.deliveredProductsChart);
        this.updateChart(this.config.containerIds.userDistribution, this.state.configs.userDistributionChart);
    }
    
    /**
     * Update a specific chart
     * @param {string} containerId - ID of the chart container
     * @param {Object} config - Chart configuration
     */
    updateChart(containerId, config) {
        if (!config) {
            this.log(`No configuration for ${containerId}`);
            return;
        }
        
        const container = document.getElementById(containerId);
        if (!container) {
            this.log(`Chart container '${containerId}' not found`);
            return;
        }
        
        try {
            // If a chart instance already exists
            if (this.state.charts[containerId]) {
                this.log(`Updating existing ${containerId} chart`);
                
                try {
                    // For pie charts
                    if (config.chart.type === 'pie' || config.chart.type === 'donut') {
                        this.state.charts[containerId].updateOptions({
                            labels: config.labels,
                            series: config.series,
                            colors: config.colors
                        });
                    } 
                    // For other chart types
                    else {
                        this.state.charts[containerId].updateOptions({
                            series: config.series,
                            xaxis: config.xaxis,
                            colors: config.colors
                        });
                    }
                } catch (updateError) {
                    console.error(`Error updating ${containerId} chart:`, updateError);
                    
                    // If update fails, recreate the chart
                    this.destroyChart(containerId);
                    this.createChart(containerId, container, config);
                }
            } 
            // Otherwise create a new chart
            else {
                this.createChart(containerId, container, config);
            }
        } catch (error) {
            console.error(`Error processing ${containerId} chart:`, error);
            container.innerHTML = `<div class="alert alert-warning">Unable to render chart</div>`;
        }
    }
    
    /**
     * Create a new chart
     * @param {string} id - Chart ID
     * @param {HTMLElement} container - Chart container element
     * @param {Object} config - Chart configuration
     */
    createChart(id, container, config) {
        // Clear the container
        container.innerHTML = '';
        
        // Prepare the configuration (handle special formatter functions)
        const chartConfig = this.prepareChartConfig(config);
        
        this.log(`Creating new ${id} chart with type ${config.chart.type}`);
        
        try {
            // Create the chart instance
            const chart = new ApexCharts(container, chartConfig);
            
            // Render the chart
            chart.render();
            
            // Store the chart instance
            this.state.charts[id] = chart;
            
            // For backward compatibility, also store on window
            window[id] = chart;
            
            this.log(`Created new ${id} chart`);
        } catch (error) {
            console.error(`Error creating ${id} chart:`, error);
            container.innerHTML = `<div class="alert alert-warning">Unable to render chart. Error: ${error.message}</div>`;
        }
    }
    
    /**
     * Destroy a chart instance
     * @param {string} id - Chart ID
     */
    destroyChart(id) {
        if (this.state.charts[id]) {
            try {
                this.state.charts[id].destroy();
                delete this.state.charts[id];
                this.log(`Destroyed ${id} chart`);
            } catch (error) {
                console.warn(`Error destroying ${id} chart:`, error);
            }
        }
    }
    
    /**
     * Update summary numbers in the dashboard
     * @param {Object} data - Dashboard data
     */
    updateSummaryNumbers(data) {
        if (!data) return;
        
        // Update total orders
        this.updateElement('total-orders', data.revenue?.totalOrders || data.orderStats?.total || 0);
        
        // Update delivered orders
        this.updateElement('delivered-orders', 
            data.revenue?.deliveredOrders || 
            data.deliveredOrdersStats?.delivered || 
            data.orderStats?.delivered?.orders || 0);
        
        // Update total users
        this.updateElement('total-users', data.userDistribution?.totalUsers || 0);
        
        // Update revenue
        this.updateElement('total-revenue', 
            data.revenue?.total || 0, 
            (value) => new Intl.NumberFormat('fr-FR', { 
                style: 'currency', 
                currency: 'EUR'
            }).format(value));
    }
    
    /**
     * Update an element with formatted value
     * @param {string} id - Element ID
     * @param {*} value - Value to display
     * @param {Function} formatter - Optional formatter function
     */
    updateElement(id, value, formatter = null) {
        const element = document.getElementById(id);
        if (!element) return;
        
        element.textContent = formatter ? formatter(value) : value;
    }
    
    /**
     * Prepare chart configuration for ApexCharts
     * @param {Object} config - Chart configuration
     * @returns {Object} Processed configuration
     */
    prepareChartConfig(config) {
        // Deep clone the config to avoid modifying the original
        const processed = JSON.parse(JSON.stringify(config));
        
        // Convert formatter strings to functions
        this.processFormatters(processed);
        
        return processed;
    }
    
    /**
     * Process formatter strings in configuration and convert them to functions
     * @param {Object} obj - Object to process
     */
    processFormatters(obj) {
        for (const key in obj) {
            if (typeof obj[key] === 'object' && obj[key] !== null) {
                this.processFormatters(obj[key]);
            } else if (key === 'formatter' && typeof obj[key] === 'string' && obj[key].startsWith('function')) {
                // Convert formatter string to function
                try {
                    obj[key] = new Function('return ' + obj[key])();
                } catch (error) {
                    console.error('Error parsing formatter function:', error);
                }
            }
        }
    }
    
    /**
     * Show or hide loading state
     * @param {boolean} isLoading - Whether loading is in progress
     */
    showLoadingState(isLoading) {
        const loadingElement = document.getElementById('dashboard-loading');
        if (loadingElement) {
            loadingElement.style.display = isLoading ? 'flex' : 'none';
        }
    }
    
    /**
     * Show error message
     * @param {string} message - Error message to display
     */
    showErrorMessage(message) {
        const errorElement = document.getElementById('dashboard-error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }
    
    /**
     * Hide error message
     */
    hideErrorMessage() {
        const errorElement = document.getElementById('dashboard-error');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
    
    /**
     * Start auto-refresh interval
     */
    startAutoRefresh() {
        if (this.autoRefreshInterval) {
            clearInterval(this.autoRefreshInterval);
        }
        
        this.autoRefreshInterval = setInterval(() => {
            this.log('Auto-refreshing dashboard data');
            this.refreshData(true);
        }, this.config.refreshInterval);
        
        this.log(`Auto-refresh started (every ${this.config.refreshInterval / 1000} seconds)`);
    }
    
    /**
     * Stop auto-refresh interval
     */
    stopAutoRefresh() {
        if (this.autoRefreshInterval) {
            clearInterval(this.autoRefreshInterval);
            this.autoRefreshInterval = null;
            this.log('Auto-refresh stopped');
        }
    }
    
    /**
     * Debounce function to limit how often a function is called
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {Function} Debounced function
     */
    debounce(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }
    
    /**
     * Logging helper
     * @param {string} message - Message to log
     */
    log(message) {
        if (this.config.debugMode) {
            console.log(`[Dashboard Charts] ${message}`);
        }
    }
}

// Initialize when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a dashboard page
    const dashboardContainer = document.getElementById('admin-dashboard-container');
    if (!dashboardContainer) return;
    
    // Create and initialize the dashboard charts manager
    const dashboardCharts = new DashboardCharts({
        debugMode: true // Set to false in production
    });
    
    dashboardCharts.initialize();
    
    // Announce global availability
    document.dispatchEvent(new CustomEvent('dashboardManagerLoaded', {
        detail: { version: '1.0.0' }
    }));
}); 