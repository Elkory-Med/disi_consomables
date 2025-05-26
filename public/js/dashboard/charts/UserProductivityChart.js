import BaseChart from '../BaseChart.js';

/**
 * UserProductivityChart - Chart for displaying user productivity metrics
 * 
 * This chart shows productivity metrics for individual users,
 * displaying metrics like total deliveries, performance score, etc.
 */
class UserProductivityChart extends BaseChart {
    /**
     * Create a new user productivity chart instance
     * 
     * @param {string} chartId - The ID of the chart element in the DOM
     * @param {Object} options - Configuration options for the chart
     */
    constructor(chartId, options = {}) {
        // Set default options specific to this chart
        const defaultOptions = {
            chartType: 'bar',
            colors: [
                '#4e73df', // Primary blue
                '#1cc88a', // Success green
                '#36b9cc', // Info cyan
                '#f6c23e'  // Warning yellow
            ],
            dataKey: 'userStats',
            sortBy: 'deliveries', // 'deliveries', 'score', 'avgTime'
            limit: 10, // Limit to top X users
            stackedBars: false
        };
        
        // Merge with base options and any user-provided options
        super(chartId, Object.assign({}, defaultOptions, options));
    }
    
    /**
     * Build the user productivity chart with the provided data
     * 
     * @param {Object} dashboardData - The full dashboard data from the API
     */
    buildChart(dashboardData) {
        // Get user data from the response
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
                console.warn(`Error destroying user productivity chart: ${e.message}`);
            }
        }
        
        // Create the chart using Chart.js
        if (typeof Chart !== 'undefined') {
            const ctx = this.chartElement.getContext('2d');
            
            // Create chart configuration
            const chartConfig = this.createChartConfig(processedData);
            
            // Create the chart
            this.chart = new Chart(ctx, chartConfig);
            
            // Update any related UI elements with user productivity data
            this.updateUserProductivityInfo(processedData);
        } else {
            console.error('Chart.js library not found. Please include Chart.js in your project.');
        }
    }
    
    /**
     * Create chart configuration based on processed data
     * 
     * @param {Array} processedData - The processed user data
     * @returns {Object} Chart.js configuration object
     */
    createChartConfig(processedData) {
        // Prepare the datasets based on available metrics
        const datasets = [];
        
        // Total deliveries dataset
        if (processedData[0].hasOwnProperty('deliveries')) {
            datasets.push({
                label: 'Livraisons',
                data: processedData.map(item => item.deliveries),
                backgroundColor: this.options.colors[0],
                borderColor: this.options.stackedBars ? 'rgba(255, 255, 255, 0.8)' : this.options.colors[0],
                borderWidth: 1,
                stack: this.options.stackedBars ? 'Stack 0' : undefined
            });
        }
        
        // Performance score dataset
        if (processedData[0].hasOwnProperty('score')) {
            datasets.push({
                label: 'Score',
                data: processedData.map(item => item.score),
                backgroundColor: this.options.colors[1],
                borderColor: this.options.stackedBars ? 'rgba(255, 255, 255, 0.8)' : this.options.colors[1],
                borderWidth: 1,
                stack: this.options.stackedBars ? 'Stack 1' : undefined,
                // Use a second y-axis for score
                yAxisID: 'y1'
            });
        }
        
        // Average time dataset
        if (processedData[0].hasOwnProperty('avgTime')) {
            datasets.push({
                label: 'Temps moyen (min)',
                data: processedData.map(item => item.avgTime),
                backgroundColor: this.options.colors[2],
                borderColor: this.options.stackedBars ? 'rgba(255, 255, 255, 0.8)' : this.options.colors[2],
                borderWidth: 1,
                stack: this.options.stackedBars ? 'Stack 2' : undefined,
                // Use a third y-axis for time
                yAxisID: 'y2'
            });
        }
        
        // On-time percentage dataset
        if (processedData[0].hasOwnProperty('onTimePercentage')) {
            datasets.push({
                label: 'À temps (%)',
                data: processedData.map(item => item.onTimePercentage),
                backgroundColor: this.options.colors[3],
                borderColor: this.options.stackedBars ? 'rgba(255, 255, 255, 0.8)' : this.options.colors[3],
                borderWidth: 1,
                stack: this.options.stackedBars ? 'Stack 3' : undefined,
                // Use a fourth y-axis for percentages
                yAxisID: 'y3'
            });
        }
        
        // Configure scales based on datasets
        const scales = {
            x: {
                title: {
                    display: true,
                    text: 'Utilisateurs'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Livraisons'
                }
            }
        };
        
        // Add additional scales if needed
        if (processedData[0].hasOwnProperty('score')) {
            scales.y1 = {
                type: 'linear',
                position: 'right',
                beginAtZero: true,
                max: 100,
                title: {
                    display: true,
                    text: 'Score'
                },
                grid: {
                    drawOnChartArea: false
                }
            };
        }
        
        if (processedData[0].hasOwnProperty('avgTime')) {
            scales.y2 = {
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
            };
        }
        
        if (processedData[0].hasOwnProperty('onTimePercentage')) {
            scales.y3 = {
                type: 'linear',
                position: 'right',
                beginAtZero: true,
                max: 100,
                title: {
                    display: true,
                    text: 'À temps (%)'
                },
                grid: {
                    drawOnChartArea: false
                }
            };
        }
        
        return {
            type: this.options.chartType,
            data: {
                labels: processedData.map(item => item.name),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: scales,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y;
                                
                                if (label.includes('%')) {
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
        };
    }
    
    /**
     * Process and sort the user data according to the options
     * 
     * @param {Object} data - The raw chart data
     * @returns {Array} Processed and sorted data
     */
    processData(data) {
        // Extract users and their metrics
        let processedData = [];
        
        if (data && data.users) {
            processedData = data.users.map(user => ({
                id: user.id || 0,
                name: user.name || 'Anonymous',
                deliveries: user.deliveries || 0,
                score: user.performanceScore !== undefined ? 
                    parseFloat(user.performanceScore) : 0,
                avgTime: user.averageDeliveryTime !== undefined ? 
                    parseFloat(user.averageDeliveryTime) : 0,
                onTimeDeliveries: user.onTimeDeliveries || 0,
                onTimePercentage: user.onTimePercentage !== undefined ? 
                    parseFloat(user.onTimePercentage) : 
                    (user.onTimeDeliveries && user.deliveries ? 
                        Math.round((user.onTimeDeliveries / user.deliveries) * 100) : 0)
            }));
        }
        
        // Sort the data based on the sortBy option
        if (this.options.sortBy === 'deliveries') {
            processedData.sort((a, b) => b.deliveries - a.deliveries);
        } else if (this.options.sortBy === 'score') {
            processedData.sort((a, b) => b.score - a.score);
        } else if (this.options.sortBy === 'avgTime') {
            processedData.sort((a, b) => a.avgTime - b.avgTime);
        } else if (this.options.sortBy === 'onTimePercentage') {
            processedData.sort((a, b) => b.onTimePercentage - a.onTimePercentage);
        }
        
        // Limit the number of users shown
        if (this.options.limit && processedData.length > this.options.limit) {
            processedData = processedData.slice(0, this.options.limit);
        }
        
        return processedData;
    }
    
    /**
     * Update the user productivity information on the dashboard
     * 
     * @param {Array} data - The processed user data
     */
    updateUserProductivityInfo(data) {
        if (!data || data.length === 0) return;
        
        // Update top performer info
        const topPerformerElement = document.getElementById('top-performer');
        if (topPerformerElement) {
            // Sort by score to find the top performer
            const sortedByScore = [...data].sort((a, b) => b.score - a.score);
            if (sortedByScore.length > 0) {
                topPerformerElement.textContent = sortedByScore[0].name;
                
                // Update score value
                const scoreElement = document.getElementById('top-performer-score');
                if (scoreElement) {
                    scoreElement.textContent = `${sortedByScore[0].score}`;
                }
            }
        }
        
        // Update most deliveries info
        const mostDeliveriesElement = document.getElementById('most-deliveries-user');
        if (mostDeliveriesElement) {
            // Sort by deliveries to find the user with most deliveries
            const sortedByDeliveries = [...data].sort((a, b) => b.deliveries - a.deliveries);
            if (sortedByDeliveries.length > 0) {
                mostDeliveriesElement.textContent = sortedByDeliveries[0].name;
                
                // Update deliveries count
                const countElement = document.getElementById('most-deliveries-count');
                if (countElement) {
                    countElement.textContent = BaseChart.formatNumber(sortedByDeliveries[0].deliveries);
                }
            }
        }
        
        // Update fastest user info
        const fastestUserElement = document.getElementById('fastest-user');
        if (fastestUserElement) {
            // Sort by average time to find the fastest user
            const sortedByTime = [...data].filter(u => u.avgTime > 0).sort((a, b) => a.avgTime - b.avgTime);
            if (sortedByTime.length > 0) {
                fastestUserElement.textContent = sortedByTime[0].name;
                
                // Update average time value
                const timeElement = document.getElementById('fastest-user-time');
                if (timeElement) {
                    timeElement.textContent = `${sortedByTime[0].avgTime} min`;
                }
            }
        }
        
        // Update total users stats
        const totalUsersElement = document.getElementById('total-active-users');
        if (totalUsersElement) {
            totalUsersElement.textContent = data.length;
        }
    }
    
    /**
     * Generate fallback data for testing when no real data is available
     * 
     * @returns {Object} Fallback chart data
     */
    generateFallbackData() {
        const userNames = [
            'Sophie Martin', 'Thomas Bernard', 'Camille Dubois', 
            'Lucas Petit', 'Emma Lefebvre', 'Hugo Moreau', 
            'Léa Robert', 'Jules Roux', 'Manon Simon', 
            'Louis Michel', 'Chloé Leroy', 'Nathan Lambert'
        ];
        
        const generateUserData = () => {
            const deliveries = Math.floor(Math.random() * 300) + 50;
            const onTimeDeliveries = Math.floor(Math.random() * deliveries);
            const onTimePercentage = Math.round((onTimeDeliveries / deliveries) * 100);
            const performanceScore = Math.min(100, Math.floor(Math.random() * 40) + 60);
            const avgTime = Math.floor(Math.random() * 45) + 10;
            
            return { 
                deliveries, 
                onTimeDeliveries, 
                onTimePercentage,
                performanceScore, 
                avgTime 
            };
        };
        
        return {
            users: userNames.map((name, index) => ({
                id: index + 1,
                name: name,
                ...generateUserData()
            })),
            realValues: false,
            isFallback: true,
            fallbackGenerated: new Date().toISOString()
        };
    }
}

// Export for module usage
export default UserProductivityChart; 