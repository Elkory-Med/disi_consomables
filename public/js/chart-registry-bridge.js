/**
 * Chart Registry Bridge
 * A central registry for chart instances to enable reliable chart access across scripts.
 * Version 1.0.0
 */
(function() {
    // Initialize chart registry if doesn't exist
    window.ChartRegistry = window.ChartRegistry || {
        // Storage for chart instances
        _instances: {},
        
        /**
         * Register a chart instance
         * @param {String} chartId - The ID of the chart
         * @param {Object} chartInstance - The chart instance object
         * @returns {Boolean} - True if registered successfully
         */
        registerChart: function(chartId, chartInstance) {
            if (!chartId || typeof chartId !== 'string' || !chartInstance) {
                console.error('[Chart Registry] Invalid chart registration parameters', { chartId, chartInstance });
                return false;
            }
            
            if (this._instances[chartId] === chartInstance) {
                // Already registered this exact instance
                return true;
            }
            
            console.log(`[Chart Registry] Registering chart: ${chartId}`);
            this._instances[chartId] = chartInstance;
            
            // Notify that a chart has been registered
            document.dispatchEvent(new CustomEvent('chartRegistered', {
                detail: { chartId: chartId }
            }));
            
            return true;
        },
        
        /**
         * Get a chart instance by ID
         * @param {String} chartId - The ID of the chart
         * @returns {Object|null} - The chart instance or null if not found
         */
        getChart: function(chartId) {
            return this._instances[chartId] || null;
        },
        
        /**
         * Check if a chart is registered
         * @param {String} chartId - The ID of the chart
         * @returns {Boolean} - True if the chart is registered
         */
        hasChart: function(chartId) {
            return !!this._instances[chartId];
        },
        
        /**
         * List all registered chart IDs
         * @returns {Array} - Array of chart IDs
         */
        getChartIds: function() {
            return Object.keys(this._instances);
        },
        
        /**
         * Unregister a chart instance
         * @param {String} chartId - The ID of the chart to unregister
         * @returns {Boolean} - True if unregistered successfully
         */
        unregisterChart: function(chartId) {
            if (this._instances[chartId]) {
                console.log(`[Chart Registry] Unregistering chart: ${chartId}`);
                delete this._instances[chartId];
                return true;
            }
            return false;
        },
        
        /**
         * Scan the page for available chart instances and register them
         * Useful for automatically capturing charts created elsewhere
         */
        scanForCharts: function() {
            console.log('[Chart Registry] Scanning for chart instances...');
            
            // Check ApexCharts instances if available
            if (window.ApexCharts && window.ApexCharts.instances) {
                window.ApexCharts.instances.forEach(instance => {
                    if (instance && instance.el && instance.el.id) {
                        this.registerChart(instance.el.id, instance);
                    }
                });
            }
            
            // Check global chartInstances collection (from dashboard-unified.js)
            if (window.chartInstances) {
                for (const chartId in window.chartInstances) {
                    if (window.chartInstances[chartId]) {
                        this.registerChart(chartId, window.chartInstances[chartId]);
                    }
                }
            }
            
            // Check ApexChartsInstances collection (from enhanced-products-chart.js)
            if (window.ApexChartsInstances) {
                for (const chartId in window.ApexChartsInstances) {
                    if (window.ApexChartsInstances[chartId]) {
                        this.registerChart(chartId, window.ApexChartsInstances[chartId]);
                    }
                }
            }
            
            console.log(`[Chart Registry] Scan complete. Found ${Object.keys(this._instances).length} charts.`);
        },
        
        /**
         * Handle chart creation event from ApexCharts
         * @param {Object} instance - ApexCharts instance
         */
        handleChartCreated: function(instance) {
            if (instance && instance.el && instance.el.id) {
                console.log(`[Chart Registry] Detected new chart: ${instance.el.id}`);
                this.registerChart(instance.el.id, instance);
                
                // Notify that a chart is ready
                document.dispatchEvent(new CustomEvent('chartReady', {
                    detail: { chartId: instance.el.id }
                }));
            }
        }
    };
    
    // Dispatch an event indicating the registry is ready
    document.dispatchEvent(new CustomEvent('chartRegistryReady'));
    
    // Intercept ApexCharts constructor if possible to auto-register charts
    if (typeof ApexCharts !== 'undefined' && !ApexCharts._registryPatched) {
        const originalApexCharts = ApexCharts;
        
        // Override the constructor to track new chart instances
        window.ApexCharts = function(element, options) {
            const instance = new originalApexCharts(element, options);
            
            // After render, register the chart
            const originalRender = instance.render;
            instance.render = function() {
                const result = originalRender.apply(this, arguments);
                
                // Register the chart after rendering
                if (element && element.id) {
                    console.log(`[Chart Registry] New chart rendered: ${element.id}`);
                    window.ChartRegistry.registerChart(element.id, instance);
                    
                    // Notify that a chart is ready
                    document.dispatchEvent(new CustomEvent('chartReady', {
                        detail: { chartId: element.id }
                    }));
                }
                
                return result;
            };
            
            return instance;
        };
        
        // Copy prototype and static properties
        window.ApexCharts.prototype = originalApexCharts.prototype;
        Object.setPrototypeOf(window.ApexCharts, originalApexCharts);
        
        // Mark as patched to avoid double patching
        ApexCharts._registryPatched = true;
        
        console.log('[Chart Registry] ApexCharts constructor intercepted for auto-registration');
    }
    
    // Scan for existing charts after a delay
    setTimeout(function() {
        window.ChartRegistry.scanForCharts();
    }, 1000);
    
    console.log('[Chart Registry] Chart registry bridge initialized');
})(); 