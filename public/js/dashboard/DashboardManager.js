/**
 * DashboardManager - Coordinates dashboard charts and global functionality
 * 
 * This class is responsible for initializing, refreshing, and coordinating
 * all charts on the admin dashboard. It handles global events and data loading.
 */
class DashboardManager {
    /**
     * Create a new Dashboard Manager
     * 
     * @param {Object} options - Configuration options
     */
    constructor(options = {}) {
        // Default options
        const defaultOptions = {
            endpoint: '/admin/dashboard/data',
            refreshInterval: 300000, // 5 minutes
            charts: [],
            chartsConfig: {},
            darkMode: false
        };
        
        // Merge options with defaults
        this.options = Object.assign({}, defaultOptions, options);
        
        // Reference to active charts
        this.activeCharts = [];
        
        // Dashboard data
        this.dashboardData = null;
        
        // Loading state
        this.isLoading = false;
        
        // Bind methods
        this.initializeCharts = this.initializeCharts.bind(this);
        this.refreshDashboard = this.refreshDashboard.bind(this);
        this.toggleDarkMode = this.toggleDarkMode.bind(this);
        this.handleFilterChange = this.handleFilterChange.bind(this);
        this.fetchDashboardData = this.fetchDashboardData.bind(this);
        
        // Initialize dashboard
        this.initialize();
    }
    
    /**
     * Initialize the dashboard
     */
    initialize() {
        // Set up event listeners
        this.setupEventListeners();
        
        // First data fetch
        this.refreshDashboard().then(() => {
            // Initialize charts once data is available
            this.initializeCharts();
            
            // Set up auto refresh if interval is set
            if (this.options.refreshInterval) {
                this.startAutoRefresh();
            }
            
            // Show dashboard (remove loading state)
            this.setLoadingState(false);
        }).catch(error => {
            console.error('Error initializing dashboard:', error);
            this.setLoadingState(false, true);
        });
    }
    
    /**
     * Set up dashboard event listeners
     */
    setupEventListeners() {
        // Refresh button
        const refreshBtn = document.getElementById('refresh-dashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.refreshDashboard();
            });
        }
        
        // Dark mode toggle
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleDarkMode();
            });
        }
        
        // Filter changes
        const filters = document.querySelectorAll('.dashboard-filter');
        filters.forEach(filter => {
            filter.addEventListener('change', this.handleFilterChange);
        });
        
        // Date range picker
        const dateRangePicker = document.getElementById('dashboard-daterange');
        if (dateRangePicker) {
            // Initialize date range picker if it exists
            if (typeof $(dateRangePicker).daterangepicker === 'function') {
                $(dateRangePicker).daterangepicker({
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    },
                    startDate: moment().subtract(29, 'days'),
                    endDate: moment(),
                    locale: {
                        format: 'DD/MM/YYYY'
                    }
                }, (start, end) => {
                    // Update date range text
                    $('#dashboard-daterange span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                    
                    // Refresh dashboard with new date range
                    this.handleFilterChange({
                        target: {
                            name: 'daterange',
                            value: {
                                start: start.format('YYYY-MM-DD'),
                                end: end.format('YYYY-MM-DD')
                            }
                        }
                    });
                });
            }
        }
        
        // Window resize handler for responsive charts
        window.addEventListener('resize', this.debounce(() => {
            this.resizeCharts();
        }, 250));
    }
    
    /**
     * Initialize all dashboard charts
     */
    initializeCharts() {
        // Clear any existing charts
        this.disposeCharts();
        
        // Initialize each chart in the configuration
        for (const [chartKey, ChartClass] of Object.entries(this.options.charts)) {
            // Check if the chart's DOM element exists
            const chartId = `${chartKey}-chart`;
            const chartElement = document.getElementById(chartId);
            
            if (chartElement) {
                try {
                    // Get chart-specific options
                    const chartOptions = this.options.chartsConfig[chartKey] || {};
                    
                    // Create chart instance
                    const chart = new ChartClass(chartId, {
                        ...chartOptions,
                        endpoint: this.options.endpoint,
                        darkMode: this.options.darkMode
                    });
                    
                    // Build chart with dashboard data
                    if (this.dashboardData) {
                        chart.buildChart(this.dashboardData);
                    }
                    
                    // Add to active charts
                    this.activeCharts.push(chart);
                } catch (error) {
                    console.error(`Error initializing chart ${chartKey}:`, error);
                }
            }
        }
    }
    
    /**
     * Clean up and dispose of all active charts
     */
    disposeCharts() {
        // Dispose each chart
        this.activeCharts.forEach(chart => {
            try {
                chart.dispose();
            } catch (error) {
                console.warn('Error disposing chart:', error);
            }
        });
        
        // Clear active charts array
        this.activeCharts = [];
    }
    
    /**
     * Resize all active charts
     */
    resizeCharts() {
        this.activeCharts.forEach(chart => {
            if (chart.chart && typeof chart.chart.resize === 'function') {
                chart.chart.resize();
            }
        });
    }
    
    /**
     * Start auto refresh of the dashboard
     */
    startAutoRefresh() {
        // Clear any existing interval
        this.stopAutoRefresh();
        
        // Set up new interval
        this.refreshInterval = setInterval(() => {
            this.refreshDashboard();
        }, this.options.refreshInterval);
    }
    
    /**
     * Stop auto refresh of the dashboard
     */
    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }
    
    /**
     * Refresh dashboard with the latest data
     */
    async refreshDashboard() {
        // Skip if already loading
        if (this.isLoading) {
            return Promise.reject(new Error('Dashboard refresh already in progress'));
        }
        
        // Set loading state
        this.setLoadingState(true);
        
        try {
            // Fetch latest dashboard data
            const data = await this.fetchDashboardData();
            
            // Store dashboard data
            this.dashboardData = data;
            
            // Update each chart with new data
            this.activeCharts.forEach(chart => {
                try {
                    chart.buildChart(data);
                } catch (error) {
                    console.error('Error updating chart:', error);
                }
            });
            
            // Update dashboard summary metrics
            this.updateDashboardSummary(data);
            
            // Clear loading state
            this.setLoadingState(false);
            
            return data;
        } catch (error) {
            console.error('Error refreshing dashboard:', error);
            
            // Set error state
            this.setLoadingState(false, true);
            
            throw error;
        }
    }
    
    /**
     * Fetch dashboard data from the server
     * 
     * @returns {Promise<Object>} Promise resolving to dashboard data
     */
    async fetchDashboardData() {
        try {
            // Add refresh parameter to prevent caching
            const url = new URL(this.options.endpoint, window.location.origin);
            url.searchParams.append('refresh', Date.now());
            
            // Get filter values
            const filters = document.querySelectorAll('.dashboard-filter');
            filters.forEach(filter => {
                if (filter.name && filter.value) {
                    url.searchParams.append(filter.name, filter.value);
                }
            });
            
            // Fetch data from endpoint
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            // Check if response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            // Parse response as JSON
            const data = await response.json();
            
            // Dispatch data loaded event
            const event = new CustomEvent('dashboard:data-loaded', { detail: { data } });
            document.dispatchEvent(event);
            
            // Return data
            return data;
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
            
            // Dispatch error event
            const event = new CustomEvent('dashboard:data-error', { detail: { error } });
            document.dispatchEvent(event);
            
            throw error;
        }
    }
    
    /**
     * Update dashboard summary metrics
     * 
     * @param {Object} data - Dashboard data
     */
    updateDashboardSummary(data) {
        // Check if summary data exists
        if (!data || !data.summary) {
            return;
        }
        
        // Update each summary card
        Object.entries(data.summary).forEach(([key, value]) => {
            const element = document.getElementById(`summary-${key}`);
            if (element) {
                element.textContent = value;
            }
            
            // Check for percentage change elements
            const changeElement = document.getElementById(`summary-${key}-change`);
            if (changeElement && data.summaryChange && data.summaryChange[key] !== undefined) {
                const change = data.summaryChange[key];
                
                // Update change text
                changeElement.textContent = `${change > 0 ? '+' : ''}${change.toFixed(1)}%`;
                
                // Update change class
                changeElement.className = 'ml-2';
                if (change > 0) {
                    changeElement.classList.add('text-success');
                } else if (change < 0) {
                    changeElement.classList.add('text-danger');
                } else {
                    changeElement.classList.add('text-info');
                }
            }
        });
    }
    
    /**
     * Set loading state for the dashboard
     * 
     * @param {boolean} isLoading - Whether the dashboard is loading
     * @param {boolean} isError - Whether there was an error
     */
    setLoadingState(isLoading, isError = false) {
        // Update loading state
        this.isLoading = isLoading;
        
        // Update loading spinner
        const loadingSpinner = document.getElementById('dashboard-loading');
        if (loadingSpinner) {
            loadingSpinner.style.display = isLoading ? 'block' : 'none';
        }
        
        // Update error message
        const errorMessage = document.getElementById('dashboard-error');
        if (errorMessage) {
            errorMessage.style.display = isError ? 'block' : 'none';
        }
        
        // Update refresh button
        const refreshBtn = document.getElementById('refresh-dashboard');
        if (refreshBtn) {
            refreshBtn.disabled = isLoading;
            
            // Show spinner in button if loading
            const btnSpinner = refreshBtn.querySelector('.spinner-border');
            if (btnSpinner) {
                btnSpinner.style.display = isLoading ? 'inline-block' : 'none';
            }
        }
    }
    
    /**
     * Handle filter change events
     * 
     * @param {Event} event - The change event
     */
    handleFilterChange(event) {
        // Refresh dashboard with new filter
        this.refreshDashboard();
    }
    
    /**
     * Toggle dark mode for the dashboard
     */
    toggleDarkMode() {
        // Toggle dark mode class on root element
        document.documentElement.classList.toggle('dark-mode');
        
        // Update dark mode option
        this.options.darkMode = document.documentElement.classList.contains('dark-mode');
        
        // Refresh all charts to update colors
        this.activeCharts.forEach(chart => {
            if (typeof chart.updateColors === 'function') {
                chart.updateColors();
                chart.refresh();
            }
        });
    }
    
    /**
     * Debounce function to limit the rate at which a function can fire
     * 
     * @param {Function} func - The function to debounce
     * @param {number} wait - The number of milliseconds to wait
     * @returns {Function} Debounced function
     */
    debounce(func, wait) {
        let timeout;
        
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Export for module usage
export default DashboardManager; 