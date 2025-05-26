const defaultChartOptions = {
    chart: {
        toolbar: {
            show: false,
        },
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800
        }
    },
    colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#6366F1'],
    legend: {
        position: 'bottom',
        horizontalAlign: 'center'
    },
    plotOptions: {
        bar: {
            columnWidth: '70%',
            distributed: true,
            borderRadius: 4
        }
    },
    responsive: [
        {
            breakpoint: 480,
            options: {
                chart: {
                    width: '100%'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    ],
    noData: {
        text: 'Aucune donnée disponible',
        align: 'center',
        verticalAlign: 'middle',
        style: {
            fontSize: '16px',
            fontFamily: 'inherit'
        }
    }
};

// Chart instance storage - using global object to ensure it's accessible
window.chartInstances = {
    orderStatusChart: null,
    deliveredOrdersChart: null,
    deliveredProductsChart: null,
    productDistributionChart: null,
    orderTrendsChart: null
};

// Simplified helper to create or update a chart
function createOrUpdateChart(elementId, options) {
    console.log(`[CHART DEBUG] Creating/updating chart for ${elementId}`);
    
    const element = document.getElementById(elementId);
    if (!element) {
        console.error(`[CHART DEBUG] ${elementId}: Element not found`);
        return null;
    }
    
    console.log(`[CHART DEBUG] ${elementId}: Element found, dimensions: ${element.offsetWidth}x${element.offsetHeight}`);
    
    try {
        // Always create a new chart for simplicity (fixes potential issues with chart updates)
        if (window.chartInstances[elementId]) {
            console.log(`[CHART DEBUG] ${elementId}: Destroying existing chart`);
            window.chartInstances[elementId].destroy();
        }
        
        console.log(`[CHART DEBUG] ${elementId}: Creating new chart with options:`, options);
        window.chartInstances[elementId] = new ApexCharts(element, options);
        window.chartInstances[elementId].render();
        console.log(`[CHART DEBUG] ${elementId}: Chart rendered successfully`);
        return window.chartInstances[elementId];
    } catch (error) {
        console.error(`[CHART DEBUG] ${elementId}: Error creating chart:`, error);
        return null;
    }
}

// Modify chart functions to work with the actual data structure
export function orderStatusChart(chartData) {
    console.log('[CHART DEBUG] orderStatusChart called with data:', chartData);
    
    try {
        // Create basic options for a pie chart
        const options = {
            chart: {
                type: 'pie',
                height: 350
            },
            series: [1, 1, 1],  // Default values
            labels: ['En attente', 'Approuvée', 'Rejetée'],
            ...defaultChartOptions
        };
        
        // If we have valid data, use it - adapt to the actual structure
        if (chartData && chartData.orderStats) {
            // Using the nested structure with pending/approved/rejected
            // Convert string values to numbers explicitly
            const pendingOrders = Number(chartData.orderStats.pending?.orders || 0);
            const approvedOrders = Number(chartData.orderStats.approved?.orders || 0);
            const rejectedOrders = Number(chartData.orderStats.rejected?.orders || 0);
            
            options.series = [pendingOrders, approvedOrders, rejectedOrders];
        } else if (chartData && chartData.orderStatus) {
            if (chartData.orderStatus.pending && chartData.orderStatus.approved && chartData.orderStatus.rejected) {
                // Using the nested structure with pending/approved/rejected
                options.series = [
                    Number(chartData.orderStatus.pending.orders || 0), 
                    Number(chartData.orderStatus.approved.orders || 0), 
                    Number(chartData.orderStatus.rejected.orders || 0)
                ];
            } else if (Array.isArray(chartData.orderStatus.series)) {
                // Fallback to the original array structure if it exists
                options.series = chartData.orderStatus.series.map(val => Number(val) || 0);
                
                if (chartData.orderStatus.labels) {
                    options.labels = chartData.orderStatus.labels;
                }
            }
        }
        
        // Log data to debug
        console.log('[CHART DEBUG] Order status series data:', options.series);
        
        // If all values are zero, add a tiny value to make the chart visible
        if (options.series.every(val => val === 0)) {
            options.series = [0.1, 0.1, 0.1];
            console.log('[CHART DEBUG] All zero values, using placeholder');
        }
        
        // Ensure we have the right colors for each status
        options.colors = ['#FFC107', '#28A745', '#DC3545']; // Yellow, Green, Red
        
        console.log('[CHART DEBUG] Final orderStatusChart options:', options);
        
        // Create or update the chart
        return createOrUpdateChart('orderStatusChart', options);
    } catch (error) {
        console.error('[CHART DEBUG] Error in orderStatusChart:', error);
        return null;
    }
}

export function deliveredOrdersChart(chartData) {
    console.log('[CHART DEBUG] deliveredOrdersChart called with data:', chartData);
    
    try {
        // Create basic options for a donut chart
        const options = {
            chart: {
                type: 'donut',
                height: 350
            },
            series: [1, 1],  // Default values
            labels: ['Non livrées', 'Livrées'],
            colors: ['#FFA500', '#4CAF50'], // Orange for non-delivered, green for delivered
            ...defaultChartOptions
        };
        
        // If we have valid data, use it
        if (chartData && chartData.deliveredOrdersStats) {
            if (Array.isArray(chartData.deliveredOrdersStats.series) && chartData.deliveredOrdersStats.series.length > 0) {
                // Make sure we parse values as numbers (not integers)
                const series = chartData.deliveredOrdersStats.series.map(val => Number(val) || 0);
                options.series = series;
                
                if (Array.isArray(chartData.deliveredOrdersStats.labels)) {
                    options.labels = chartData.deliveredOrdersStats.labels;
                }
                
                console.log('[CHART DEBUG] Using deliveredOrdersStats.series:', series);
            }
        } else if (chartData && chartData.deliveredOrders) {
            if (Array.isArray(chartData.deliveredOrders.series) && chartData.deliveredOrders.series.length > 0) {
                // Make sure we parse values as numbers (not integers)
                const series = chartData.deliveredOrders.series.map(val => Number(val) || 0);
                
                if (series.every(val => val === 0)) {
                    // If all values are 0, use minimal values to make the chart visible
                    options.series = [1, 1];
                    console.log('[CHART DEBUG] All zero values in deliveredOrders.series, using placeholders');
                } else {
                    options.series = series;
                    
                    if (Array.isArray(chartData.deliveredOrders.labels)) {
                        options.labels = chartData.deliveredOrders.labels;
                    }
                    
                    console.log('[CHART DEBUG] Using deliveredOrders.series:', series);
                }
            }
        }
        
        // CRITICAL: Ensure we don't have any string values in the series
        options.series = options.series.map(val => Number(val) || 0);
        
        // Add data labels showing both percentage and actual count
        options.plotOptions = {
            pie: {
                donut: {
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => Number(a) + Number(b), 0);
                            }
                        }
                    }
                }
            }
        };
        
        options.dataLabels = {
            formatter: function(val, opts) {
                // Show both percentage and actual count
                const count = opts.w.globals.series[opts.seriesIndex];
                return `${Math.round(val)}% (${count})`;
            }
        };
        
        console.log('[CHART DEBUG] Final deliveredOrdersChart options:', options);
        
        // Create or update the chart
        return createOrUpdateChart('deliveredOrdersChart', options);
    } catch (error) {
        console.error('[CHART DEBUG] Error in deliveredOrdersChart:', error);
        return null;
    }
}

export function deliveredProductsChart(chartData) {
    console.log('[CHART DEBUG] deliveredProductsChart called with data:', chartData);
    
    try {
        // Create basic options for a pie chart
        const options = {
            chart: {
                type: 'pie',
                height: 350,
                animations: {
                    enabled: false // Disable animations for better performance
                }
            },
            series: [1],  // Default values
            labels: ['Aucune donnée'],
            ...defaultChartOptions
        };
        
        // If we have valid data, use it - check both possible data structures
        if (chartData && chartData.deliveredProducts) {
            // Check if we have series data
            if (Array.isArray(chartData.deliveredProducts.series) && chartData.deliveredProducts.series.length > 0) {
                // Convert to numbers and filter out zero values for better display
                const seriesData = chartData.deliveredProducts.series.map(val => Number(val) || 0);
                const labelsData = Array.isArray(chartData.deliveredProducts.labels) ? 
                    chartData.deliveredProducts.labels : ['Aucune donnée'];
                
                // Filter out zero values only if we have multiple values
                if (seriesData.length > 1) {
                    // Create pairs of labels and series values
                    const pairs = seriesData.map((value, index) => ({
                        value: Number(value),
                        label: labelsData[index] || `Item ${index+1}`
                    })).filter(pair => pair.value > 0);
                    
                    // If we have any non-zero values, use them
                    if (pairs.length > 0) {
                        options.series = pairs.map(pair => pair.value);
                        options.labels = pairs.map(pair => pair.label);
                        console.log('[CHART DEBUG] Filtered deliveredProducts data:', { series: options.series, labels: options.labels });
                    } else {
                        // All values are zero, use placeholder
                        options.series = [1];
                        options.labels = ['Aucune donnée'];
                        console.log('[CHART DEBUG] All values are zero in deliveredProducts');
                    }
                } else {
                    // Single value, use it if non-zero
                    if (seriesData[0] > 0) {
                        options.series = seriesData;
                        options.labels = labelsData;
                    } else {
                        options.series = [1];
                        options.labels = ['Aucune donnée'];
                    }
                }
            }
        }
        
        // CRITICAL: Ensure all values in the series are numbers
        options.series = options.series.map(val => Number(val) || 0);
        
        // Better visual options for the chart
        options.dataLabels = {
            enabled: true,
            formatter: function(val, opts) {
                // Only show percentage 
                return Math.round(val) + '%';
            },
            style: {
                fontSize: '12px',
                fontFamily: 'Helvetica, Arial, sans-serif',
                fontWeight: 'bold'
            }
        };
        
        options.tooltip = {
            y: {
                formatter: function(val) {
                    // Format numbers with thousands separator
                    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
                }
            }
        };
        
        // Use vibrant colors for better visibility
        options.colors = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', 
                         '#3F51B5', '#009688', '#FF5722', '#673AB7', '#795548'];
        
        console.log('[CHART DEBUG] Final deliveredProductsChart options:', options);
        
        // Create or update the chart
        return createOrUpdateChart('deliveredProductsChart', options);
    } catch (error) {
        console.error('[CHART DEBUG] Error in deliveredProductsChart:', error);
        return null;
    }
}

export function productDistributionChart(chartData) {
    console.log('[CHART DEBUG] productDistributionChart called with data:', chartData);
    
    try {
        // Create basic options for a bar chart
        const options = {
            chart: {
                type: 'bar',
                height: 350,
                animations: {
                    enabled: false // Disable animations for better performance with large datasets
                },
                toolbar: {
                    show: false // Hide toolbar for cleaner look
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    distributed: true // Use distributed columns for better categorical display
                }
            },
            dataLabels: {
                enabled: false // Disable data labels for cleaner chart with lots of data
            },
            series: [{
                name: 'Produits',
                data: [1]
            }],
            xaxis: {
                categories: ['Aucune donnée'],
                labels: {
                    style: {
                        fontSize: '12px'
                    },
                    rotate: -45, // Rotate labels for better readability with many categories
                    trim: true,
                    hideOverlappingLabels: true
                }
            },
            legend: {
                show: false // Hide legend since we're using distributed bars
            },
            ...defaultChartOptions
        };
        
        // If we have valid data, use it (check both userDistribution and productDistribution)
        let hasData = false;
        
        // First check if we have productDistribution data available
        if (chartData && chartData.productDistribution) {
            if (chartData.productDistribution.series && 
                ((Array.isArray(chartData.productDistribution.series.data)) || 
                 (Array.isArray(chartData.productDistribution.series) && chartData.productDistribution.series.length > 0))) {
                
                hasData = true;
                let seriesData, categoryLabels;
                
                // Check if series is an array of objects or a single object
                if (Array.isArray(chartData.productDistribution.series)) {
                    // Data is an array of series objects with data property
                    const firstSeries = chartData.productDistribution.series[0];
                    if (firstSeries && Array.isArray(firstSeries.data)) {
                        seriesData = firstSeries.data.map(val => Number(val) || 0);
                        options.series = [{
                            name: firstSeries.name || 'Produits',
                            data: seriesData
                        }];
                    }
                } else if (Array.isArray(chartData.productDistribution.series.data)) {
                    // Data is a single series object with data array
                    seriesData = chartData.productDistribution.series.data.map(val => Number(val) || 0);
                    options.series = [{
                        name: chartData.productDistribution.series.name || 'Produits',
                        data: seriesData
                    }];
                }
                
                // Set categories from either categories or labels property
                if (Array.isArray(chartData.productDistribution.categories)) {
                    options.xaxis.categories = chartData.productDistribution.categories;
                } else if (Array.isArray(chartData.productDistribution.labels)) {
                    options.xaxis.categories = chartData.productDistribution.labels;
                }
                
                console.log('[CHART DEBUG] Using productDistribution data');
            }
        }
        
        // If no productDistribution data, check userDistribution (legacy structure)
        if (!hasData && chartData && chartData.userDistribution) {
            if (chartData.userDistribution.series && 
                (Array.isArray(chartData.userDistribution.series.data) || 
                 (Array.isArray(chartData.userDistribution.series) && 
                  chartData.userDistribution.series[0] && 
                  Array.isArray(chartData.userDistribution.series[0].data)))) {
                
                hasData = true;
                let seriesData, categoryLabels;
                
                // Handle two possible data structures
                if (Array.isArray(chartData.userDistribution.series.data)) {
                    // Direct series object
                    seriesData = chartData.userDistribution.series.data.map(val => Number(val) || 0);
                    options.series = [{
                        name: chartData.userDistribution.series.name || 'Produits',
                        data: seriesData
                    }];
                } else if (Array.isArray(chartData.userDistribution.series)) {
                    // Array of series objects
                    if (chartData.userDistribution.series[0] && Array.isArray(chartData.userDistribution.series[0].data)) {
                        seriesData = chartData.userDistribution.series[0].data.map(val => Number(val) || 0);
                        options.series = [{
                            name: chartData.userDistribution.series[0].name || 'Produits',
                            data: seriesData
                        }];
                    }
                }
                
                // Set categories/labels for x-axis
                if (Array.isArray(chartData.userDistribution.categories)) {
                    options.xaxis.categories = chartData.userDistribution.categories;
                } else if (Array.isArray(chartData.userDistribution.labels)) {
                    options.xaxis.categories = chartData.userDistribution.labels;
                }
                
                console.log('[CHART DEBUG] Using userDistribution data');
            }
        }
        
        // Limit to at most 10 visible items for better display
        if (options.series[0].data.length > 10) {
            options.series[0].data = options.series[0].data.slice(0, 10);
            options.xaxis.categories = options.xaxis.categories.slice(0, 10);
            console.log('[CHART DEBUG] Limited to 10 items for better display');
        }
        
        // CRITICAL: Ensure all values in the series are numbers
        options.series[0].data = options.series[0].data.map(val => Number(val) || 0);
        
        // Fallback handling for empty data
        if (options.series[0].data.length === 0 || options.series[0].data.every(val => val === 0)) {
            options.series = [{
                name: 'Produits',
                data: [1]
            }];
            options.xaxis.categories = ['Aucune donnée'];
            console.log('[CHART DEBUG] No valid data in series, using placeholder');
        }
        
        console.log('[CHART DEBUG] Final productDistributionChart options:', options);
        
        // Create or update the chart
        return createOrUpdateChart('productDistributionChart', options);
    } catch (error) {
        console.error('[CHART DEBUG] Error in productDistributionChart:', error);
        return null;
    }
}

export function orderTrendsChart(chartData) {
    console.log('[CHART DEBUG] orderTrendsChart called with data:', chartData);
    
    try {
        // Create basic options for a line chart
        const options = {
            chart: {
                type: 'line',
                height: 350
            },
            series: [{
                name: 'Total',
                data: [1, 1, 1, 1, 1, 1, 1]
            }],
            xaxis: {
                categories: ['Jour 1', 'Jour 2', 'Jour 3', 'Jour 4', 'Jour 5', 'Jour 6', 'Jour 7']
            },
            ...defaultChartOptions
        };
        
        // If we have valid data, use it
        if (chartData && chartData.orderTrends) {
            let hasData = false;
            
            // Check if we have series data
            if (Array.isArray(chartData.orderTrends.series)) {
                if (Array.isArray(chartData.orderTrends.series[0])) {
                    // Direct array of data
                    options.series = [{
                        name: 'Total',
                        data: chartData.orderTrends.series[0]
                    }];
                    
                    // Add a second series if available
                    if (Array.isArray(chartData.orderTrends.series[1])) {
                        options.series.push({
                            name: 'Livrées',
                            data: chartData.orderTrends.series[1]
                        });
                    }
                    
                    hasData = true;
                } else if (chartData.orderTrends.series[0] && chartData.orderTrends.series[0].data) {
                    // Array of series objects
                    options.series = chartData.orderTrends.series;
                    hasData = true;
                }
            }
            
            // Set labels if available
            if (Array.isArray(chartData.orderTrends.labels)) {
                options.xaxis.categories = chartData.orderTrends.labels;
            }
            
            // If all values are 0, add a small value to make chart visible
            if (hasData && options.series[0].data.every(val => val === 0)) {
                // Instead of using a placeholder, keep the zeros but ensure the chart shows empty state properly
                options.series[0].data = options.series[0].data.map(() => 0.1);
            }
        }
        
        console.log('[CHART DEBUG] Final orderTrendsChart options:', options);
        
        // Create or update the chart
        return createOrUpdateChart('orderTrendsChart', options);
    } catch (error) {
        console.error('[CHART DEBUG] Error in orderTrendsChart:', error);
        return null;
    }
}

// Add a test function that can be called directly from the console
window.testChart = function(elementId) {
    console.log(`[CHART DEBUG] Manual test chart for ${elementId}`);
    
    try {
        const element = document.getElementById(elementId);
        if (!element) {
            console.error(`[CHART DEBUG] Test: Element ${elementId} not found`);
            return;
        }
        
        const options = {
            chart: {
                type: 'pie',
                height: 350
            },
            series: [44, 55, 41],
            labels: ['Test A', 'Test B', 'Test C'],
            title: {
                text: 'Manual Test Chart',
                align: 'center'
            }
        };
        
        const chart = new ApexCharts(element, options);
        chart.render();
        console.log(`[CHART DEBUG] Test chart for ${elementId} rendered successfully`);
        return chart;
    } catch (error) {
        console.error(`[CHART DEBUG] Test chart error:`, error);
        return null;
    }
};

// Add a force refresh function to handle all charts at once
export function forceRefreshCharts(chartData) {
    console.log('[CHART DEBUG] Force refreshing all charts');
    
    try {
        // Validate incoming data
        if (!chartData) {
            console.error('[CHART DEBUG] No data provided to forceRefreshCharts');
            chartData = {}; // Provide empty object to prevent errors
        }
        
        // Sanitize data to ensure we don't have string values in series
        if (chartData.orderStats) {
            if (chartData.orderStats.pending && typeof chartData.orderStats.pending.orders === 'string') {
                chartData.orderStats.pending.orders = Number(chartData.orderStats.pending.orders);
            }
            if (chartData.orderStats.approved && typeof chartData.orderStats.approved.orders === 'string') {
                chartData.orderStats.approved.orders = Number(chartData.orderStats.approved.orders);
            }
            if (chartData.orderStats.rejected && typeof chartData.orderStats.rejected.orders === 'string') {
                chartData.orderStats.rejected.orders = Number(chartData.orderStats.rejected.orders);
            }
        }
        
        if (chartData.deliveredOrdersStats && Array.isArray(chartData.deliveredOrdersStats.series)) {
            chartData.deliveredOrdersStats.series = chartData.deliveredOrdersStats.series.map(val => Number(val) || 0);
        }
        
        if (chartData.deliveredProducts && Array.isArray(chartData.deliveredProducts.series)) {
            chartData.deliveredProducts.series = chartData.deliveredProducts.series.map(val => Number(val) || 0);
        }
        
        // Fix userDistribution data
        if (chartData.userDistribution && chartData.userDistribution.series) {
            if (Array.isArray(chartData.userDistribution.series) && chartData.userDistribution.series[0] && Array.isArray(chartData.userDistribution.series[0].data)) {
                chartData.userDistribution.series[0].data = chartData.userDistribution.series[0].data.map(val => Number(val) || 0);
            }
        }
        
        // Clear any existing charts
        const chartIds = ['orderStatusChart', 'deliveredOrdersChart', 'deliveredProductsChart', 'productDistributionChart'];
        
        // Destroy any existing chart instances
        chartIds.forEach(chartId => {
            if (window.chartInstances && window.chartInstances[chartId]) {
                try {
                    window.chartInstances[chartId].destroy();
                    window.chartInstances[chartId] = null;
                } catch (e) {
                    console.error(`[CHART DEBUG] Error destroying ${chartId}:`, e);
                }
            }
            
            // Clear the container
            const container = document.getElementById(chartId);
            if (container) {
                container.innerHTML = '<div class="flex h-full items-center justify-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div></div>';
            }
        });
        
        // Log the sanitized data
        console.log('[CHART DEBUG] Sanitized chart data:', JSON.stringify(chartData));
        
        // Add a slight delay before recreating charts
        setTimeout(() => {
            try {
                // Recreate charts with provided data
                orderStatusChart(chartData);
                deliveredOrdersChart(chartData);
                deliveredProductsChart(chartData);
                productDistributionChart(chartData);
                
                console.log('[CHART DEBUG] All charts force refreshed');
            } catch (err) {
                console.error('[CHART DEBUG] Error recreating charts:', err);
            }
        }, 100);
    } catch (error) {
        console.error('[CHART DEBUG] Error in forceRefreshCharts:', error);
    }
}

// Make forceRefreshCharts available globally
window.forceRefreshCharts = forceRefreshCharts;
