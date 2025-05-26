/**
 * Final Dashboard Fix
 * This script adjusts the control sizes and spacings to match a more natural compact design
 * and helps synchronize chart initializations
 * Version 1.1.0
 */

(function() {
    console.log('Applying dashboard button and control fixes with natural sizing...');
    
    // Create a simple event emitter to help with chart synchronization
    if (!window.chartSyncEvents) {
        window.chartSyncEvents = {
            dispatch: function(eventName, data) {
                console.log(`[Chart Sync] Dispatching ${eventName} event`);
                document.dispatchEvent(new CustomEvent(eventName, { detail: data }));
            },
            chartReady: function(chartId) {
                this.dispatch('chartRefreshed', { chartId: chartId, timestamp: Date.now() });
            }
        };
    }
    
    // Help register chart instances in the global registry
    function registerChartInstances() {
        // Ensure ApexChartsInstances exists
        window.ApexChartsInstances = window.ApexChartsInstances || {};
        
        // Look for possible chart ids
        const chartIds = [
            'ordersOverviewChart', 
            'deliveredProductsChart',
            'orderTrendsChart',
            'userDeliveryChart',
            'orderStatusChart',
            'deliveredOrdersChart',
            'administrationChart'
        ];
        
        // Try to find each chart
        chartIds.forEach(chartId => {
            const chartElement = document.getElementById(chartId);
            if (!chartElement) return; // Skip if chart element doesn't exist
            
            // Check if we already have this chart instance
            if (window.ApexChartsInstances[chartId]) return;
            
            console.log(`[Dashboard Fix] Looking for chart instance for ${chartId}`);
            
            // Try different methods to find the chart instance
            if (typeof ApexCharts !== 'undefined') {
                try {
                    const instance = ApexCharts.getChartByID(chartId);
                    if (instance && instance.w && instance.w.globals) {
                        window.ApexChartsInstances[chartId] = instance;
                        console.log(`[Dashboard Fix] Found and registered chart instance for ${chartId}`);
                        
                        // Dispatch event that chart is ready
                        window.chartSyncEvents.chartReady(chartId);
                    }
                } catch (e) {
                    console.log(`[Dashboard Fix] Could not find ApexCharts instance for ${chartId} using getChartByID`);
                }
                
                // Try to find through ApexCharts.instances
                if (ApexCharts.instances) {
                    for (let id in ApexCharts.instances) {
                        const instance = ApexCharts.instances[id];
                        if (instance && instance.id === chartId) {
                            window.ApexChartsInstances[chartId] = instance;
                            console.log(`[Dashboard Fix] Found and registered chart instance for ${chartId} through instances`);
                            
                            // Dispatch event that chart is ready
                            window.chartSyncEvents.chartReady(chartId);
                            break;
                        }
                    }
                }
            }
        });
    }
    
    // Fix button heights and padding - more natural size
    document.querySelectorAll('.chart-btn').forEach(btn => {
        btn.style.height = '24px';
        btn.style.padding = '2px 8px';
        btn.style.lineHeight = '20px';
        btn.style.fontSize = '11px';
    });
    
    // Fix select and input heights - more natural size
    document.querySelectorAll('.chart-search, .chart-select').forEach(el => {
        el.style.height = '24px';
        el.style.lineHeight = '24px';
        el.style.padding = '2px 8px';
        el.style.fontSize = '11px';
    });
    
    // Fix page indicators - more natural size
    document.querySelectorAll('.page-indicator').forEach(el => {
        el.style.height = '24px';
        el.style.padding = '2px 8px';
        el.style.fontSize = '11px';
        el.style.lineHeight = '20px';
    });
    
    // Fix "par page" text - slightly larger
    document.querySelectorAll('.text-xs.text-gray-600.mr-1').forEach(el => {
        if (el.textContent.includes('par page')) {
            el.style.fontSize = '11px';
            el.style.marginRight = '4px';
        }
    });
    
    // Keep compact vertical spacing but not too cramped
    document.querySelectorAll('.flex.flex-col').forEach(el => {
        el.style.rowGap = '2px';
        if (el.classList.contains('mb-1')) {
            el.style.marginBottom = '4px';
        }
    });
    
    // Make flex containers with controls compact but not cramped
    document.querySelectorAll('.flex.flex-wrap.items-center, .flex.items-center, .flex.space-x-1, .flex.space-x-2').forEach(el => {
        el.style.gap = '4px';
        el.style.marginBottom = '4px';
        
        // Find any nesting children with gap or margin
        el.querySelectorAll('.flex').forEach(child => {
            child.style.gap = '4px';
            if (!child.style.marginLeft) {
                child.style.marginLeft = '0';
            }
        });
    });
    
    // Make h2 headings more natural sized
    document.querySelectorAll('h2.text-lg').forEach(el => {
        el.style.marginBottom = '6px';
        el.style.fontSize = '1rem';
    });
    
    // Add more vertical space for charts
    document.querySelectorAll('#orderStatusChart, #deliveredOrdersChart, #deliveredProductsChart, #administrationChart, #orderTrendsChart, #userDeliveryChart').forEach(chart => {
        chart.classList.remove('h-72');
        chart.classList.add('h-80');
    });
    
    // Set up periodic chart instance check and registration
    const checkInterval = setInterval(function() {
        registerChartInstances();
    }, 2000);
    
    // Stop checking after 30 seconds
    setTimeout(function() {
        clearInterval(checkInterval);
    }, 30000);
    
    // Refresh User Delivery Chart if needed
    setTimeout(function() {
        const userDeliveryChart = document.getElementById('userDeliveryChart');
        if (userDeliveryChart) {
            const content = userDeliveryChart.innerHTML || '';
            
            // If the chart is showing "no data" message but we know there should be data, refresh it
            if ((content.includes('Aucune donnée disponible') || content.trim() === '') && 
                window.livraisons_fix && typeof window.livraisons_fix.checkAndFixUserDeliveryChart === 'function') {
                console.log('Dashboard final fix: Checking user delivery chart which appears empty');
                
                // Try to refresh the data
                if (window.livraisons_fix.isWaitingForData) {
                    console.log('Dashboard final fix: Still waiting for data, not making a second request');
                } else {
                    console.log('Dashboard final fix: Trying to refresh user delivery chart data');
                    
                    // Look for a refresh button first
                    const refreshBtn = document.getElementById('refresh-dashboard') || 
                                      document.querySelector('button[id*="refresh"]');
                    
                    if (refreshBtn) {
                        console.log('Dashboard final fix: Found refresh button, clicking it');
                        refreshBtn.click();
                    } else {
                        console.log('Dashboard final fix: No refresh button found, running manual check');
                        window.livraisons_fix.checkAndFixUserDeliveryChart();
                    }
                }
            }
        }
    }, 3000); // Wait 3 seconds after page load
    
    // Initial registration of chart instances
    setTimeout(registerChartInstances, 1000);
    
    console.log('Dashboard fixes applied with more natural control sizes!');
})(); 