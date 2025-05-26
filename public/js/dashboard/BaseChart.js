/**
 * BaseChart - Foundation class for all dashboard charts
 * 
 * This class provides common functionality for chart initialization,
 * rendering, updating, and formatting. All specific chart implementations
 * should extend this base class.
 */
class BaseChart {
    /**
     * Create a new chart instance
     * 
     * @param {string} elementId - The ID of the DOM element to render the chart in
     * @param {Object} options - Configuration options for the chart
     */
    constructor(elementId, options = {}) {
        // Default options
        this.options = Object.assign({
            type: 'line',
            title: '',
            colors: ['#4e73df', '#1cc88a', '#36b9cc'],
            darkMode: document.body.classList.contains('dark-mode'),
            stacked: false,
            showLegend: true,
            animationDuration: 800,
            responsive: true
        }, options);
        
        // Get canvas element
        this.elementId = elementId;
        this.canvas = document.getElementById(elementId);
        
        if (!this.canvas) {
            console.error(`Canvas element with ID "${elementId}" not found`);
            return;
        }
        
        // Get rendering context
        this.ctx = this.canvas.getContext('2d');
        
        // Chart instance (will be created in buildChart)
        this.chart = null;
        
        // Bind methods to this instance
        this.update = this.update.bind(this);
        this.destroy = this.destroy.bind(this);
        this.toggleDarkMode = this.toggleDarkMode.bind(this);
        this.buildChart = this.buildChart.bind(this);
    }
    
    /**
     * Build the chart with the provided data
     * This method should be overridden by child classes
     * 
     * @param {Object} data - The data to build the chart with
     */
    buildChart(data) {
        console.warn('BaseChart.buildChart() called - should be overridden by child class');
    }
    
    /**
     * Update the chart with new data
     * 
     * @param {Object} data - The new data to update the chart with
     */
    update(data) {
        if (!this.canvas) {
            console.error(`Cannot update chart: canvas element "${this.elementId}" not found`);
            return;
        }
        
        // If chart exists, destroy it before rebuilding
        if (this.chart) {
            this.destroy();
        }
        
        // Build the chart with new data
        this.buildChart(data);
        
        // Dispatch event indicating the chart was updated
        const event = new CustomEvent('chart:updated', {
            detail: {
                chartId: this.elementId,
                instance: this
            }
        });
        document.dispatchEvent(event);
    }
    
    /**
     * Destroy the chart instance and clean up
     */
    destroy() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
            
            // Dispatch event indicating the chart was destroyed
            const event = new CustomEvent('chart:destroyed', {
                detail: {
                    chartId: this.elementId,
                    instance: this
                }
            });
            document.dispatchEvent(event);
        }
    }
    
    /**
     * Toggle dark mode for this chart
     * 
     * @param {boolean} isDarkMode - Whether dark mode is enabled
     */
    toggleDarkMode(isDarkMode) {
        if (this.options.darkMode === isDarkMode) {
            return; // No change needed
        }
        
        this.options.darkMode = isDarkMode;
        
        // If chart exists, update it
        if (this.chart && this.chart.data) {
            // Update colors based on dark mode
            this.chart.options.scales.x.ticks.color = isDarkMode ? '#e0e0e0' : '#666';
            this.chart.options.scales.y.ticks.color = isDarkMode ? '#e0e0e0' : '#666';
            this.chart.options.scales.x.grid.color = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            this.chart.options.scales.y.grid.color = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            
            // Update dataset border colors
            this.chart.data.datasets.forEach(dataset => {
                dataset.borderColor = isDarkMode ? '#fff' : '#000';
            });
            
            // Update the chart
            this.chart.update();
        }
    }
    
    /**
     * Format a number for display
     * 
     * @param {number} value - The number to format
     * @returns {string} Formatted number
     */
    static formatNumber(value) {
        if (value === null || value === undefined) {
            return '0';
        }
        
        // Format based on magnitude
        if (value >= 1000000) {
            return (value / 1000000).toFixed(1) + 'M';
        } else if (value >= 1000) {
            return (value / 1000).toFixed(1) + 'K';
        } else {
            return value.toLocaleString();
        }
    }
    
    /**
     * Format a percentage for display
     * 
     * @param {number} value - The percentage value to format
     * @returns {string} Formatted percentage
     */
    static formatPercentage(value) {
        if (value === null || value === undefined) {
            return '0%';
        }
        
        return value.toFixed(1) + '%';
    }
    
    /**
     * Format a currency value for display
     * 
     * @param {number} value - The currency value to format
     * @param {string} currency - The currency code (default: USD)
     * @returns {string} Formatted currency value
     */
    static formatCurrency(value, currency = 'USD') {
        if (value === null || value === undefined) {
            return '$0.00';
        }
        
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(value);
    }
    
    /**
     * Format a date for display
     * 
     * @param {string|Date} date - The date to format
     * @param {string} format - The format to use (default: 'short')
     * @returns {string} Formatted date
     */
    static formatDate(date, format = 'short') {
        if (!date) {
            return '';
        }
        
        const dateObj = typeof date === 'string' ? new Date(date) : date;
        
        if (isNaN(dateObj.getTime())) {
            return 'Invalid date';
        }
        
        switch (format) {
            case 'short':
                return dateObj.toLocaleDateString();
            case 'long':
                return dateObj.toLocaleDateString(undefined, {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            case 'time':
                return dateObj.toLocaleTimeString(undefined, {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            case 'datetime':
                return dateObj.toLocaleDateString() + ' ' + 
                       dateObj.toLocaleTimeString(undefined, {
                           hour: '2-digit',
                           minute: '2-digit'
                       });
            default:
                return dateObj.toLocaleDateString();
        }
    }
    
    /**
     * Generate an array of colors based on a base color
     * 
     * @param {string} baseColor - The base color in hex format
     * @param {number} count - Number of colors to generate
     * @param {number} saturationStep - Step to change saturation (0-1)
     * @param {number} lightnessStep - Step to change lightness (0-1)
     * @returns {Array} Array of generated colors
     */
    static generateColors(baseColor, count, saturationStep = 0.1, lightnessStep = 0.1) {
        // Convert hex to HSL
        const hexToHSL = (hex) => {
            // Convert hex to RGB first
            let r = 0, g = 0, b = 0;
            if (hex.length === 4) {
                r = parseInt(hex[1] + hex[1], 16) / 255;
                g = parseInt(hex[2] + hex[2], 16) / 255;
                b = parseInt(hex[3] + hex[3], 16) / 255;
            } else if (hex.length === 7) {
                r = parseInt(hex.substring(1, 3), 16) / 255;
                g = parseInt(hex.substring(3, 5), 16) / 255;
                b = parseInt(hex.substring(5, 7), 16) / 255;
            }
            
            // Find min and max RGB values
            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            let h = 0, s = 0, l = (max + min) / 2;
            
            if (max !== min) {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                
                switch (max) {
                    case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                    case g: h = (b - r) / d + 2; break;
                    case b: h = (r - g) / d + 4; break;
                }
                
                h /= 6;
            }
            
            return [h, s, l];
        };
        
        // Convert HSL to hex
        const hslToHex = (h, s, l) => {
            let r, g, b;
            
            if (s === 0) {
                r = g = b = l;
            } else {
                const hue2rgb = (p, q, t) => {
                    if (t < 0) t += 1;
                    if (t > 1) t -= 1;
                    if (t < 1/6) return p + (q - p) * 6 * t;
                    if (t < 1/2) return q;
                    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                    return p;
                };
                
                const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                const p = 2 * l - q;
                
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
            }
            
            const toHex = (x) => {
                const hex = Math.round(x * 255).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            };
            
            return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
        };
        
        // Get base HSL values
        const [h, s, l] = hexToHSL(baseColor);
        const colors = [];
        
        // Generate colors
        for (let i = 0; i < count; i++) {
            // Adjust saturation and lightness
            const newS = Math.max(0, Math.min(1, s - i * saturationStep));
            const newL = Math.max(0.2, Math.min(0.8, l + i * lightnessStep));
            
            // Convert back to hex and add to array
            colors.push(hslToHex(h, newS, newL));
        }
        
        return colors;
    }
}

// Export for module usage
export default BaseChart;