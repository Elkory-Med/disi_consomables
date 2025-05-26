import BaseChart from '../BaseChart.js';

/**
 * UserDeliveryChart - Chart for displaying user delivery statistics
 * 
 * This chart shows the distribution of deliveries by user, emphasizing
 * top performers and total delivery counts.
 */
class UserDeliveryChart extends BaseChart {
    /**
     * Create a new user delivery chart instance
     * 
     * @param {string} chartId - The ID of the chart element in the DOM
     * @param {Object} options - Configuration options for the chart
     */
    constructor(chartId, options = {}) {
        // Set default options specific to this chart
        const defaultOptions = {
            chartType: 'bar',
            colors: ['#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56'],
            dataKey: 'userDeliveryStats', // Key in the API response
            topUsers: 10, // Number of top users to display
            filterInactiveUsers: true, // Filter users with 0 deliveries
            enableSorting: true // Enable sorting by delivery count
        };
        
        // Merge with base options and any user-provided options
        super(chartId, Object.assign({}, defaultOptions, options));
    }
    
    /**
     * Build the user delivery chart with the provided data
     * 
     * @param {Object} dashboardData - The full dashboard data from the API
     */
    buildChart(dashboardData) {
        // Get user delivery data from the response
        const data = dashboardData[this.options.dataKey] || 
                     { labels: [], series: [[]], totalDeliveries: 0 };
        
        // Process the data (sort, filter, limit)
        const processedData = this.processData(data);
        
        // Store the processed data
        this.chartData = processedData;
        
        // If there's no chart container, we can't proceed
        if (!this.chartElement) {
            console.error(`Chart element with ID ${this.chartId} not found in the DOM`);
            return;
        }
        
        // Destroy existing chart instance if it exists
        if (this.chart) {
            try {
                this.chart.destroy();
            } catch (e) {
                console.warn(`Error destroying user delivery chart: ${e.message}`);
            }
        }
        
        // Create the chart using Chart.js
        if (typeof Chart !== 'undefined') {
            const ctx = this.chartElement.getContext('2d');
            
            this.chart = new Chart(ctx, {
                type: this.options.chartType,
                data: {
                    labels: processedData.labels,
                    datasets: [{
                        label: 'Commandes livrées',
                        data: processedData.series[0],
                        backgroundColor: this.options.colors[0],
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.parsed.y} livraisons`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre de livraisons'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Utilisateurs'
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
            
            // Update delivery statistics on the dashboard if elements exist
            this.updateDeliveryStatistics(processedData);
        } else {
            console.error('Chart.js library not found. Please include Chart.js in your project.');
        }
    }
    
    /**
     * Process the chart data - sort, filter, and limit to top performers
     * 
     * @param {Object} data - Original chart data with labels and series
     * @returns {Object} Processed chart data
     */
    processData(data) {
        if (!data || !data.labels || !data.series || !data.series[0]) {
            return { 
                labels: ['No Data'], 
                series: [[0]],
                realValues: false
            };
        }
        
        const labels = [...data.labels];
        const series = [...data.series[0]];
        let totalDeliveries = data.totalDeliveries || series.reduce((sum, val) => sum + val, 0);
        let activeUsers = labels.length;
        
        // Create combined array for sorting
        let combined = labels.map((label, i) => ({
            label,
            value: series[i]
        }));
        
        // Filter inactive users if enabled
        if (this.options.filterInactiveUsers) {
            const originalLength = combined.length;
            combined = combined.filter(item => item.value > 0);
            activeUsers = combined.length;
        }
        
        // Sort by delivery count if enabled
        if (this.options.enableSorting) {
            combined.sort((a, b) => b.value - a.value);
        }
        
        // Limit to top users if specified
        if (this.options.topUsers > 0 && combined.length > this.options.topUsers) {
            combined = combined.slice(0, this.options.topUsers);
        }
        
        // Extract sorted/filtered data
        const processedLabels = combined.map(item => item.label);
        const processedSeries = combined.map(item => item.value);
        
        return {
            labels: processedLabels,
            series: [processedSeries],
            realValues: data.realValues !== false,
            totalDeliveries: totalDeliveries,
            activeUsers: activeUsers,
            totalUsers: data.labels.length,
            topUserCount: this.options.topUsers,
            isLimited: combined.length < data.labels.length
        };
    }
    
    /**
     * Update delivery statistics on the dashboard
     * 
     * @param {Object} data - Processed chart data
     */
    updateDeliveryStatistics(data) {
        // Update total deliveries if element exists
        const deliveryCountElement = document.getElementById('delivery-count');
        if (deliveryCountElement && data.totalDeliveries) {
            deliveryCountElement.textContent = BaseChart.formatNumber(data.totalDeliveries);
        }
        
        // Update active users count if element exists
        const activeUsersElement = document.getElementById('active-users');
        if (activeUsersElement && data.activeUsers) {
            activeUsersElement.textContent = data.activeUsers;
        }
        
        // Update total users count if element exists
        const totalUsersElement = document.getElementById('total-users');
        if (totalUsersElement && data.totalUsers) {
            totalUsersElement.textContent = data.totalUsers;
        }
    }
    
    /**
     * Generate fallback data for testing when no real data is available
     * 
     * @returns {Object} Fallback chart data
     */
    generateFallbackData() {
        const defaultUsers = [
            'Jean Dupont',
            'Marie Martin',
            'Thomas Bernard',
            'Sophie Petit',
            'Pierre Durand',
            'Julie Leroy',
            'Nicolas Moreau',
            'Camille Simon',
            'Alexandre Dubois',
            'Laura Michel'
        ];
        
        const values = defaultUsers.map(() => Math.floor(Math.random() * 100) + 1);
        const totalDeliveries = values.reduce((sum, val) => sum + val, 0);
        
        return {
            labels: defaultUsers,
            series: [values],
            realValues: false,
            totalDeliveries: totalDeliveries,
            totalUsers: defaultUsers.length,
            activeUsers: defaultUsers.length,
            isFallback: true,
            fallbackGenerated: new Date().toISOString()
        };
    }
}

// Export for module usage
export default UserDeliveryChart; 