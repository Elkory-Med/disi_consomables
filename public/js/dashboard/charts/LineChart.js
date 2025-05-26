/**
 * LineChart - Line/Area chart implementation for dashboard
 * 
 * This class extends the BaseChart class to provide specific 
 * implementation for line and area charts.
 */
class LineChart extends BaseChart {
    /**
     * Create a new line/area chart instance
     * 
     * @param {string} elementId - The ID of the DOM element to render the chart in
     * @param {Object} options - Configuration options for the chart
     */
    constructor(elementId, options = {}) {
        // Set default options for line charts
        const lineOptions = Object.assign({
            type: 'line',
            tension: 0.4, // Curve smoothness (0 = straight lines)
            fill: false, // True for area charts
            borderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 6,
            showGrid: true,
            stacked: false,
            xAxisType: 'category', // 'category', 'time', 'linear'
            yAxisType: 'linear',
            timeUnit: 'day' // 'day', 'week', 'month', 'year'
        }, options);
        
        // Call parent constructor
        super(elementId, lineOptions);
    }
    
    /**
     * Build line/area chart with the provided data
     * 
     * @param {Object} data - Data for the chart containing labels and datasets
     */
    buildChart(data) {
        if (!this.canvas) {
            console.error('Cannot build chart: Canvas element not found');
            return;
        }
        
        if (!data || !data.labels || !data.datasets) {
            console.error('Invalid data format for line chart');
            return;
        }
        
        // Prepare datasets with proper configuration
        const datasets = data.datasets.map((dataset, index) => {
            // Get color from options or generate one
            const color = dataset.color || dataset.borderColor || 
                this.colors[index % this.colors.length] || 
                this.generateColors(this.options.colors[0], 1)[0];
            
            // Prepare fill color for area charts
            const fillColor = dataset.fillColor || dataset.backgroundColor || 
                this.hexToRgba(color, 0.2);
            
            return {
                label: dataset.label || `Dataset ${index + 1}`,
                data: dataset.data,
                borderColor: color,
                backgroundColor: this.options.fill ? fillColor : color,
                fill: this.options.fill,
                tension: dataset.tension !== undefined ? dataset.tension : this.options.tension,
                borderWidth: dataset.borderWidth || this.options.borderWidth,
                pointRadius: dataset.pointRadius || this.options.pointRadius,
                pointHoverRadius: dataset.pointHoverRadius || this.options.pointHoverRadius,
                pointBackgroundColor: dataset.pointBackgroundColor || color,
                pointBorderColor: dataset.pointBorderColor || '#fff',
                pointBorderWidth: dataset.pointBorderWidth || 2,
                order: dataset.order || index
            };
        });
        
        // Configure axes based on chart type
        const scales = {
            x: {
                type: this.options.xAxisType,
                grid: {
                    display: this.options.showGrid,
                    color: this.options.darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                },
                time: this.options.xAxisType === 'time' ? {
                    unit: this.options.timeUnit
                } : undefined,
                stacked: this.options.stacked,
                ticks: {
                    color: this.options.darkMode ? '#ccc' : '#666'
                }
            },
            y: {
                type: this.options.yAxisType,
                grid: {
                    display: this.options.showGrid,
                    color: this.options.darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                },
                stacked: this.options.stacked,
                ticks: {
                    color: this.options.darkMode ? '#ccc' : '#666',
                    callback: function(value) {
                        return BaseChart.formatNumber(value);
                    }
                }
            }
        };
        
        // Configure chart options
        const chartOptions = {
            type: this.options.type,
            data: {
                labels: data.labels,
                datasets: datasets
            },
            options: {
                responsive: this.options.responsive,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                animation: {
                    duration: this.options.animationDuration
                },
                scales: scales,
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
                                label += BaseChart.formatNumber(context.parsed.y);
                                return label;
                            }
                        }
                    }
                }
            }
        };
        
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
     * Convert this chart to an area chart by enabling fill
     * 
     * @param {number} opacity - Fill opacity (0-1)
     */
    toArea(opacity = 0.2) {
        if (!this.chart) return this;
        
        this.options.fill = true;
        
        // Update all datasets
        this.chart.data.datasets.forEach((dataset, i) => {
            const color = dataset.borderColor;
            dataset.fill = true;
            dataset.backgroundColor = this.hexToRgba(color, opacity);
        });
        
        this.chart.update();
        return this;
    }
    
    /**
     * Convert this chart to a regular line chart by disabling fill
     */
    toLine() {
        if (!this.chart) return this;
        
        this.options.fill = false;
        
        // Update all datasets
        this.chart.data.datasets.forEach((dataset) => {
            dataset.fill = false;
            dataset.backgroundColor = dataset.borderColor;
        });
        
        this.chart.update();
        return this;
    }
    
    /**
     * Enable or disable stacking for this chart
     * 
     * @param {boolean} stacked - Whether to stack the datasets
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
     * Set the tension (curve smoothness) for all datasets
     * 
     * @param {number} tension - Tension value (0-1)
     */
    setTension(tension) {
        if (!this.chart) return this;
        
        this.options.tension = tension;
        
        this.chart.data.datasets.forEach((dataset) => {
            dataset.tension = tension;
        });
        
        this.chart.update();
        return this;
    }
}

// Export for module bundlers
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LineChart;
} 