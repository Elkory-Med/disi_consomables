/**
 * BarChart - Bar chart implementation for dashboard
 * 
 * This class extends the BaseChart class to provide specific
 * implementation for vertical and horizontal bar charts.
 */
class BarChart extends BaseChart {
    /**
     * Create a new bar chart instance
     * 
     * @param {string} elementId - The ID of the DOM element to render the chart in
     * @param {Object} options - Configuration options for the chart
     */
    constructor(elementId, options = {}) {
        // Set default options for bar charts
        const barOptions = Object.assign({
            type: 'bar',
            horizontal: false,
            stacked: false,
            showGrid: true,
            barPercentage: 0.8,
            categoryPercentage: 0.9,
            barThickness: null, // Auto
            borderRadius: 4,
            borderSkipped: false,
            showValues: false, // Show values on top of bars
            showLabels: true   // Show x-axis labels
        }, options);
        
        // If horizontal bar, change type to 'horizontalBar'
        if (barOptions.horizontal) {
            barOptions.type = 'bar'; // In Chart.js v3+, 'horizontalBar' is replaced by indexAxis: 'y'
            barOptions.indexAxis = 'y'; 
        }
        
        // Call parent constructor
        super(elementId, barOptions);
    }
    
    /**
     * Build bar chart with the provided data
     * 
     * @param {Object} data - Data for the chart containing labels and datasets
     */
    buildChart(data) {
        if (!this.canvas) {
            console.error('Cannot build chart: Canvas element not found');
            return;
        }
        
        if (!data || !data.labels || !data.datasets) {
            console.error('Invalid data format for bar chart');
            return;
        }
        
        // Prepare datasets with proper configuration
        const datasets = data.datasets.map((dataset, index) => {
            // Get color from options or generate one
            const color = dataset.color || dataset.backgroundColor || 
                this.colors[index % this.colors.length] || 
                this.generateColors(this.options.colors[0], 1)[0];
                
            // Create hover color (slightly darker)
            const hoverColor = this.adjustBrightness(color, -15);
            
            return {
                label: dataset.label || `Dataset ${index + 1}`,
                data: dataset.data,
                backgroundColor: color,
                hoverBackgroundColor: hoverColor,
                borderColor: dataset.borderColor || 'rgba(0,0,0,0.1)',
                borderWidth: dataset.borderWidth || 1,
                borderRadius: dataset.borderRadius !== undefined ? 
                    dataset.borderRadius : this.options.borderRadius,
                borderSkipped: dataset.borderSkipped !== undefined ? 
                    dataset.borderSkipped : this.options.borderSkipped,
                barPercentage: dataset.barPercentage || this.options.barPercentage,
                categoryPercentage: dataset.categoryPercentage || this.options.categoryPercentage,
                barThickness: dataset.barThickness || this.options.barThickness,
                order: dataset.order || index
            };
        });
        
        // Configure chart options
        const chartOptions = {
            type: this.options.type,
            data: {
                labels: this.options.showLabels ? data.labels : [],
                datasets: datasets
            },
            options: {
                indexAxis: this.options.indexAxis || 'x',
                responsive: this.options.responsive,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                animation: {
                    duration: this.options.animationDuration
                },
                scales: {
                    x: {
                        stacked: this.options.stacked,
                        grid: {
                            display: this.options.showGrid && !this.options.horizontal,
                            color: this.options.darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            display: this.options.showLabels,
                            color: this.options.darkMode ? '#ccc' : '#666'
                        }
                    },
                    y: {
                        stacked: this.options.stacked,
                        grid: {
                            display: this.options.showGrid && this.options.horizontal,
                            color: this.options.darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            color: this.options.darkMode ? '#ccc' : '#666',
                            callback: function(value) {
                                return BaseChart.formatNumber(value);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: this.options.showLegend,
                        position: 'top',
                        labels: {
                            color: this.options.darkMode ? '#e0e0e0' : '#666',
                            padding: 15
                        }
                    },
                    title: {
                        display: !!this.options.title,
                        text: this.options.title,
                        color: this.options.darkMode ? '#fff' : '#333',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: this.options.darkMode ? '#333' : 'rgba(255, 255, 255, 0.8)',
                        titleColor: this.options.darkMode ? '#fff' : '#333',
                        bodyColor: this.options.darkMode ? '#ccc' : '#666',
                        borderColor: this.options.darkMode ? '#555' : '#ccc',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += BaseChart.formatNumber(context.parsed[this.options.horizontal ? 'x' : 'y']);
                                return label;
                            }
                        }
                    }
                }
            }
        };
        
        // Add value display on top of bars if enabled
        if (this.options.showValues) {
            chartOptions.options.plugins.datalabels = {
                display: true,
                color: this.options.darkMode ? '#fff' : '#333',
                anchor: this.options.horizontal ? 'end' : 'end',
                align: this.options.horizontal ? 'right' : 'top',
                formatter: function(value) {
                    return BaseChart.formatNumber(value);
                },
                font: {
                    weight: 'bold'
                },
                padding: {
                    top: 4,
                    bottom: 4
                }
            };
        }
        
        // Create chart
        this.chart = new Chart(this.ctx, chartOptions);
        
        // Dispatch event indicating the chart was built
        const event = new CustomEvent('chart:built', {
            detail: {
                chartId: this.elementId,
                instance: this,
                type: this.options.type
            }
        });
        document.dispatchEvent(event);
    }
    
    /**
     * Convert this chart to a horizontal bar chart
     */
    toHorizontal() {
        if (!this.chart) return this;
        
        this.options.horizontal = true;
        this.options.indexAxis = 'y';
        
        if (this.chart.options) {
            this.chart.options.indexAxis = 'y';
            
            // Swap grid display settings
            const xGridDisplay = this.chart.options.scales.x.grid.display;
            this.chart.options.scales.x.grid.display = this.chart.options.scales.y.grid.display;
            this.chart.options.scales.y.grid.display = xGridDisplay;
            
            this.chart.update();
        }
        
        return this;
    }
    
    /**
     * Convert this chart to a vertical bar chart
     */
    toVertical() {
        if (!this.chart) return this;
        
        this.options.horizontal = false;
        this.options.indexAxis = 'x';
        
        if (this.chart.options) {
            this.chart.options.indexAxis = 'x';
            
            // Swap grid display settings
            const xGridDisplay = this.chart.options.scales.x.grid.display;
            this.chart.options.scales.x.grid.display = this.chart.options.scales.y.grid.display;
            this.chart.options.scales.y.grid.display = xGridDisplay;
            
            this.chart.update();
        }
        
        return this;
    }
    
    /**
     * Enable or disable stacking for this chart
     * 
     * @param {boolean} stacked - Whether to stack the bars
     */
    setStacked(stacked = true) {
        if (!this.chart) return this;
        
        this.options.stacked = stacked;
        
        if (this.chart.scales) {
            this.chart.options.scales.x.stacked = stacked;
            this.chart.options.scales.y.stacked = stacked;
            this.chart.update();
        }
        
        return this;
    }
    
    /**
     * Show or hide values on top of the bars
     * 
     * @param {boolean} show - Whether to show the values
     */
    showValues(show = true) {
        if (!this.chart) return this;
        
        this.options.showValues = show;
        
        // Need to recreate the chart to apply datalabels
        const data = this.chart.data;
        this.chart.destroy();
        this.buildChart(data);
        
        return this;
    }
    
    /**
     * Set the bar thickness
     * 
     * @param {number} thickness - The thickness in pixels, or null for auto
     */
    setBarThickness(thickness) {
        if (!this.chart) return this;
        
        this.options.barThickness = thickness;
        
        this.chart.data.datasets.forEach((dataset) => {
            dataset.barThickness = thickness;
        });
        
        this.chart.update();
        return this;
    }
    
    /**
     * Set the corner radius of bars
     * 
     * @param {number} radius - The radius in pixels
     */
    setCornerRadius(radius) {
        if (!this.chart) return this;
        
        this.options.borderRadius = radius;
        
        this.chart.data.datasets.forEach((dataset) => {
            dataset.borderRadius = radius;
        });
        
        this.chart.update();
        return this;
    }
}

// Export for module bundlers
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BarChart;
} 