/**
 * PieChart - Pie/Doughnut chart implementation for dashboard
 * 
 * This class extends the BaseChart class to provide specific 
 * implementation for pie and doughnut charts.
 */
class PieChart extends BaseChart {
    /**
     * Create a new pie/doughnut chart instance
     * 
     * @param {string} elementId - The ID of the DOM element to render the chart in
     * @param {Object} options - Configuration options for the chart
     */
    constructor(elementId, options = {}) {
        // Set default options for pie charts
        const pieOptions = Object.assign({
            type: 'pie', // 'pie' or 'doughnut'
            cutout: undefined, // For doughnut charts (percentage of chart radius)
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 8,
            showLabelsInLegend: true,
            showLabelsOnChart: false,
            showPercentages: true
        }, options);
        
        // Automatically set cutout for doughnut charts if not specified
        if (pieOptions.type === 'doughnut' && pieOptions.cutout === undefined) {
            pieOptions.cutout = '50%';
        }
        
        // Call parent constructor
        super(elementId, pieOptions);
    }
    
    /**
     * Build pie/doughnut chart with the provided data
     * 
     * @param {Object} data - Data for the chart containing labels and datasets
     */
    buildChart(data) {
        if (!this.canvas) {
            console.error('Cannot build chart: Canvas element not found');
            return;
        }
        
        if (!data || !data.labels || !data.datasets) {
            console.error('Invalid data format for pie chart');
            return;
        }
        
        // Get the first dataset (pie charts typically only have one dataset)
        const dataset = data.datasets[0];
        
        // Generate colors if not provided
        const colors = dataset.colors || dataset.backgroundColor || 
            this.generateColors(this.options.colors[0], data.labels.length);
        
        // Configure tooltip callback to show percentages if needed
        const tooltipCallback = this.options.showPercentages ? 
            function(context) {
                const label = context.label || '';
                const value = context.raw;
                const sum = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = (value / sum * 100).toFixed(1) + '%';
                
                const valueFormatted = BaseChart.formatNumber(value);
                return ` ${label}: ${valueFormatted} (${percentage})`;
            } : 
            function(context) {
                const label = context.label || '';
                const value = BaseChart.formatNumber(context.raw);
                return ` ${label}: ${value}`;
            };
        
        // Configure chart options
        const chartOptions = {
            type: this.options.type,
            data: {
                labels: data.labels,
                datasets: [{
                    label: dataset.label || 'Data',
                    data: dataset.data,
                    backgroundColor: colors,
                    borderColor: this.options.borderColor,
                    borderWidth: this.options.borderWidth,
                    hoverOffset: this.options.hoverOffset,
                    weight: dataset.weight || 1
                }]
            },
            options: {
                responsive: this.options.responsive,
                maintainAspectRatio: false,
                cutout: this.options.cutout,
                animation: {
                    duration: this.options.animationDuration
                },
                plugins: {
                    legend: {
                        display: this.options.showLabelsInLegend,
                        position: 'top',
                        labels: {
                            color: this.options.darkMode ? '#e0e0e0' : '#666',
                            padding: 15,
                            generateLabels: (chart) => {
                                // Custom legend labels to include percentages if needed
                                const labels = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                
                                if (this.options.showPercentages) {
                                    const data = chart.data.datasets[0].data;
                                    const total = data.reduce((a, b) => a + b, 0);
                                    
                                    labels.forEach((label, i) => {
                                        const value = data[i];
                                        const percent = (value / total * 100).toFixed(1) + '%';
                                        label.text = `${label.text} (${percent})`;
                                    });
                                }
                                
                                return labels;
                            }
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
                            label: tooltipCallback
                        }
                    },
                    datalabels: this.options.showLabelsOnChart ? {
                        color: '#fff',
                        font: {
                            weight: 'bold'
                        },
                        formatter: (value, ctx) => {
                            const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = (value / sum * 100).toFixed(1) + '%';
                            return percentage;
                        }
                    } : false
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
     * Convert this chart to a doughnut type
     * 
     * @param {string} cutout - Cutout percentage (e.g. '50%')
     */
    toDoughnut(cutout = '50%') {
        if (!this.chart) return;
        
        this.options.type = 'doughnut';
        this.options.cutout = cutout;
        
        // If the chart exists, update it
        if (this.chart) {
            this.chart.config.type = 'doughnut';
            this.chart.config.options.cutout = cutout;
            this.chart.update();
        }
        
        return this;
    }
    
    /**
     * Convert this chart to a pie type (no cutout)
     */
    toPie() {
        if (!this.chart) return;
        
        this.options.type = 'pie';
        this.options.cutout = 0;
        
        // If the chart exists, update it
        if (this.chart) {
            this.chart.config.type = 'pie';
            this.chart.config.options.cutout = 0;
            this.chart.update();
        }
        
        return this;
    }
}

// Export for module bundlers
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PieChart;
} 