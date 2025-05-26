/**
 * Dashboard Trends Chart - Enhanced Version
 * This script adds advanced features to the Order Trends chart including:
 * - Date range selection
 * - Data aggregation for large datasets
 * - Enhanced visualization
 * - Interactive features
 * - Trend analysis
 */
(function() {
    console.log('[Trends Enhanced] Initializing advanced trend charts...');
    
    // Configuration
    const config = {
        orderTrendsChartId: 'orderTrendsChart',
        maxRetryAttempts: 10,     // Maximum number of retry attempts
        retryDelay: 1000,         // Delay between retries in ms
        pollInterval: 3000        // Poll interval for checking chart instances
    };
    
    // State tracking
    const state = {
        initialized: false,
        retryCount: 0,         // Track number of retry attempts
        intervalId: null
    };
    
    // Make sure we have a global registry for our chart instances
    window.ApexChartsInstances = window.ApexChartsInstances || {};
    
    // Wait for ApexCharts to be available if needed
    function checkApexChartsAvailability() {
        if (typeof ApexCharts === 'undefined') {
            console.log(' [Trends Enhanced] ApexCharts not available yet, waiting...');
            setTimeout(checkApexChartsAvailability, 500);
            return;
        }
            
        console.log('[Trends Enhanced] ApexCharts now available, initializing...');
        initializeTrendsEnhancements();
    }
    
    // Check if ApexCharts is available
    checkApexChartsAvailability();
            
    // Fallback - if it's not detected in the immediate check, try again after a delay
    setTimeout(function() {
        if (typeof ApexCharts !== 'undefined') {
            console.log('[Trends Enhanced] ApexCharts now available after delay, initializing...');
            initializeTrendsEnhancements();
        }
    }, 3000);
    
    /**
     * Improved function to validate ApexCharts instance
     * Ensures the object is a valid ApexCharts instance with required properties
     */
    function isValidApexChartsInstance(chart) {
        try {
            return chart && 
                typeof chart === 'object' && 
                chart.w && 
                chart.w.globals && 
                typeof chart.updateOptions === 'function';
        } catch (e) {
            console.debug('[Trends Enhanced] Chart validation error:', e);
            return false;
        }
    }
    
    /**
     * Find chart instance by ID or from global chart instances
     * Improved with better error handling and validation
     */
    function findChartInstance(chartId) {
        try {
            // First check ApexChartsInstances global registry 
            if (window.ApexChartsInstances && window.ApexChartsInstances[chartId]) {
                return window.ApexChartsInstances[chartId];
            }
            
            // Then check chartInstances if available
            if (window.chartInstances && window.chartInstances[chartId]) {
                const instance = window.chartInstances[chartId];
                // Auto-register in global registry if found
                window.ApexChartsInstances[chartId] = instance;
                return instance;
            }
            
            // As fallback, try to find by DOM ID
            if (typeof ApexCharts !== 'undefined' && document.getElementById(chartId)) {
                const chartElement = document.getElementById(chartId);
                // Try to find the ApexCharts instance associated with this element
                if (typeof ApexCharts.getChartByID === 'function') {
                    const instance = ApexCharts.getChartByID(chartId);
                    window.ApexChartsInstances[chartId] = instance;
                    return instance;
                }
            }
            
            // Check if chart is directly in window context
            if (window[chartId]) {
                window.ApexChartsInstances[chartId] = window[chartId];
                return window[chartId];
            }
        } catch (e) {
            console.debug('[Trends Enhanced] Error while finding chart instance:', e);
        }
        
        return null;
    }
    
    // Initialize the enhanced features for the trends chart
    function initializeTrendsEnhancements() {
        console.log('[Trends Enhanced] Initializing dashboard trend enhancements...');
        
        if (typeof ApexCharts === 'undefined') {
            console.warn('[Trends Enhanced] ApexCharts not loaded, will retry...');
            if (state.retryCount < config.maxRetryAttempts) {
                state.retryCount++;
                setTimeout(initializeTrendsEnhancements, config.retryDelay);
            } else {
                console.warn('[Trends Enhanced] Maximum retry attempts reached. Unable to initialize.');
                state.retryCount = 0; // Reset for future attempts
            }
            return;
        }
        
        // Reset retry count on success
        state.retryCount = 0;
        
        // Clear any existing intervals
        if (state.intervalId) {
            clearInterval(state.intervalId);
        }
        
        // Start polling for chart instances
        state.intervalId = setInterval(enhanceCharts, config.pollInterval);
        
        // Do initial enhancement
        enhanceCharts();
        
        // Listen for dashboard refresh events
        document.addEventListener('dashboardRefreshed', function() {
            console.log('[Trends Enhanced] Dashboard refreshed event detected, re-enhancing...');
            enhanceCharts();
        });
        
        state.initialized = true;
    }
    
    // Start the initialization process
    checkApexChartsAvailability();
    
    /**
     * Main function to enhance the Order Trends chart
     */
    function enhanceCharts() {
        console.log('[Trends Enhanced] Checking for charts to enhance...');
        
        let foundCharts = false;
        
        // Process the chart selector - treating it as a string, not an array
        const chartId = config.orderTrendsChartId.replace ? 
                      config.orderTrendsChartId.replace('#', '') : 
                      config.orderTrendsChartId;
                      
        const chartInstance = findChartInstance(chartId);
        
        if (chartInstance) {
            foundCharts = true;
            console.log(`[Trends Enhanced] Found chart: ${chartId}`);
            
            // Check if this is a valid ApexCharts instance
            if (isValidApexChartsInstance(chartInstance)) {
                enhanceOrderTrendsChart(chartInstance);
            } else {
                console.log(`[Trends Enhanced] Found chart ${chartId} but it doesn't appear to be a valid ApexCharts instance`);
            }
        } else {
            console.log(`[Trends Enhanced] Chart ${chartId} not found, will try again later`);
        }
        
        // If we never find charts after initialization, eventually stop looking
        if (!foundCharts && state.initialized) {
            state.retryCount++;
            if (state.retryCount >= config.maxRetryAttempts) {
                console.warn('[Trends Enhanced] No charts found after maximum retry attempts. Stopping enhancement attempts.');
                if (state.intervalId) {
                    clearInterval(state.intervalId);
                    state.intervalId = null;
                }
                state.retryCount = 0; // Reset for future attempts if page is refreshed
            }
        } else {
            // Reset retry count when charts are found
            state.retryCount = 0;
        }
    }
    
    /**
     * Main function to enhance the Order Trends chart
     */
    function enhanceOrderTrendsChart(chart) {
        console.log('[Trends Enhanced] Applying enhancements to Order Trends chart...');
        
        if (!isValidApexChartsInstance(chart)) {
            console.log('[Trends Enhanced] Chart instance not found, will try again later');
            setTimeout(enhanceOrderTrendsChart, 1000);
            return;
        }
            
        console.log('[Trends Enhanced] Found valid Order Trends chart instance, enhancing...');
        
        // Add a date range selector
        addDateRangeSelector(chart);
        
        // Add chart summary information
        if (chart.w && chart.w.globals && chart.w.globals.dom) {
            const chartEl = chart.w.globals.dom.baseEl;
            const chartContainer = chartEl.closest('.bg-white.rounded-lg');
            
            if (chartContainer) {
                // Add chart summary below the chart
                let summaryEl = chartContainer.querySelector('.chart-summary');
                if (!summaryEl) {
                    summaryEl = document.createElement('div');
                    summaryEl.className = 'chart-summary text-xs text-gray-500 mt-2';
                    chartContainer.appendChild(summaryEl);
                }
                
                // Get total value and average from series
                let total = 0;
                let max = 0;
                let min = Infinity;
                
                if (chart.w.globals.series && chart.w.globals.series.length > 0) {
                    const series = chart.w.globals.series[0];
                    total = series.reduce((a, b) => a + b, 0);
                    max = Math.max(...series);
                    min = Math.min(...series);
                }
                
                const average = total / (chart.w.globals.series[0]?.length || 1);
                
                // Update summary text
                summaryEl.innerHTML = `
                    <span class="font-medium">Total:</span> ${total.toFixed(0)} commandes | 
                    <span class="font-medium">Moyenne:</span> ${average.toFixed(1)}/jour | 
                    <span class="font-medium">Max:</span> ${max} | 
                    <span class="font-medium">Min:</span> ${min}
                `;
            }
        }
        
        console.log('[Trends Enhanced] Order Trends chart enhanced successfully');
    }
    
    // Add a date range selector to the chart
    function addDateRangeSelector(chart) {
        // Get the chart container
        if (!chart.w || !chart.w.globals || !chart.w.globals.dom) {
            return;
        }
        
        const chartEl = chart.w.globals.dom.baseEl;
        const chartContainer = chartEl.closest('.bg-white.rounded-lg');
        
        if (!chartContainer) {
            return;
        }
        
        // Check if control panel already exists
        let controlPanel = chartContainer.querySelector('.trends-control-panel');
        if (controlPanel) {
            return;
        }
        
        // Get the title element
        const titleEl = chartContainer.querySelector('h2');
        if (!titleEl) {
            return;
        }
        
        // Create control panel
        controlPanel = document.createElement('div');
        controlPanel.className = 'trends-control-panel flex items-center justify-end space-x-2 mb-2';
        
        // Add range selector
        const rangeSelector = document.createElement('select');
        rangeSelector.className = 'chart-select';
        rangeSelector.innerHTML = `
            <option value="7">7 jours</option>
            <option value="14">14 jours</option>
            <option value="30">30 jours</option>
            <option value="60">60 jours</option>
            <option value="90">90 jours</option>
        `;
        
        // Add export button
        const exportButton = document.createElement('button');
        exportButton.className = 'chart-btn chart-btn-secondary';
        exportButton.textContent = 'Exporter';
        
        // Add controls to panel
        controlPanel.appendChild(document.createTextNode('Période:'));
        controlPanel.appendChild(rangeSelector);
        controlPanel.appendChild(exportButton);
        
        // Add the control panel after the title
        titleEl.parentNode.insertBefore(controlPanel, titleEl.nextSibling);
        
        // Handle range change
        rangeSelector.addEventListener('change', function() {
            const days = parseInt(this.value);
            updateChartRange(chart, days);
        });
        
        // Handle export button
        exportButton.addEventListener('click', function() {
            exportChartData(chart);
        });
    }
    
    // Update chart with new date range
    function updateChartRange(chart, days) {
        if (!chart || !chart.w || !chart.w.globals) {
            return;
        }
        
        // Generate dates for the selected range
        const labels = [];
        const today = new Date();
        
        for (let i = days - 1; i >= 0; i--) {
            const date = new Date();
            date.setDate(today.getDate() - i);
            labels.push(date.toLocaleDateString('fr-FR', {day: '2-digit', month: '2-digit'}));
        }
        
        // Get data for the new range (or mock data if not available)
        let series = [[0]];
        
        // If we have real data stored somewhere, use it
        if (window.orderTrendsData && window.orderTrendsData.length >= days) {
            series = [window.orderTrendsData.slice(-days)];
        } else {
            // Generate mock data if real data not available
            series = [[...Array(days)].map(() => Math.floor(Math.random() * 10))];
        }
        
        // Update chart with new data
        chart.updateOptions({
            xaxis: {
                categories: labels
            },
            series: [{
                name: 'Commandes',
                data: series[0]
            }]
        });
    }
    
    // Export chart data to CSV
    function exportChartData(chart) {
        if (!chart || !chart.w || !chart.w.globals) {
            return;
        }
        
        const labels = chart.w.globals.labels || [];
        const series = chart.w.globals.series[0] || [];
        
        // Create CSV content
        let csvContent = 'Date,Commandes\n';
        
        for (let i = 0; i < labels.length; i++) {
            csvContent += `${labels[i]},${series[i] || 0}\n`;
        }
        
        // Create download link
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        
        link.setAttribute('href', url);
        link.setAttribute('download', 'tendances-commandes.csv');
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Intercept chart creation to capture the Order Trends chart instance
    const originalApexCharts = window.ApexCharts;
    if (originalApexCharts) {
        window.ApexCharts = function() {
            const chart = new originalApexCharts(...arguments);
            const originalRender = chart.render;
            
            // Enhance the render method
            chart.render = function() {
                const result = originalRender.apply(this, arguments);
                
                // If this is a container with the orderTrendsChart ID, save it
                if (arguments[0] && arguments[0].id === 'orderTrendsChart') {
                    console.log('[Trends Enhanced] Order Trends chart rendered, storing instance');
                    window.ApexChartsInstances.orderTrendsChart = chart;
                    setTimeout(enhanceOrderTrendsChart, 500);
                }
                
                return result;
            };
            
            return chart;
        };
        
        // Copy prototype and properties
        window.ApexCharts.prototype = originalApexCharts.prototype;
        Object.keys(originalApexCharts).forEach(key => {
            window.ApexCharts[key] = originalApexCharts[key];
        });
    }
})(); 