import BaseChart from '../BaseChart.js';

/**
 * DeliveryStatusChart - Chart for displaying delivery status distribution
 * 
 * This chart shows the distribution of deliveries by status (confirmed, in transit, delivered, etc.)
 * using a pie chart visualization.
 */
class DeliveryStatusChart extends BaseChart {
    /**
     * Create a new delivery status chart instance
     * 
     * @param {string} chartId - The ID of the chart element in the DOM
     * @param {Object} options - Configuration options for the chart
     */
    constructor(chartId, options = {}) {
        // Set default options specific to this chart
        const defaultOptions = {
            chartType: 'pie',
            colors: [
                '#28a745', // Delivered - green
                '#fd7e14', // In Transit - orange
                '#17a2b8', // Confirmed - teal
                '#dc3545', // Cancelled - red
                '#6c757d', // Pending - gray
                '#007bff'  // Other - blue
            ],
            dataKey: 'deliveryStatusStats', // Key in the API response
            enablePercentages: true, // Show percentages in legend
            cutout: '30%'  // Doughnut-style cutout
        };
        
        // Merge with base options and any user-provided options
        super(chartId, Object.assign({}, defaultOptions, options));
    }
    
    /**
     * Build the delivery status chart with the provided data
     * 
     * @param {Object} dashboardData - The full dashboard data from the API
     */
    buildChart(dashboardData) {
        // Get delivery status data from the response
        const data = dashboardData[this.options.dataKey] || this.generateFallbackData();
        
        // Store the data
        this.chartData = data;
        
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
                console.warn(`Error destroying delivery status chart: ${e.message}`);
            }
        }
        
        // Create the chart using Chart.js
        if (typeof Chart !== 'undefined') {
            const ctx = this.chartElement.getContext('2d');
            const total = data.series[0].reduce((sum, value) => sum + value, 0);
            
            // Calculate percentages for tooltips if enabled
            const formattedLabels = data.labels.map((label, index) => {
                if (this.options.enablePercentages && total > 0) {
                    const percentage = ((data.series[0][index] / total) * 100).toFixed(1);
                    return `${label} (${percentage}%)`;
                }
                return label;
            });
            
            this.chart = new Chart(ctx, {
                type: this.options.chartType,
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.series[0],
                        backgroundColor: this.options.colors.slice(0, data.labels.length),
                        borderColor: 'rgba(255, 255, 255, 0.8)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: this.options.cutout,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        const dataset = data.datasets[0];
                                        const total = dataset.data.reduce((sum, value) => sum + value, 0);
                                        
                                        return data.labels.map(function(label, i) {
                                            const value = dataset.data[i];
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            const formattedLabel = options.enablePercentages ? 
                                                `${label}: ${value} (${percentage}%)` : 
                                                `${label}: ${value}`;
                                                
                                            return {
                                                text: formattedLabel,
                                                fillStyle: dataset.backgroundColor[i],
                                                lineWidth: 1,
                                                strokeStyle: dataset.borderColor || '#fff',
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Update any related UI elements with delivery status data
            this.updateDeliveryStatusInfo(data, total);
        } else {
            console.error('Chart.js library not found. Please include Chart.js in your project.');
        }
    }
    
    /**
     * Update delivery status information on the dashboard
     * 
     * @param {Object} data - The chart data
     * @param {number} total - The total number of deliveries
     */
    updateDeliveryStatusInfo(data, total) {
        // Update total deliveries counter
        const totalElement = document.getElementById('total-deliveries');
        if (totalElement) {
            totalElement.textContent = BaseChart.formatNumber(total);
        }
        
        // Update individual status counters
        data.labels.forEach((label, index) => {
            const elementId = `status-${label.toLowerCase().replace(/\s+/g, '-')}`;
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = BaseChart.formatNumber(data.series[0][index]);
            }
        });
        
        // Update chart summary message
        const summaryElement = document.getElementById('delivery-status-summary');
        if (summaryElement && total > 0) {
            const deliveredIndex = data.labels.findIndex(label => 
                label.toLowerCase().includes('livré') || 
                label.toLowerCase().includes('delivered'));
                
            if (deliveredIndex !== -1) {
                const deliveredCount = data.series[0][deliveredIndex];
                const deliveredPercentage = ((deliveredCount / total) * 100).toFixed(1);
                summaryElement.textContent = `${deliveredPercentage}% des commandes sont livrées`;
            }
        }
    }
    
    /**
     * Generate fallback data for testing when no real data is available
     * 
     * @returns {Object} Fallback chart data
     */
    generateFallbackData() {
        const statuses = [
            'Livré',
            'En cours',
            'Confirmé',
            'Annulé',
            'En attente'
        ];
        
        const values = [
            Math.floor(Math.random() * 300) + 200, // Delivered
            Math.floor(Math.random() * 100) + 50,  // In Transit
            Math.floor(Math.random() * 80) + 20,   // Confirmed
            Math.floor(Math.random() * 30) + 5,    // Cancelled
            Math.floor(Math.random() * 50) + 10    // Pending
        ];
        
        return {
            labels: statuses,
            series: [values],
            realValues: false,
            isFallback: true,
            fallbackGenerated: new Date().toISOString()
        };
    }
}

// Export for module usage
export default DeliveryStatusChart; 