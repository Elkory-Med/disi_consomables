/**
 * Dashboard Enhanced Charts - Additional functionality for ApexCharts in Dashboard
 * This script adds:
 * - Interactive tooltips
 * - Chart annotations
 * - Export functionality
 * - Synchronized views
 * - Dynamic data updates
 */
(function() {
    console.log('[Dashboard Enhanced] Initializing enhanced dashboard charts...');
    
    // Ensure ApexCharts is loaded before continuing
    function checkApexChartsAvailability() {
        if (typeof ApexCharts === 'undefined') {
            console.warn('ApexCharts not loaded! Dashboard chart enhancements postponed.');
            return false;
        }
        return true;
    }
    
    // Ensure we have a global registry for our chart instances
    window.ApexChartsInstances = window.ApexChartsInstances || {};
    
    /**
     * Improved function to validate ApexCharts instance
     */
    function isValidApexChartsInstance(chart) {
        try {
            return chart && 
                typeof chart === 'object' && 
                chart.w && 
                chart.w.globals && 
                typeof chart.updateOptions === 'function';
        } catch (e) {
            console.debug('[Dashboard Enhanced] Chart validation error:', e);
            return false;
        }
    }
    
    /**
     * Find chart instance by ID with better error handling
     */
    function findChartInstance(chartId) {
        try {
            // First check ApexChartsInstances global registry 
            if (window.ApexChartsInstances && window.ApexChartsInstances[chartId]) {
                const instance = window.ApexChartsInstances[chartId];
                if (isValidApexChartsInstance(instance)) {
                    return instance;
                }
            }
            
            // Then check chartInstances if available
            if (window.chartInstances && window.chartInstances[chartId]) {
                const instance = window.chartInstances[chartId];
                if (isValidApexChartsInstance(instance)) {
                    // Auto-register in global registry if found
                    window.ApexChartsInstances[chartId] = instance;
                    return instance;
                }
            }
            
            // As fallback, try to find by DOM ID
            const chartElement = document.getElementById(chartId);
            if (chartElement) {
                if (typeof ApexCharts !== 'undefined' && typeof ApexCharts.getChartByID === 'function') {
                    const instance = ApexCharts.getChartByID(chartId);
                    if (isValidApexChartsInstance(instance)) {
                        window.ApexChartsInstances[chartId] = instance;
                        return instance;
                    }
                }
            }
        } catch (e) {
            console.debug('[Dashboard Enhanced] Error finding chart instance:', e);
        }
        
        return null;
    }
    
    // One-time check for ApexCharts availability
    if (!checkApexChartsAvailability()) {
        // Set a single timeout to check again later
        setTimeout(function() {
            if (checkApexChartsAvailability()) {
                console.log('[Dashboard Enhanced] ApexCharts now available, initializing enhancements...');
                initEnhancedCharts();
            } else {
                console.warn('[Dashboard Enhanced] ApexCharts still not available, enhancements canceled.');
            }
        }, 2000);
        return;
    }
    
    // Proceed with initialization
    initEnhancedCharts();
    
    function initEnhancedCharts() {
        // Configuration for each chart we want to enhance
        const chartsConfig = [
            {
                id: 'orderStatusChart',
                title: 'Order Status',
                tooltipEnhanced: true,
                zoomable: true
            },
            {
                id: 'deliveredProductsChart',
                title: 'Delivered Products',
                tooltipEnhanced: true,
                zoomable: true
            },
            {
                id: 'orderTrendsChart',
                title: 'Order Trends',
                tooltipEnhanced: true,
                zoomable: true 
            },
            {
                id: 'administrationChart',
                title: 'Administration',
                tooltipEnhanced: true,
                zoomable: true 
            }
        ];
        
        // Apply enhancements to each chart
        chartsConfig.forEach(enhanceChart);
        
        // Add event listeners for any dashboard layout changes
        window.addEventListener('resize', handleResize);
        
        // Initial dashboard setup
        updateDashboardLayout();
    }
    
    // Check if charts exist every 2 seconds for a limited time
    let checkCount = 0;
    const maxChecks = 10;
    const checkInterval = setInterval(function() {
        checkCount++;
        
        if (checkCount >= maxChecks) {
            clearInterval(checkInterval);
            console.log('[Dashboard Enhanced] Finished checking for chart instances');
            return;
        }
        
        // Check for new chart instances
        const chartsConfig = [
            { id: 'orderStatusChart' },
            { id: 'deliveredProductsChart' },
            { id: 'orderTrendsChart' },
            { id: 'administrationChart' }
        ];
        
        let foundAny = false;
        
        chartsConfig.forEach(config => {
            const chartInstance = findChartInstance(config.id);
            if (chartInstance) {
                foundAny = true;
                console.log(`[Dashboard Enhanced] Found chart: ${config.id}`);
                enhanceChart({
                    id: config.id,
                    title: config.id.replace('Chart', '').replace(/([A-Z])/g, ' $1').trim(),
                    tooltipEnhanced: true,
                    zoomable: true
                });
            }
        });
        
        if (foundAny) {
            checkCount = maxChecks - 1; // Just one more check
        }
        
    }, 2000);
    
    /**
     * Enhance a specific chart with advanced features
     */
    function enhanceChart(chartConfig) {
        const chartId = chartConfig.id;
        const chartInstance = findChartInstance(chartId);
        
        if (!chartInstance) {
            console.log(`[Dashboard Enhanced] Chart '${chartId}' not found, will try again later`);
            return;
        }
        
        console.log(`[Dashboard Enhanced] Enhancing chart: ${chartId}`);
        
        // 1. Add zoom capabilities if configured
        if (chartConfig.zoomable) {
            addZoomCapabilities(chartInstance, chartId);
        }
        
        // 2. Enhance tooltips if configured
        if (chartConfig.tooltipEnhanced) {
            enhanceTooltips(chartInstance, chartId);
        }
        
        // 3. Add custom title
        updateChartTitle(chartInstance, chartConfig.title || chartId);
        
        // Store enhanced state
        window.ApexChartsInstances[chartId] = chartInstance;
    }
    
    /**
     * Add zoom capabilities to chart
     */
    function addZoomCapabilities(chart, chartId) {
        try {
            if (!isValidApexChartsInstance(chart)) {
                console.warn(`[Dashboard Enhanced] Invalid chart instance for zoom: ${chartId}`);
                return;
            }
            
            // Get current options
            const currentOptions = chart.w.globals.initialConfig;
            
            // Prepare new options with zoom capabilities
            const zoomOptions = {
                chart: {
                    zoom: {
                    enabled: true,
                        type: 'x',
                        autoScaleYaxis: true
                    },
                    toolbar: {
                        show: true,
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
                }
            };
            
            // Update chart with new options
            chart.updateOptions(zoomOptions);
            console.log(`[Dashboard Enhanced] Added zoom capabilities to ${chartId}`);
        } catch (e) {
            console.error(`[Dashboard Enhanced] Error adding zoom to ${chartId}:`, e);
        }
    }
    
    /**
     * Enhance tooltips with more information
     */
    function enhanceTooltips(chart, chartId) {
        try {
            if (!isValidApexChartsInstance(chart)) {
                console.warn(`[Dashboard Enhanced] Invalid chart instance for tooltips: ${chartId}`);
                return;
            }
            
            // Enhance tooltip
            const tooltipOptions = {
                tooltip: {
                    shared: true,
                    intersect: false,
                    theme: 'dark',
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Helvetica, Arial, sans-serif'
                    },
                    onDatasetHover: {
                        highlightDataSeries: true
                    },
                    x: {
                        show: true,
                        format: 'dd MMM yyyy'
                    },
                    marker: {
                        show: true
                    }
                }
            };
            
            // Update chart options
            chart.updateOptions(tooltipOptions);
            console.log(`[Dashboard Enhanced] Enhanced tooltips for ${chartId}`);
        } catch (e) {
            console.error(`[Dashboard Enhanced] Error enhancing tooltips for ${chartId}:`, e);
        }
    }
    
    /**
     * Update chart title with custom title
     */
    function updateChartTitle(chart, title) {
        try {
            if (!isValidApexChartsInstance(chart)) {
                console.warn(`[Dashboard Enhanced] Invalid chart instance for title update: ${title}`);
                return;
            }
            
            // Update title
            chart.updateOptions({
                title: {
                    text: title,
                    align: 'center',
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold',
                        color: '#263238'
                    }
                }
            });
        } catch (e) {
            console.error(`[Dashboard Enhanced] Error updating title for ${title}:`, e);
        }
    }
    
    /**
     * Handle window resize events
     */
    function handleResize() {
        // Update dashboard layout when window resizes
        updateDashboardLayout();
    }
    
    /**
     * Update dashboard layout
     */
    function updateDashboardLayout() {
        // Logic to update dashboard layout if needed
        // This could involve adjusting chart sizes, positions, etc.
    }
})(); 