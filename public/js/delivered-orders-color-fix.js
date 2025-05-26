/**
 * Delivered Orders Color Fix
 * 
 * This script provides a color fix for the delivered orders chart to ensure consistent colors
 * are used across the dashboard for better visual coherence.
 */

(function() {
    document.addEventListener('DOMContentLoaded', function() {
        // Set up a listener for when the delivered orders chart gets created or recreated
        document.addEventListener('chart-data-updated', function(e) {
            setTimeout(applyColorFix, 500);
        });
        
        // Initial attempt after a short delay
        setTimeout(applyColorFix, 1000);
    });
    
    function applyColorFix() {
        const chartElement = document.getElementById('deliveredOrdersChart');
        if (!chartElement) return;
        
        // Find the ApexCharts instance
        const chartInstance = findChartInstance(chartElement);
        if (!chartInstance) return;
        
        // Verify this is a valid ApexCharts instance
        if (typeof chartInstance.updateOptions !== 'function') {
            console.warn('Found chart instance but it doesn\'t appear to be a valid ApexCharts instance');
            return;
        }
        
        // Apply consistent colors to the chart
        const enhancedColors = [
            '#4680FF', // blue
            '#10B981', // green
            '#F59E0B', // amber
            '#3B82F6', // indigo
            '#EF4444', // red
            '#8B5CF6', // purple
            '#14B8A6', // teal
            '#EC4899'  // pink
        ];
        
        // Update the chart colors
        try {
            chartInstance.updateOptions({
                colors: enhancedColors,
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.2,
                        opacityFrom: 0.85,
                        opacityTo: 0.95,
                        stops: [0, 100]
                    }
                }
            });
        } catch (err) {
            console.error('Error applying color fix to delivered orders chart:', err);
        }
    }
    
    function findChartInstance(element) {
        if (!element) return null;
        
        // Check specific global variable
        if (window.chartInstances && window.chartInstances.deliveredOrdersChart) {
            return window.chartInstances.deliveredOrdersChart;
        }
        
        // Direct global variable check
        if (window.deliveredOrdersChart) {
            return window.deliveredOrdersChart;
        }
        
        // Check if there's a global ApexCharts registry we can access
        if (window.ApexChartsInstances) {
            for (const chart of window.ApexChartsInstances) {
                if (chart.el === element) {
                    return chart;
                }
            }
        }
        
        // Alternative method: ApexCharts might store the instance on the element
        if (element && element._chart) {
            return element._chart;
        }
        
        // Check data attributes
        if (element.dataset && element.dataset.apexchartsId) {
            const chartId = element.dataset.apexchartsId;
            if (window.ApexCharts && window.ApexCharts.charts) {
                return window.ApexCharts.charts[chartId];
            }
        }
        
        // Check ApexCharts registry
        if (window.ApexCharts) {
            // Check ApexCharts.instances
            if (window.ApexCharts.instances) {
                for (const chart of window.ApexCharts.instances) {
                    if (chart.el === element) {
                        return chart;
                    }
                }
            }
            
            // Check ApexCharts.charts
            if (window.ApexCharts.charts) {
                for (const chartId in window.ApexCharts.charts) {
                    const chart = window.ApexCharts.charts[chartId];
                    if (chart && chart.el === element) {
                        return chart;
                    }
                }
            }
        }
        
        // Look through all window properties for ApexCharts instances
        for (const prop in window) {
            if (window[prop] && 
                typeof window[prop] === 'object' && 
                window[prop].constructor && 
                window[prop].constructor.name === 'ApexCharts' &&
                window[prop].el === element) {
                return window[prop];
            }
        }
        
        return null;
    }
})(); 