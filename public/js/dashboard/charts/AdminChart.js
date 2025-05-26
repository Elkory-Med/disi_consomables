/**
 * AdminChart - Administration data visualization chart
 * 
 * This class extends BaseChart to provide specific functionality for displaying
 * administration data, including user distribution and activity metrics.
 */
import BaseChart from '../BaseChart.js';

class AdminChart extends BaseChart {
    /**
     * Create a new AdminChart instance
     * 
     * @param {string} elementId - The ID of the DOM element to render the chart in
     * @param {Object} options - Configuration options for the chart
     */
    constructor(elementId, options = {}) {
        // Default options specific to AdminChart
        const defaultOptions = {
            type: 'bar',
            title: 'Administration Activity',
            colors: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
            stacked: true,
            showLegend: true,
            // List of unwanted labels to filter out (e.g. test users or patterns)
            unwantedLabels: [
                'Test User',
                'Admin',
                'Demo Account'
            ],
            // User patterns to filter out (e.g. test or temporary accounts)
            userPatterns: [
                /^test/i,
                /demo\d+/i,
                /temp_.*/i
            ]
        };
        
        // Call parent constructor with merged options
        super(elementId, Object.assign({}, defaultOptions, options));
        
        // Bind methods
        this.cleanChartData = this.cleanChartData.bind(this);
        this.generateFallbackData = this.generateFallbackData.bind(this);
    }
    
    /**
     * Build the chart with the provided data
     * 
     * @param {Object} data - The data to build the chart with
     * @override
     */
    buildChart(data) {
        // Check if admin data exists
        if (!data || !data.admin || !data.admin.users) {
            console.warn('No administration data available, using fallback data');
            this.generateFallbackData();
            return;
        }
        
        // Clean the data based on filter settings
        const cleanedData = this.cleanChartData(data.admin);
        
        // If no data after cleaning, use fallback
        if (!cleanedData.labels || cleanedData.labels.length === 0) {
            console.warn('No valid data after cleaning, using fallback data');
            this.generateFallbackData();
            return;
        }
        
        // Chart configuration
        const config = {
            type: this.options.type,
            data: {
                labels: cleanedData.labels,
                datasets: cleanedData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: Boolean(this.options.title),
                        text: this.options.title,
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: this.options.showLegend,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                
                                // Format number based on type
                                if (context.parsed.y !== null) {
                                    if (context.dataset.yAxisID === 'percentage') {
                                        label += BaseChart.formatPercentage(context.parsed.y);
                                    } else if (context.dataset.yAxisID === 'currency') {
                                        label += BaseChart.formatCurrency(context.parsed.y);
                                    } else {
                                        label += BaseChart.formatNumber(context.parsed.y);
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: this.options.stacked,
                        ticks: {
                            color: this.options.darkMode ? '#e0e0e0' : '#666'
                        },
                        grid: {
                            color: this.options.darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y: {
                        stacked: this.options.stacked,
                        ticks: {
                            color: this.options.darkMode ? '#e0e0e0' : '#666',
                            beginAtZero: true
                        },
                        grid: {
                            color: this.options.darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        };
        
        // Create or update chart
        if (this.chart) {
            this.chart.data = config.data;
            this.chart.options = config.options;
            this.chart.update();
        } else {
            this.chart = new Chart(this.ctx, config);
        }
    }
    
    /**
     * Clean the chart data based on filter settings
     * 
     * @param {Object} adminData - The administration data to clean
     * @returns {Object} Cleaned data suitable for charting
     */
    cleanChartData(adminData) {
        if (!adminData || !adminData.users || !adminData.departments) {
            return { labels: [], datasets: [] };
        }
        
        // Filter out unwanted labels and patterns
        let filteredLabels = Object.keys(adminData.users).filter(label => {
            // Skip if in unwanted labels list
            if (this.options.unwantedLabels.includes(label)) {
                return false;
            }
            
            // Skip if matches any pattern
            for (const pattern of this.options.userPatterns) {
                if (pattern.test(label)) {
                    return false;
                }
            }
            
            return true;
        });
        
        // Sort labels alphabetically
        filteredLabels.sort();
        
        // Prepare datasets
        const datasets = [];
        
        // Add user activity dataset
        if (adminData.user_activity) {
            datasets.push({
                label: 'User Activity',
                data: filteredLabels.map(label => adminData.user_activity[label] || 0),
                backgroundColor: this.options.colors[0],
                borderColor: this.options.darkMode ? '#fff' : '#000',
                borderWidth: 1
            });
        }
        
        // Add department datasets
        if (adminData.departments) {
            Object.entries(adminData.departments).forEach(([department, values], index) => {
                datasets.push({
                    label: department,
                    data: filteredLabels.map(label => values[label] || 0),
                    backgroundColor: this.options.colors[(index + 1) % this.options.colors.length],
                    borderColor: this.options.darkMode ? '#fff' : '#000',
                    borderWidth: 1
                });
            });
        }
        
        return {
            labels: filteredLabels,
            datasets: datasets
        };
    }
    
    /**
     * Generate fallback data if no real data is available
     */
    generateFallbackData() {
        console.info('Generating fallback administration data');
        
        // Generate sample labels
        const labels = ['HR', 'Finance', 'IT', 'Marketing', 'Sales'];
        
        // Generate sample datasets
        const datasets = [
            {
                label: 'User Activity',
                data: labels.map(() => Math.floor(Math.random() * 100)),
                backgroundColor: this.options.colors[0],
                borderColor: this.options.darkMode ? '#fff' : '#000',
                borderWidth: 1
            },
            {
                label: 'North Region',
                data: labels.map(() => Math.floor(Math.random() * 50)),
                backgroundColor: this.options.colors[1],
                borderColor: this.options.darkMode ? '#fff' : '#000',
                borderWidth: 1
            },
            {
                label: 'South Region',
                data: labels.map(() => Math.floor(Math.random() * 70)),
                backgroundColor: this.options.colors[2],
                borderColor: this.options.darkMode ? '#fff' : '#000',
                borderWidth: 1
            }
        ];
        
        // Chart configuration
        const config = {
            type: this.options.type,
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: Boolean(this.options.title),
                        text: this.options.title + ' (Sample Data)',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: this.options.showLegend,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        stacked: this.options.stacked,
                        ticks: {
                            color: this.options.darkMode ? '#e0e0e0' : '#666'
                        },
                        grid: {
                            color: this.options.darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y: {
                        stacked: this.options.stacked,
                        ticks: {
                            color: this.options.darkMode ? '#e0e0e0' : '#666',
                            beginAtZero: true
                        },
                        grid: {
                            color: this.options.darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        };
        
        // Create or update chart
        if (this.chart) {
            this.chart.data = config.data;
            this.chart.options = config.options;
            this.chart.update();
        } else {
            this.chart = new Chart(this.ctx, config);
        }
    }
}

// Export for module usage
export default AdminChart; 