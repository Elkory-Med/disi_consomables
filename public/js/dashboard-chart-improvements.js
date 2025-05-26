/**
 * Dashboard Chart Improvements
 * Enhanced dashboard chart animations, tooltips, and interactions.
 * Version 2.1.0 - Integrated with Chart Registry
 */
(function() {
    // Configuration for chart enhancements
    const config = {
        version: '2.1.0',
        animationDuration: 800,
        maximumRetryAttempts: 25,
        retryInterval: 2000,
        chartIds: [
            'orderStatusChart', 
            'deliveredOrdersChart', 
            'orderTrendsChart',
            'administrationChart',
            'userDeliveryChart'
        ],
        debug: true
    };
    
    // Tracking for retry attempts
    const retryCount = {};
    
    /**
     * Log messages with consistent format if debug enabled
     */
    function log(message, data) {
        if (config.debug) {
            if (data) {
                console.log(`[Chart Improvements] ${message}`, data);
            } else {
                console.log(`[Chart Improvements] ${message}`);
            }
        }
    }
    
    /**
     * Validate if object is a chart instance
     */
    function isValidChartInstance(chart) {
            return chart && 
                typeof chart === 'object' && 
                typeof chart.updateOptions === 'function';
    }
    
    /**
     * Find a chart instance using multiple methods
     */
    function findChartInstance(chartId) {
        // First check in the registry if it's available
        if (window.ChartRegistry && typeof window.ChartRegistry.getChart === 'function') {
            const registryInstance = window.ChartRegistry.getChart(chartId);
            if (isValidChartInstance(registryInstance)) {
                log(`Found chart ${chartId} in ChartRegistry`);
                return registryInstance;
            }
        }
        
        // Try ApexChartsInstances global collection
            if (window.ApexChartsInstances && window.ApexChartsInstances[chartId]) {
                const instance = window.ApexChartsInstances[chartId];
            if (isValidChartInstance(instance)) {
                log(`Found chart ${chartId} in ApexChartsInstances`);
                    return instance;
                }
            }
            
        // Try chartInstances global collection (from dashboard-unified.js)
            if (window.chartInstances && window.chartInstances[chartId]) {
                const instance = window.chartInstances[chartId];
            if (isValidChartInstance(instance)) {
                log(`Found chart ${chartId} in chartInstances`);
                return instance;
            }
        }
        
        // Direct check for the chart in window context
        if (window[chartId] && isValidChartInstance(window[chartId])) {
            log(`Found chart ${chartId} as global variable`);
            return window[chartId];
        }
        
        // Try using ApexCharts.getChartByID
        if (typeof ApexCharts !== 'undefined' && typeof ApexCharts.getChartByID === 'function') {
            try {
                const instance = ApexCharts.getChartByID(chartId);
                if (isValidChartInstance(instance)) {
                    log(`Found chart ${chartId} using ApexCharts.getChartByID`);
                    return instance;
                }
            } catch (e) {
                // Ignore errors from getChartByID
                }
            }
            
        // Look directly in the DOM for the chart element
                const chartElement = document.getElementById(chartId);
        if (chartElement) {
            // Try to find ApexCharts instance attached to this element
            if (window.ApexCharts && window.ApexCharts.instances) {
                for (let i = 0; i < window.ApexCharts.instances.length; i++) {
                    const instance = window.ApexCharts.instances[i];
                    if (instance && instance.el && instance.el.id === chartId) {
                        log(`Found chart ${chartId} in ApexCharts.instances by element ID`);
                        // Register in global registries for future use
                        if (window.ApexChartsInstances) window.ApexChartsInstances[chartId] = instance;
                        if (window.ChartRegistry && typeof window.ChartRegistry.registerChart === 'function') {
                            window.ChartRegistry.registerChart(chartId, instance);
                        }
                        return instance;
                    }
                }
            }
            
            // Try to get chart from element's __proto__
            if (chartElement.__proto__ && chartElement.__proto__.chart) {
                const instance = chartElement.__proto__.chart;
                if (isValidChartInstance(instance)) {
                    log(`Found chart ${chartId} from DOM element prototype`);
                    return instance;
                }
            }
        }
        
        // Scan all properties in window for matching ApexCharts instances
        for (const prop in window) {
            try {
                const obj = window[prop];
                if (obj && typeof obj === 'object' && obj.w && 
                    typeof obj.updateOptions === 'function' && 
                    obj.el && obj.el.id === chartId) {
                    log(`Found chart ${chartId} by scanning window properties`);
                    // Register in global registries for future use
                    if (window.ApexChartsInstances) window.ApexChartsInstances[chartId] = obj;
                    if (window.ChartRegistry && typeof window.ChartRegistry.registerChart === 'function') {
                        window.ChartRegistry.registerChart(chartId, obj);
                    }
                    return obj;
            }
        } catch (e) {
                // Skip any properties that throw errors when accessed
            }
        }
        
        // If we get here, no chart instance was found
        return null;
    }
    
    /**
     * Apply improvements to a chart
     */
    function applyChartImprovements(chart, chartId) {
        if (!isValidChartInstance(chart)) {
            log(`Invalid chart instance for ${chartId}`);
            return false;
        }
        
        try {
            // Store original options to merge with enhancements
            const originalOptions = chart.w.config;
            
            // Base improvements for all charts
            const improvements = {
                chart: {
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: config.animationDuration,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                    toolbar: {
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        }
                    }
                },
                tooltip: {
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Poppins, sans-serif'
                    },
                    theme: document.body.classList.contains('dark-version') ? 'dark' : 'light',
                    fillSeriesColor: false
                },
                dataLabels: {
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Poppins, sans-serif',
                        fontWeight: 'bold'
                    }
                },
                legend: {
                    fontFamily: 'Poppins, sans-serif',
                    fontSize: '13px'
                }
            };
            
            // Apply chart-specific improvements
            if (chartId === 'orderStatusChart') {
                // Enhanced Order Status chart
                improvements.chart.toolbar.show = true;
                improvements.tooltip.y = {
                    formatter: function(value) {
                        return value + ' commandes';
                    }
                };
            } 
            else if (chartId === 'deliveredProductsChart') {
                // Enhanced Delivered Products chart
                improvements.chart.toolbar.show = true;
                improvements.tooltip.y = {
                    formatter: function(value) {
                        return value + ' produits';
                    }
                };
            }
            else if (chartId === 'orderTrendsChart') {
                // Enhanced Order Trends chart
                improvements.chart.toolbar.show = true;
                improvements.stroke = {
                    curve: 'smooth',
                    width: 3
                };
                improvements.tooltip.y = {
                    formatter: function(value) {
                        return value + ' commandes';
                    }
                };
            }
            else if (chartId === 'administrationChart') {
                // Enhanced Administration chart
                improvements.chart.toolbar.show = true;
                improvements.tooltip.y = {
                    formatter: function(value) {
                        return value + ' livraisons';
                    }
                };
            }
            else if (chartId === 'userDeliveryChart') {
                // Enhanced User Delivery chart
                improvements.chart.toolbar.show = true;
                improvements.tooltip.y = {
                    formatter: function(value) {
                        return value + ' commandes livrées';
                    }
                };
                improvements.plotOptions = {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '65%',
                        dataLabels: {
                            position: 'top'
                        }
                    }
                };
                improvements.dataLabels = {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        fontSize: '11px',
                        colors: ["#304758"]
                    },
                    formatter: function(val) {
                        return val > 0 ? val : '';
                    }
                };
                
                // Create summary section after enhancement
                setTimeout(function() {
                    createChartSummary(chart, chartId);
                }, 500);
            }
            
            // Update chart with improvements
            chart.updateOptions(improvements, false, true, true);
            
            // Register successful enhancement
            log(`Successfully applied improvements to ${chartId}`);
            
            // Flag chart as enhanced to avoid duplicate enhancements
            chart._enhancedWithVersion = config.version;
            
            return true;
        } catch (error) {
            log(`Error applying improvements to ${chartId}:`, error);
            return false;
        }
    }
    
    /**
     * Try to enhance a specific chart
     */
    function enhanceChart(chartId) {
        // Skip if maximum retry attempts reached
        if (retryCount[chartId] && retryCount[chartId] >= config.maximumRetryAttempts) {
            log(`Maximum retry attempts reached for ${chartId}. Unable to find chart instance.`);
            return false;
        }
        
        // Increment retry counter
        retryCount[chartId] = (retryCount[chartId] || 0) + 1;
        
        // Find chart instance
        const chart = findChartInstance(chartId);
        
        if (chart) {
            // Skip if already enhanced with current version
            if (chart._enhancedWithVersion === config.version) {
                log(`Chart ${chartId} already enhanced with version ${config.version}`);
                return true;
            }
            
            // Apply improvements
            if (applyChartImprovements(chart, chartId)) {
                // Reset retry counter on success
                retryCount[chartId] = 0;
                return true;
            }
        } else {
            log(`Chart ${chartId} not found (attempt ${retryCount[chartId]}/${config.maximumRetryAttempts})`);
            
            // Schedule retry
            if (retryCount[chartId] < config.maximumRetryAttempts) {
                setTimeout(function() {
                    enhanceChart(chartId);
                }, config.retryInterval);
            }
        }
        
        return false;
    }
    
    /**
     * Apply improvements to all charts in config
     */
    function enhanceAllCharts() {
        log(`Applying improvements to all dashboard charts (version ${config.version})...`);
        
        config.chartIds.forEach(function(chartId) {
            enhanceChart(chartId);
        });
    }
    
    /**
     * Re-check and apply improvements after data loads
     */
    function refreshChartImprovements() {
        log('Refreshing chart improvements after data update...');
        
        config.chartIds.forEach(function(chartId) {
            const chart = findChartInstance(chartId);
            
            if (chart && (!chart._enhancedWithVersion || chart._enhancedWithVersion !== config.version)) {
                applyChartImprovements(chart, chartId);
                            }
                        });
                    }
                    
    /**
     * Register interactivity events for a chart
     */
    function registerChartInteractivity(chart, chartId) {
        if (!chart || !chart.el) return;
        
        // Add hover class for visual feedback
        chart.el.addEventListener('mouseenter', function() {
            this.classList.add('chart-hover');
        });
        
        chart.el.addEventListener('mouseleave', function() {
            this.classList.remove('chart-hover');
        });
        
        log(`Registered interactivity events for ${chartId}`);
    }
    
    /**
     * Create a summary section for the chart showing totals and averages
     */
    function createChartSummary(chart, chartId) {
        if (!chart || !chart.el) return;
        
        try {
            // Get parent container of the chart
            const chartContainer = chart.el.closest('.bg-white.rounded-lg.shadow-md');
            if (!chartContainer) return;
            
            // Check if there's already a summary section
            const existingSummary = chartContainer.querySelector('.chart-summary-section');
            if (existingSummary) {
                existingSummary.remove();
            }
            
            // Get chart data
            const series = chart.w.config.series[0].data;
            if (!series || !series.length) return;
            
            // Calculate summary data
            const total = series.reduce((sum, val) => sum + (typeof val === 'number' ? val : 0), 0);
            const avg = total / series.filter(val => val > 0).length;
            const max = Math.max(...series);
            const min = Math.min(...series.filter(val => val > 0));
            
            // Create summary element
            const summarySection = document.createElement('div');
            summarySection.className = 'chart-summary-section flex justify-between items-center mt-1 px-2 text-xs text-gray-600';
            
            // Build summary content
            summarySection.innerHTML = `
                <div class="flex space-x-3">
                    <div>
                        <span class="font-medium">Total:</span> 
                        <span class="text-blue-600 font-semibold">${total.toLocaleString()} commandes</span>
                    </div>
                    <div>
                        <span class="font-medium">Moyenne:</span> 
                        <span class="text-green-600 font-semibold">${avg.toFixed(1)} par utilisateur</span>
                    </div>
                    <div>
                        <span class="font-medium">Max:</span> 
                        <span class="text-purple-600 font-semibold">${max.toLocaleString()}</span>
                    </div>
                </div>
                <div>
                    <span class="text-xs text-gray-400">Mise à jour: ${new Date().toLocaleTimeString()}</span>
                </div>
            `;
            
            // Remove any existing descriptive text at the bottom of the chart
            const existingDesc = chartContainer.querySelector('div.text-xs.text-gray-500');
            if (existingDesc && existingDesc.textContent.includes('Commandes marquées comme livrées')) {
                existingDesc.remove();
            }
            
            // Add summary section after the chart
            const chartElement = chartContainer.querySelector(`#${chartId}`);
            if (chartElement) {
                chartElement.insertAdjacentElement('afterend', summarySection);
                log(`Added summary section to ${chartId}`);
            }
        } catch (error) {
            log(`Error creating summary for ${chartId}:`, error);
        }
    }
    
    // Initialize and register events
    function initialize() {
        log('Initializing chart improvements...');
        
        // Listen for chart registry ready event
        document.addEventListener('chartRegistryReady', function() {
            log('Chart registry is ready, proceeding with enhancements');
            enhanceAllCharts();
        });
        
        // Listen for individual chart ready events
        document.addEventListener('chartReady', function(event) {
            if (event.detail && event.detail.chartId) {
                const chartId = event.detail.chartId;
                log(`Chart ready event received for ${chartId}`);
                
                if (config.chartIds.includes(chartId)) {
                    setTimeout(function() {
                        enhanceChart(chartId);
                    }, 500);
                }
            }
        });
        
        // Listen for chart registered events
        document.addEventListener('chartRegistered', function(event) {
            if (event.detail && event.detail.chartId) {
                const chartId = event.detail.chartId;
                log(`Chart registered event received for ${chartId}`);
                
                if (config.chartIds.includes(chartId)) {
                    setTimeout(function() {
                        enhanceChart(chartId);
                    }, 500);
                }
            }
        });
        
        // Listen for dashboard data loaded events
        document.addEventListener('dashboardDataLoaded', function() {
            log('Dashboard data loaded event received');
            setTimeout(refreshChartImprovements, 1000);
        });
        
        // Enhance charts initially after a delay
        setTimeout(enhanceAllCharts, 1500);
        
        // Schedule periodic checks to catch any missed charts
        const interval = setInterval(function() {
            const allComplete = config.chartIds.every(function(chartId) {
                const chart = findChartInstance(chartId);
                return chart && chart._enhancedWithVersion === config.version;
            });
            
            if (allComplete) {
                log('All charts enhanced successfully, stopping periodic checks');
                clearInterval(interval);
            } else {
                log('Periodic check for unenhanced charts...');
                refreshChartImprovements();
            }
        }, 5000);
        
        // Stop periodic checks after 2 minutes
        setTimeout(function() {
            clearInterval(interval);
            log('Stopped periodic chart enhancement checks');
        }, 120000);
    }
    
    // Run initialization immediately or when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
    
    log(`Chart improvements module loaded (version ${config.version})`);
})(); 