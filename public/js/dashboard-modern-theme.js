/**
 * Dashboard Charts - Modern Theme
 * This script enhances the visual appearance of all ApexCharts in the dashboard
 * with modern styling options like gradients, rounded corners, and modern colors.
 */
(function() {
    console.log('[Modern Theme] Initializing modern chart theme...');
    
    // Define modern color palette
    const modernColors = [
        '#3b82f6', // Blue
        '#10b981', // Green
        '#f59e0b', // Amber
        '#8b5cf6', // Purple
        '#ef4444', // Red
        '#06b6d4', // Cyan
        '#6366f1', // Indigo
        '#f97316', // Orange
        '#ec4899', // Pink
        '#14b8a6'  // Teal
    ];
    
    // Special colors for specific charts
    const deliveredOrdersColors = [
        '#10B981', // Green for "Livrée"
        '#EF4444'  // Red for "Non livrée"
    ];

    // Define common modern chart options
    const modernChartOptions = {
        chart: {
            fontFamily: 'Inter, Nunito, sans-serif',
            foreColor: '#64748b',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false
                },
                export: {
                    svg: {
                        filename: 'chart-svg',
                    },
                    png: {
                        filename: 'chart-png',
                    }
                },
                autoSelected: 'zoom'
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            },
            dropShadow: {
                enabled: true,
                enabledOnSeries: undefined,
                top: 5,
                left: 0,
                blur: 3,
                color: '#000',
                opacity: 0.1
            }
        },
        colors: modernColors,
        stroke: {
            curve: 'smooth',
            width: 3,
            lineCap: 'round'
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 3,
            padding: {
                top: 10,
                right: 10,
                bottom: 10,
                left: 10
            }
        },
        tooltip: {
            theme: 'light',
            x: {
                show: true,
                format: 'dd MMM',
                formatter: undefined,
            },
            style: {
                fontSize: '12px',
                fontFamily: 'Inter, Nunito, sans-serif'
            },
            marker: {
                show: true,
                size: 6
            },
            shared: true,
            intersect: false,
            custom: undefined
        },
        dataLabels: {
            style: {
                fontSize: '12px',
                fontFamily: 'Inter, Nunito, sans-serif',
                fontWeight: 'normal',
                colors: ['#64748b']
            },
            background: {
                enabled: true,
                foreColor: '#fff',
                padding: 4,
                borderRadius: 5,
                opacity: 0.9,
                borderWidth: 1,
                borderColor: '#f8fafc'
            }
        },
        xaxis: {
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px',
                    fontFamily: 'Inter, Nunito, sans-serif'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px',
                    fontFamily: 'Inter, Nunito, sans-serif'
                }
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '13px',
            fontFamily: 'Inter, Nunito, sans-serif',
            itemMargin: {
                horizontal: 8,
                vertical: 5
            },
            markers: {
                width: 12,
                height: 12,
                radius: 6
            }
        },
        responsive: [
            {
                breakpoint: 640,
                options: {
                    chart: {
                        height: 300
                    },
                    legend: {
                        position: 'bottom',
                        offsetY: 0
                    }
                }
            }
        ]
    };
    
    // Bar/column chart specific options
    const modernBarChartOptions = {
        ...modernChartOptions,
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '55%',
                distributed: false,
                rangeBarOverlap: true,
                rangeBarGroupRows: false,
                colors: {
                    ranges: [{
                        from: 0,
                        to: 0,
                        color: undefined
                    }],
                    backgroundBarColors: [],
                    backgroundBarOpacity: 1
                },
                dataLabels: {
                    position: 'top'
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.2,
                gradientToColors: undefined,
                inverseColors: false,
                opacityFrom: 0.85,
                opacityTo: 0.95,
                stops: [0, 100]
            }
        }
    };
    
    // Pie/donut chart specific options
    const modernPieChartOptions = {
        ...modernChartOptions,
        stroke: {
            width: 2,
            colors: ['#fff']
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.2,
                gradientToColors: undefined,
                inverseColors: false,
                opacityFrom: 0.9,
                opacityTo: 1,
                stops: [0, 90, 100]
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '50%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            fontSize: '16px',
                            fontFamily: 'Inter, Nunito, sans-serif',
                            color: '#334155'
                        }
                    }
                }
            }
        },
        labels: [],
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return val.toFixed(1) + '%';
            },
            textAnchor: 'middle',
            offsetX: 0,
            offsetY: 0,
            style: {
                fontSize: '12px',
                fontFamily: 'Inter, Nunito, sans-serif',
                fontWeight: '600',
                colors: ['#fff']
            },
            background: {
                enabled: false
            },
            dropShadow: {
                enabled: true,
                top: 1,
                left: 1,
                blur: 1,
                color: 'rgba(0,0,0,0.5)',
                opacity: 0.45
            }
        }
    };
    
    // Delivered Orders chart specific options (with specified colors)
    const deliveredOrdersChartOptions = {
        ...modernPieChartOptions,
        colors: deliveredOrdersColors
    };
    
    // Line chart specific options
    const modernLineChartOptions = {
        ...modernChartOptions,
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.2,
                gradientToColors: undefined,
                inverseColors: false,
                opacityFrom: 0.3,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        markers: {
            size: 5,
            colors: modernColors,
            strokeColors: '#fff',
            strokeWidth: 2,
            strokeOpacity: 0.9,
            strokeDashArray: 0,
            fillOpacity: 1,
            discrete: [],
            shape: 'circle',
            radius: 5,
            hover: {
                size: 8
            }
        }
    };
    
    // Apply modern theme to existing charts
    function applyModernThemeToCharts() {
        if (!window.chartInstances) {
            console.log('[Modern Theme] No chart instances found');
            return;
        }
        
        console.log('[Modern Theme] Applying modern theme to charts...');
        
        // Loop through each chart instance
        Object.keys(window.chartInstances).forEach(chartKey => {
            const chart = window.chartInstances[chartKey];
            if (!chart || !chart.updateOptions) {
                return;
            }
            
            try {
                console.log(`[Modern Theme] Updating ${chartKey} with modern styles`);
                
                // Determine chart type and apply appropriate options
                let options = modernChartOptions;
                
                // Special handling for deliveredOrdersChart - apply custom colors
                if (chartKey === 'deliveredOrdersChart') {
                    options = deliveredOrdersChartOptions;
                    console.log('[Modern Theme] Applied special colors for deliveredOrdersChart:', deliveredOrdersColors);
                } else {
                    // Check chart type by examining the chart configuration
                    const chartType = chart.w?.config?.chart?.type || '';
                    
                    if (chartType.includes('bar') || chartType.includes('column')) {
                        options = modernBarChartOptions;
                    } else if (chartType.includes('pie') || chartType.includes('donut')) {
                        options = modernPieChartOptions;
                    } else if (chartType.includes('line') || chartType.includes('area')) {
                        options = modernLineChartOptions;
                    }
                }
                
                // Apply the modern theme options
                chart.updateOptions(options, false, true);
                
                console.log(`[Modern Theme] Successfully updated ${chartKey}`);
            } catch (error) {
                console.error(`[Modern Theme] Error updating ${chartKey}:`, error);
            }
        });
    }
    
    // Apply modern theme to new charts as they're created
    function setupChartCreationHook() {
        // Store the original ApexCharts constructor
        const originalApexCharts = window.ApexCharts;
        if (!originalApexCharts) {
            console.warn('[Modern Theme] ApexCharts not found, cannot set up chart creation hook');
            return;
        }
        
        // Create a proxy for the ApexCharts constructor
        window.ApexCharts = function(el, options) {
            // Determine chart type and apply appropriate modern options
            let modernOptions = { ...modernChartOptions };
            
            // Check if this is the deliveredOrdersChart
            if (el.id === 'deliveredOrdersChart') {
                modernOptions = { ...deliveredOrdersChartOptions };
                console.log('[Modern Theme] Applied special colors for deliveredOrdersChart initialization');
            } else if (options.chart && options.chart.type) {
                const chartType = options.chart.type;
                
                if (chartType.includes('bar') || chartType.includes('column')) {
                    modernOptions = { ...modernBarChartOptions };
                } else if (chartType.includes('pie') || chartType.includes('donut')) {
                    modernOptions = { ...modernPieChartOptions };
                } else if (chartType.includes('line') || chartType.includes('area')) {
                    modernOptions = { ...modernLineChartOptions };
                }
            }
            
            // Merge the modern options with the provided options
            const mergedOptions = deepMerge(modernOptions, options);
            
            // Create the chart with the merged options
            return new originalApexCharts(el, mergedOptions);
        };
        
        // Copy all properties and prototype from the original constructor
        Object.setPrototypeOf(window.ApexCharts, originalApexCharts);
        Object.setPrototypeOf(window.ApexCharts.prototype, originalApexCharts.prototype);
        
        console.log('[Modern Theme] Chart creation hook set up');
    }
    
    // Deep merge utility function
    function deepMerge(target, source) {
        const output = { ...target };
        
        if (isObject(target) && isObject(source)) {
            Object.keys(source).forEach(key => {
                if (isObject(source[key])) {
                    if (!(key in target)) {
                        Object.assign(output, { [key]: source[key] });
                    } else {
                        output[key] = deepMerge(target[key], source[key]);
                    }
                } else {
                    Object.assign(output, { [key]: source[key] });
                }
            });
        }
        
        return output;
    }
    
    // Helper to check if value is an object
    function isObject(item) {
        return (item && typeof item === 'object' && !Array.isArray(item));
    }
    
    // Initialize when DOM is ready
    function init() {
        // Set up the chart creation hook
        setupChartCreationHook();
        
        // Apply modern theme to existing charts
        setTimeout(applyModernThemeToCharts, 500);
        
        // Also apply the theme after a delay to catch late-initialized charts
        setTimeout(applyModernThemeToCharts, 2000);
        
        console.log('[Modern Theme] Initialization complete');
    }
    
    // Initialize when the DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(); 