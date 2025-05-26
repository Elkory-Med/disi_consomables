import BaseChart from '../BaseChart.js';

/**
 * AdministrationChart - Displays administration statistics by department
 * 
 * This chart shows the distribution of various metrics across different
 * administrative departments.
 */
class AdministrationChart extends BaseChart {
    /**
     * Create a new AdministrationChart instance
     * 
     * @param {string} chartId - The DOM element ID for this chart
     * @param {Object} options - Configuration options
     */
    constructor(chartId, options = {}) {
        // Set default options specific to administration chart
        const defaultOptions = {
            chartType: 'bar',
            dataKey: 'administration_data',
            displayGridLines: true,
            legendPosition: 'top',
            unwantedLabels: [
                'Utilisateur Test', 
                'User Test', 
                'Demo User',
                'Admin Test'
            ],
            userPatterns: [
                /^test/i,
                /demo/i,
                /example/i,
                /sample/i
            ]
        };
        
        // Call parent constructor with merged options
        super(chartId, Object.assign({}, defaultOptions, options));
    }
    
    /**
     * Build the administration chart with the provided dashboard data
     * 
     * @param {Object} dashboardData - The complete dashboard data object
     */
    buildChart(dashboardData) {
        // Check if chart element exists
        if (!this.chartElement) {
            console.error(`Chart element with ID ${this.chartId} not found`);
            return;
        }
        
        // Get administration data from dashboard data
        const data = dashboardData[this.options.dataKey];
        
        // If no data is available, use fallback data
        if (!data || !data.labels || data.labels.length === 0) {
            console.warn('No administration data available, using fallback data');
            this.generateFallbackData();
            return;
        }
        
        // Store the data
        this.chartData = data;
        
        // Clean the data by removing unwanted labels
        const cleanedData = this.cleanAdministrationData(data);
        
        // Create chart configuration
        const config = this.createChartConfig(cleanedData);
        
        // If chart already exists, destroy it
        if (this.chart) {
            try {
                this.chart.destroy();
            } catch (e) {
                console.warn(`Error destroying existing chart: ${e.message}`);
            }
        }
        
        // Create new chart instance
        const ctx = this.chartElement.getContext('2d');
        try {
            this.chart = new Chart(ctx, config);
            
            // Force the chart to be responsive
            window.setTimeout(() => {
                if (this.chart) this.chart.resize();
            }, 100);
        } catch (e) {
            console.error(`Error creating administration chart: ${e.message}`);
            this.generateFallbackData();
        }
        
        // Update administration summary information
        this.updateAdministrationSummary(cleanedData);
    }
    
    /**
     * Clean administration data by removing unwanted labels
     * 
     * @param {Object} data - The raw administration data
     * @returns {Object} The cleaned administration data
     */
    cleanAdministrationData(data) {
        if (!data || !data.labels || !data.datasets) {
            return data;
        }
        
        // Find indices of unwanted labels
        const indicesToRemove = [];
        
        data.labels.forEach((label, index) => {
            // Check against unwanted label list
            const isUnwanted = this.options.unwantedLabels.includes(label);
            
            // Check against regex patterns
            const matchesPattern = this.options.userPatterns.some(pattern => 
                pattern.test(label)
            );
            
            if (isUnwanted || matchesPattern) {
                indicesToRemove.push(index);
            }
        });
        
        // If no unwanted labels found, return original data
        if (indicesToRemove.length === 0) {
            return data;
        }
        
        // Create new object with cleaned data
        const cleanedData = {
            labels: [],
            datasets: []
        };
        
        // Copy labels, excluding unwanted ones
        cleanedData.labels = data.labels.filter((_, index) => 
            !indicesToRemove.includes(index)
        );
        
        // Copy datasets, excluding data for unwanted labels
        data.datasets.forEach(dataset => {
            const cleanedDataset = {
                ...dataset,
                data: dataset.data.filter((_, index) => 
                    !indicesToRemove.includes(index)
                )
            };
            cleanedData.datasets.push(cleanedDataset);
        });
        
        return cleanedData;
    }
    
    /**
     * Create chart configuration for administration data
     * 
     * @param {Object} data - The cleaned administration data
     * @returns {Object} Chart.js configuration object
     */
    createChartConfig(data) {
        return {
            type: this.options.chartType,
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: this.options.displayLegend,
                    position: this.options.legendPosition,
                    labels: {
                        fontColor: '#858796',
                        usePointStyle: true
                    }
                },
                tooltips: {
                    enabled: this.options.tooltips,
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            const datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': ' + BaseChart.formatNumber(tooltipItem.yLabel);
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: this.options.displayGridLines,
                            drawBorder: false,
                            color: "rgba(0, 0, 0, 0.1)"
                        },
                        ticks: {
                            maxTicksLimit: 12,
                            fontColor: "#858796"
                        },
                        maxBarThickness: 25
                    }],
                    yAxes: [{
                        gridLines: {
                            display: true,
                            color: "rgba(0, 0, 0, 0.1)"
                        },
                        ticks: {
                            beginAtZero: true,
                            maxTicksLimit: 5,
                            fontColor: "#858796",
                            callback: function(value) {
                                return BaseChart.formatNumber(value);
                            }
                        }
                    }]
                }
            }
        };
    }
    
    /**
     * Update administration summary information on the dashboard
     * 
     * @param {Object} data - The cleaned administration data
     */
    updateAdministrationSummary(data) {
        if (!data || !data.datasets || !data.labels) return;
        
        try {
            // Calculate total for each department
            const departmentTotals = {};
            
            data.labels.forEach((department, index) => {
                departmentTotals[department] = 0;
                
                data.datasets.forEach(dataset => {
                    departmentTotals[department] += dataset.data[index] || 0;
                });
            });
            
            // Find department with highest total
            let topDepartment = '';
            let topTotal = 0;
            
            Object.entries(departmentTotals).forEach(([dept, total]) => {
                if (total > topTotal) {
                    topTotal = total;
                    topDepartment = dept;
                }
            });
            
            // Update DOM elements with summary information
            const topDeptElement = document.getElementById('top-department');
            const topTotalElement = document.getElementById('top-department-total');
            const totalDepartmentsElement = document.getElementById('total-departments');
            
            if (topDeptElement) {
                topDeptElement.textContent = topDepartment || 'N/A';
            }
            
            if (topTotalElement) {
                topTotalElement.textContent = topTotal ? BaseChart.formatNumber(topTotal) : '0';
            }
            
            if (totalDepartmentsElement) {
                totalDepartmentsElement.textContent = data.labels.length || '0';
            }
        } catch (e) {
            console.error('Error updating administration summary:', e);
        }
    }
    
    /**
     * Generate fallback data when real data is unavailable
     */
    generateFallbackData() {
        // Sample departments
        const labels = [
            'Direction', 
            'Ressources Humaines', 
            'Comptabilité', 
            'Marketing', 
            'Informatique',
            'Service Client'
        ];
        
        // Create sample datasets
        const datasets = [
            {
                label: 'Livraisons',
                backgroundColor: this.options.colors[0],
                hoverBackgroundColor: this.options.colors[0],
                data: BaseChart.generateRandomData(labels.length, 50, 200)
            },
            {
                label: 'Documents',
                backgroundColor: this.options.colors[1],
                hoverBackgroundColor: this.options.colors[1],
                data: BaseChart.generateRandomData(labels.length, 20, 150)
            }
        ];
        
        // Create fallback data object
        const fallbackData = {
            labels: labels,
            datasets: datasets
        };
        
        // Log fallback usage
        console.log('Using fallback administration data', fallbackData);
        
        // Update chart with fallback data
        const config = this.createChartConfig(fallbackData);
        
        // If chart already exists, destroy it
        if (this.chart) {
            try {
                this.chart.destroy();
            } catch (e) {
                console.warn(`Error destroying existing chart: ${e.message}`);
            }
        }
        
        // Create new chart instance with fallback data
        const ctx = this.chartElement.getContext('2d');
        try {
            this.chart = new Chart(ctx, config);
        } catch (e) {
            console.error(`Error creating fallback administration chart: ${e.message}`);
        }
        
        // Update administration summary with fallback data
        this.updateAdministrationSummary(fallbackData);
    }
}

// Export for module usage
export default AdministrationChart; 