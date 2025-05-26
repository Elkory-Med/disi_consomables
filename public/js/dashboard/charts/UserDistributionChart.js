/**
 * UserDistributionChart - Shows user distribution by various metrics
 * 
 * This chart displays user distribution data in pie chart format,
 * allowing for visualization of user segments by role, status, etc.
 */
import BaseChart from '../BaseChart.js';

class UserDistributionChart extends BaseChart {
    /**
     * Create a new UserDistributionChart instance
     * 
     * @param {string} chartId - The DOM element ID for this chart
     * @param {Object} options - Configuration options
     */
    constructor(chartId, options = {}) {
        // Set default options specific to user distribution chart
        const defaultOptions = {
            chartType: 'pie',
            dataKey: 'user_distribution',
            displayLegend: true,
            legendPosition: 'right',
            cutoutPercentage: 0
        };
        
        // Call parent constructor with merged options
        super(chartId, Object.assign({}, defaultOptions, options));
    }
    
    /**
     * Build the user distribution chart with the provided dashboard data
     * 
     * @param {Object} dashboardData - The complete dashboard data object
     */
    buildChart(dashboardData) {
        // Check if chart element exists
        if (!this.chartElement) {
            console.error(`Chart element with ID ${this.chartId} not found`);
            return;
        }
        
        // Get user distribution data from dashboard data
        const data = dashboardData[this.options.dataKey];
        
        // If no data is available, use fallback data
        if (!data || !data.labels || data.labels.length === 0) {
            console.warn('No user distribution data available, using fallback data');
            this.generateFallbackData();
            return;
        }
        
        // Store the data
        this.chartData = data;
        
        // Create chart configuration
        const config = this.createChartConfig(data);
        
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
            console.error(`Error creating user distribution chart: ${e.message}`);
            this.generateFallbackData();
        }
        
        // Update user distribution summary information
        this.updateUserSummary(data);
    }
    
    /**
     * Create chart configuration for user distribution data
     * 
     * @param {Object} data - The user distribution data
     * @returns {Object} Chart.js configuration object
     */
    createChartConfig(data) {
        // Create color array for segments based on our base colors
        const backgroundColors = [];
        const hoverBackgroundColors = [];
        
        for (let i = 0; i < data.labels.length; i++) {
            // Use colors from options, or generate colors if we run out
            const colorIndex = i % this.options.colors.length;
            const baseColor = this.options.colors[colorIndex];
            
            // Add variation to the color to make segments distinct
            const colorVariation = i >= this.options.colors.length ? 
                BaseChart.adjustColor(baseColor, i - this.options.colors.length) : 
                baseColor;
            
            backgroundColors.push(colorVariation);
            hoverBackgroundColors.push(BaseChart.adjustColor(colorVariation, 0, -10)); // Darker on hover
        }
        
        return {
            type: this.options.chartType,
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.datasets[0].data,
                    backgroundColor: backgroundColors,
                    hoverBackgroundColor: hoverBackgroundColors,
                    hoverBorderColor: "rgba(234, 236, 244, 1)"
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: this.options.cutoutPercentage,
                legend: {
                    display: this.options.displayLegend,
                    position: this.options.legendPosition,
                    labels: {
                        fontColor: '#858796',
                        usePointStyle: true
                    }
                },
                tooltips: {
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
                        label: (tooltipItem, chart) => {
                            const datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            const value = chart.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            const label = chart.labels[tooltipItem.index];
                            const percentage = this.calculatePercentage(value, chart.datasets[tooltipItem.datasetIndex].data);
                            return `${label}: ${BaseChart.formatNumber(value)} (${percentage}%)`;
                        }
                    }
                }
            }
        };
    }
    
    /**
     * Calculate percentage for a value compared to total
     * 
     * @param {number} value - The value to calculate percentage for
     * @param {Array} data - Array of all values
     * @returns {number} Percentage value (1-100)
     */
    calculatePercentage(value, data) {
        const total = data.reduce((sum, val) => sum + val, 0);
        if (total === 0) return 0;
        return Math.round((value / total) * 100);
    }
    
    /**
     * Update user summary information on the dashboard
     * 
     * @param {Object} data - The user distribution data
     */
    updateUserSummary(data) {
        if (!data || !data.datasets || !data.labels) return;
        
        try {
            // Calculate total users
            const userValues = data.datasets[0].data;
            const totalUsers = userValues.reduce((sum, val) => sum + val, 0);
            
            // Find dominant category
            let dominantCategory = '';
            let highestValue = 0;
            
            data.labels.forEach((label, index) => {
                const value = userValues[index];
                if (value > highestValue) {
                    highestValue = value;
                    dominantCategory = label;
                }
            });
            
            // Calculate percentage for dominant category
            const dominantPercentage = this.calculatePercentage(highestValue, userValues);
            
            // Update DOM elements
            const totalUsersElement = document.getElementById('total-users');
            const dominantCategoryElement = document.getElementById('dominant-category');
            const dominantPercentageElement = document.getElementById('dominant-percentage');
            
            if (totalUsersElement) {
                totalUsersElement.textContent = BaseChart.formatNumber(totalUsers);
            }
            
            if (dominantCategoryElement) {
                dominantCategoryElement.textContent = dominantCategory;
            }
            
            if (dominantPercentageElement) {
                dominantPercentageElement.textContent = `${dominantPercentage}%`;
            }
        } catch (e) {
            console.error('Error updating user summary:', e);
        }
    }
    
    /**
     * Generate fallback data when real data is unavailable
     */
    generateFallbackData() {
        // Sample user roles
        const labels = [
            'Administrateurs', 
            'Gestionnaires', 
            'Utilisateurs', 
            'Visiteurs'
        ];
        
        // Create sample data with realistic proportions
        const data = [
            5,    // Admins (small number)
            15,   // Managers
            65,   // Regular users (majority)
            15    // Visitors
        ];
        
        // Create fallback data object
        const fallbackData = {
            labels: labels,
            datasets: [{
                data: data,
                label: 'Utilisateurs'
            }]
        };
        
        // Log fallback usage
        console.log('Using fallback user distribution data', fallbackData);
        
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
            console.error(`Error creating fallback user distribution chart: ${e.message}`);
        }
        
        // Update user summary with fallback data
        this.updateUserSummary(fallbackData);
    }
    
    /**
     * Static method to create a doughnut chart variant
     * 
     * @param {string} chartId - The DOM element ID for this chart 
     * @param {Object} options - Configuration options
     * @returns {UserDistributionChart} New doughnut chart instance
     */
    static createDoughnut(chartId, options = {}) {
        return new UserDistributionChart(chartId, {
            ...options,
            chartType: 'doughnut',
            cutoutPercentage: 70
        });
    }
}

// Export for module usage
export default UserDistributionChart; 