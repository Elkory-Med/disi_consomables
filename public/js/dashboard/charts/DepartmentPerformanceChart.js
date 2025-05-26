import BaseChart from '../BaseChart.js';

/**
 * DepartmentPerformanceChart - Chart for displaying department performance metrics
 * 
 * This chart shows delivery performance metrics across different departments
 * using a horizontal bar chart visualization.
 */
class DepartmentPerformanceChart extends BaseChart {
    /**
     * Create a new department performance chart instance
     * 
     * @param {string} chartId - The ID of the chart element in the DOM
     * @param {Object} options - Configuration options for the chart
     */
    constructor(chartId, options = {}) {
        // Set default options specific to this chart
        const defaultOptions = {
            chartType: 'bar',
            colors: [
                '#007bff', // Primary color for deliveries
                '#28a745', // Success color for completed deliveries
                '#dc3545'  // Danger color for issues/delays
            ],
            dataKey: 'departmentStats',
            sortBy: 'total', // 'total', 'avgTime', 'efficiency'
            limit: 8, // Limit to top X departments
            indexAxis: 'y' // Horizontal bar chart
        };
        
        // Merge with base options and any user-provided options
        super(chartId, Object.assign({}, defaultOptions, options));
    }
    
    /**
     * Build the department performance chart with the provided data
     * 
     * @param {Object} dashboardData - The full dashboard data from the API
     */
    buildChart(dashboardData) {
        // Get department data from the response
        const data = dashboardData[this.options.dataKey] || this.generateFallbackData();
        
        // Store the data
        this.chartData = data;
        
        // Process and sort the data according to options
        const processedData = this.processData(data);
        
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
                console.warn(`Error destroying department performance chart: ${e.message}`);
            }
        }
        
        // Create the chart using Chart.js
        if (typeof Chart !== 'undefined') {
            const ctx = this.chartElement.getContext('2d');
            
            // Prepare datasets based on the processed data
            const datasets = [
                {
                    label: 'Livraisons totales',
                    data: processedData.map(item => item.total),
                    backgroundColor: this.options.colors[0],
                    borderColor: 'rgba(255, 255, 255, 0.8)',
                    borderWidth: 1
                }
            ];
            
            // Add efficiency dataset if available
            if (processedData[0].hasOwnProperty('efficiency')) {
                datasets.push({
                    label: 'Efficacité (%)',
                    data: processedData.map(item => item.efficiency),
                    backgroundColor: this.options.colors[1],
                    borderColor: 'rgba(255, 255, 255, 0.8)',
                    borderWidth: 1,
                    // Use a second y-axis for efficiency percentages
                    yAxisID: 'y1'
                });
            }
            
            // Add average delivery time if available
            if (processedData[0].hasOwnProperty('avgTime')) {
                datasets.push({
                    label: 'Temps moyen (min)',
                    data: processedData.map(item => item.avgTime),
                    backgroundColor: this.options.colors[2],
                    borderColor: 'rgba(255, 255, 255, 0.8)',
                    borderWidth: 1,
                    // Use a third y-axis for time values
                    yAxisID: 'y2'
                });
            }
            
            this.chart = new Chart(ctx, {
                type: this.options.chartType,
                data: {
                    labels: processedData.map(item => item.department),
                    datasets: datasets
                },
                options: {
                    indexAxis: this.options.indexAxis,
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre de livraisons'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Département'
                            }
                        },
                        y1: datasets.length > 1 ? {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Efficacité (%)'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        } : null,
                        y2: datasets.length > 2 ? {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Temps moyen (min)'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        } : null
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.dataset.label || '';
                                    const value = context.parsed.x;
                                    
                                    if (label.includes('Efficacité')) {
                                        return `${label}: ${value}%`;
                                    } else if (label.includes('Temps')) {
                                        return `${label}: ${value} min`;
                                    }
                                    return `${label}: ${value}`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Update any related UI elements with department performance data
            this.updateDepartmentInfoPanel(processedData);
        } else {
            console.error('Chart.js library not found. Please include Chart.js in your project.');
        }
    }
    
    /**
     * Process and sort the department data according to the options
     * 
     * @param {Object} data - The raw chart data
     * @returns {Array} Processed and sorted data
     */
    processData(data) {
        // Extract departments and their metrics
        let processedData = [];
        
        if (data && data.departments) {
            processedData = data.departments.map(dept => ({
                department: dept.name,
                total: dept.deliveries || 0,
                onTime: dept.onTimeDeliveries || 0,
                efficiency: dept.efficiency !== undefined ? 
                    parseFloat(dept.efficiency) : 
                    (dept.onTimeDeliveries && dept.deliveries ? 
                        Math.round((dept.onTimeDeliveries / dept.deliveries) * 100) : 0),
                avgTime: dept.averageDeliveryTime !== undefined ? 
                    parseFloat(dept.averageDeliveryTime) : 0
            }));
        }
        
        // Sort the data based on the sortBy option
        if (this.options.sortBy === 'total') {
            processedData.sort((a, b) => b.total - a.total);
        } else if (this.options.sortBy === 'efficiency') {
            processedData.sort((a, b) => b.efficiency - a.efficiency);
        } else if (this.options.sortBy === 'avgTime') {
            processedData.sort((a, b) => a.avgTime - b.avgTime);
        }
        
        // Limit the number of departments shown
        if (this.options.limit && processedData.length > this.options.limit) {
            processedData = processedData.slice(0, this.options.limit);
        }
        
        return processedData;
    }
    
    /**
     * Update the department information panel on the dashboard
     * 
     * @param {Array} data - The processed department data
     */
    updateDepartmentInfoPanel(data) {
        if (!data || data.length === 0) return;
        
        // Update best performing department
        const bestDeptElement = document.getElementById('best-department');
        if (bestDeptElement && data.length > 0) {
            // Sort by efficiency to find the best department
            const sortedByEfficiency = [...data].sort((a, b) => b.efficiency - a.efficiency);
            if (sortedByEfficiency.length > 0) {
                bestDeptElement.textContent = sortedByEfficiency[0].department;
                
                // Update efficiency value
                const efficiencyElement = document.getElementById('best-dept-efficiency');
                if (efficiencyElement) {
                    efficiencyElement.textContent = `${sortedByEfficiency[0].efficiency}%`;
                }
            }
        }
        
        // Update busiest department
        const busiestDeptElement = document.getElementById('busiest-department');
        if (busiestDeptElement && data.length > 0) {
            // Sort by total deliveries to find the busiest department
            const sortedByTotal = [...data].sort((a, b) => b.total - a.total);
            if (sortedByTotal.length > 0) {
                busiestDeptElement.textContent = sortedByTotal[0].department;
                
                // Update total deliveries value
                const totalElement = document.getElementById('busiest-dept-total');
                if (totalElement) {
                    totalElement.textContent = BaseChart.formatNumber(sortedByTotal[0].total);
                }
            }
        }
        
        // Update fastest department
        const fastestDeptElement = document.getElementById('fastest-department');
        if (fastestDeptElement && data.length > 0) {
            // Sort by average time to find the fastest department
            const sortedByTime = [...data].filter(d => d.avgTime > 0).sort((a, b) => a.avgTime - b.avgTime);
            if (sortedByTime.length > 0) {
                fastestDeptElement.textContent = sortedByTime[0].department;
                
                // Update average time value
                const timeElement = document.getElementById('fastest-dept-time');
                if (timeElement) {
                    timeElement.textContent = `${sortedByTime[0].avgTime} min`;
                }
            }
        }
    }
    
    /**
     * Generate fallback data for testing when no real data is available
     * 
     * @returns {Object} Fallback chart data
     */
    generateFallbackData() {
        const departments = [
            'Paris', 'Lyon', 'Marseille', 'Lille', 
            'Bordeaux', 'Toulouse', 'Nantes', 'Strasbourg', 
            'Nice', 'Rennes'
        ];
        
        const generateDeptData = () => {
            const total = Math.floor(Math.random() * 500) + 100;
            const onTime = Math.floor(Math.random() * total);
            const efficiency = Math.round((onTime / total) * 100);
            const avgTime = Math.floor(Math.random() * 60) + 15;
            
            return { total, onTime, efficiency, avgTime };
        };
        
        return {
            departments: departments.map(dept => ({
                name: dept,
                ...generateDeptData()
            })),
            realValues: false,
            isFallback: true,
            fallbackGenerated: new Date().toISOString()
        };
    }
}

// Export for module usage
export default DepartmentPerformanceChart; 