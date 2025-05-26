// Dashboard.js - Handles dashboard data fetching and visualization

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing charts');
    // Initialize the dashboard
    initDashboard();
});

// Initialize dashboard
function initDashboard() {
    console.log('Initializing dashboard...');
    
    // Check if we're on the dashboard page
    const dashboardContainer = document.getElementById('admin-dashboard-container');
    if (!dashboardContainer) {
        console.log('Not on dashboard page, skipping initialization');
        return;
    }
    
    // Always force fresh data on page load (never use cache)
    fetchDashboardData(true);
}

// Debug helper to log the dashboard data
function debugDashboardData() {
    fetch('/admin/dashboard/data')
        .then(response => response.json())
        .then(data => {
            console.log('DEBUG - Dashboard data structure:', data);
            
            // Log specific chart data
            console.log('Order Stats:', data.orderStats);
            console.log('Delivered Orders:', data.deliveredOrdersStats);
            console.log('Delivered Products:', data.deliveredProducts);
        })
        .catch(error => {
            console.error('Debug data fetch error:', error);
        });
}

// Fetch dashboard data from the API
function fetchDashboardData(forceRefresh = true) {
    console.log('Fetching dashboard data...');
    
    // Show loading state
    setLoadingState(true);
    
    // Always request fresh data from server
    let url = '/admin/dashboard/data?refresh=1';
    
    // First, ensure all charts are cleared
    clearCharts();
    
    // Fetch data from the API
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Fetched dashboard data:', data);
            
            try {
                // Normalize data before initializing charts
                const normalizedData = normalizeApiData(data);
                console.log('Normalized data:', normalizedData);
                
                // Initialize charts with the data
                initializeCharts(normalizedData);
                
                // Update summary numbers
                updateSummaryNumbers(normalizedData);
            } catch (error) {
                console.error('Error processing dashboard data:', error);
                showErrorMessage('Erreur lors du traitement des données.');
            } finally {
                // Always hide loading state
                setLoadingState(false);
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            showErrorMessage('Échec du chargement des données. Veuillez réessayer plus tard.');
            setLoadingState(false);
        });
}

// Make fetchDashboardData globally accessible
window.fetchDashboardData = fetchDashboardData;

// Set loading state for the dashboard
function setLoadingState(isLoading) {
    const loadingOverlay = document.getElementById('dashboard-loading');
    if (loadingOverlay) {
        loadingOverlay.style.display = isLoading ? 'flex' : 'none';
    }
}

// Show error message
function showErrorMessage(message) {
    const errorContainer = document.getElementById('dashboard-error');
    if (errorContainer) {
        errorContainer.textContent = message;
        errorContainer.style.display = 'block';
    } else {
        console.error('Error container not found, message:', message);
    }
}

// Update summary numbers on the dashboard
function updateSummaryNumbers(data) {
    // Update total orders
    updateNumberElement('total-orders', data.orderStats?.total || 0);
    
    // Update delivered orders
    updateNumberElement('delivered-orders', data.deliveredOrdersStats?.delivered || 0);
    
    // Update users count if available
    if (data.userDistribution && data.userDistribution.totalUsers) {
        updateNumberElement('total-users', data.userDistribution.totalUsers);
    }
    
    // Update revenue if available
    if (data.revenue) {
        updateNumberElement('total-revenue', data.revenue.total || 0, true);
    }
}

// Update a number element with formatting
function updateNumberElement(id, value, isCurrency = false) {
    const element = document.getElementById(id);
    if (!element) return;
    
    if (isCurrency) {
        element.textContent = formatCurrency(value);
    } else {
        element.textContent = formatNumber(value);
    }
}

// Format number with thousands separator
function formatNumber(number) {
    return new Intl.NumberFormat().format(number);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    }).format(amount);
}

// Normalize API data to ensure consistent structure
function normalizeApiData(apiData) {
    const defaultData = {
        orderStats: {
            labels: ['En attente', 'Approuvée', 'Rejetée', 'Livrée'],
            series: [[0, 0, 0, 0]],
            total: 0,
            pending: { orders: 0 },
            approved: { orders: 0 },
            rejected: { orders: 0 },
            delivered: { orders: 0 }
        },
        deliveredOrdersStats: {
            labels: ['Livrée', 'Non livrée'],
            series: [[0, 0]],
            delivered: 0,
            notDelivered: 0
        },
        deliveredProducts: {
            labels: ['Aucun produit'],
            series: [[0]],
            message: 'Aucun produit disponible'
        },
        userDistribution: {
            labels: ['Aucun utilisateur'],
            series: [0],
            totalUsers: 0
        },
        orderTrends: {
            labels: generateDefaultDates(),
            series: [[0, 0, 0, 0, 0, 0, 0]]
        },
        revenue: {
            total: 0,
            monthly: 0,
            weekly: 0
        }
    };

    // Merge with defaults
    return {
        ...defaultData,
        ...apiData,
        // Handle specific nested properties
        orderStats: apiData.orderStats ? {...defaultData.orderStats, ...apiData.orderStats} : defaultData.orderStats,
        deliveredOrdersStats: apiData.deliveredOrdersStats ? {...defaultData.deliveredOrdersStats, ...apiData.deliveredOrdersStats} : defaultData.deliveredOrdersStats,
        deliveredProducts: apiData.deliveredProducts ? {...defaultData.deliveredProducts, ...apiData.deliveredProducts} : defaultData.deliveredProducts,
        userDistribution: apiData.userDistribution ? {...defaultData.userDistribution, ...apiData.userDistribution} : defaultData.userDistribution,
        orderTrends: apiData.orderTrends ? {...defaultData.orderTrends, ...apiData.orderTrends} : defaultData.orderTrends,
        revenue: apiData.revenue ? {...defaultData.revenue, ...apiData.revenue} : defaultData.revenue
    };
}

// Generate default dates for the last 7 days
function generateDefaultDates() {
    const dates = [];
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        dates.push(date.toLocaleDateString('fr-FR', {day: '2-digit', month: '2-digit'}));
    }
    return dates;
}

// Clear all existing charts
function clearCharts() {
    console.log('Clearing all charts');
    
    const chartElements = [
        'orderStatusChart', 
        'deliveredOrdersChart', 
        'deliveredProductsChart', 
        'userDistributionChart', 
        'orderTrendsChart'
    ];
    
    // First remove any ApexCharts instances
    window.ApexChartsInstances = window.ApexChartsInstances || {};
    chartElements.forEach(id => {
        if (window.ApexChartsInstances[id]) {
            try {
                window.ApexChartsInstances[id].destroy();
                window.ApexChartsInstances[id] = null;
            } catch (e) {
                console.warn(`Failed to destroy chart ${id}:`, e);
            }
        }
    });
    
    // Then clear the HTML containers
    chartElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = '';
        }
    });
}

// Initialize charts with data
function initializeCharts(data) {
    console.log('initializeCharts called in admin/dashboard.js');
    
    // Check if unified dashboard manager is present
    if (window.dashboardManager && typeof window.dashboardManager.initialize === 'function') {
        console.log('Deferring to unified dashboard manager for chart initialization');
        // Just emit event for unified manager to pick up
        document.dispatchEvent(new CustomEvent('dashboardRefreshed', {
            detail: { data: data }
        }));
        return;
    }
    
    console.log('Initializing charts with admin/dashboard.js');
    clearCharts();
    
    try {
        // Initialize each chart
        renderOrderStatusChart(data.orderStats);
        renderDeliveredOrdersChart(data.deliveredOrdersStats);
        renderDeliveredProductsChart(data.deliveredProducts);
        renderUserDistributionChart(data.userDistribution);
        renderOrderTrendsChart(data.orderTrends);
        
        console.log('All charts rendered successfully');
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
}

// Main function to initialize all charts
function renderCharts(apiData) {
    const data = normalizeApiData(apiData);
    console.log('Chart data to render:', data);
    
    // Clear any existing charts
    clearCharts();
    
    try {
        // Order Status Chart
        if (data.orderStats && data.orderStats.series && data.orderStats.labels) {
            renderOrderStatusChart(data.orderStats);
        }

        // Delivered Orders Chart
        if (data.deliveredOrdersStats && data.deliveredOrdersStats.series && data.deliveredOrdersStats.labels) {
            renderDeliveredOrdersChart(data.deliveredOrdersStats);
        }

        // Delivered Products Chart
        if (data.deliveredProducts) {
            renderDeliveredProductsChart(data.deliveredProducts);
        }

        // User Distribution Chart
        if (data.userDistribution) {
            renderUserDistributionChart(data.userDistribution);
        }

        // Order Trends Chart
        if (data.orderTrends) {
            renderOrderTrendsChart(data.orderTrends);
        }
        
        console.log('All charts rendered successfully');
        
    } catch (err) {
        console.error('Error rendering charts:', err);
    }
}

// Render Order Status Chart
function renderOrderStatusChart(data) {
    const element = document.getElementById('orderStatusChart');
    if (!element) return;
    
    console.log('Rendering order stats chart with data:', data);
    
    try {
        // Ensure we have proper data
        let chartValues = [];
        let labels = ['En attente', 'Approuvée', 'Rejetée', 'Livrée'];
        
        // Extract series data, ensuring they are numbers
        if (data.series && Array.isArray(data.series) && 
            data.series.length > 0 && 
            Array.isArray(data.series[0])) {
            chartValues = data.series[0].map(val => parseInt(val) || 0);
        } else {
            // Default to zeros matching the number of labels
            chartValues = [
                parseInt(data.pending?.orders) || 0,
                parseInt(data.approved?.orders) || 0,
                parseInt(data.rejected?.orders) || 0,
                parseInt(data.delivered?.orders) || 0
            ];
        }
        
        // Use provided labels if available
        if (data.labels && Array.isArray(data.labels)) {
            labels = data.labels;
        }
        
        // Create chart options with the correct format for ApexCharts bar chart
        const options = {
            series: chartValues,
            chart: { 
                type: 'pie', 
                height: 320,
                toolbar: { show: false }
            },
            labels: labels,
            colors: ['#FFB64D', '#10B981', '#FF5370', '#4680FF'],
            dataLabels: {
                enabled: true,
                formatter: function(val, opts) {
                    // Return the absolute value instead of percentage
                    return chartValues[opts.seriesIndex];
                }
            }
        };
        
        // Make sure we destroy any existing chart first
        window.ApexChartsInstances = window.ApexChartsInstances || {};
        if (window.ApexChartsInstances.orderStatusChart) {
            try {
                window.ApexChartsInstances.orderStatusChart.destroy();
            } catch (e) {
                console.warn('Failed to destroy order status chart:', e);
            }
        }
        
        // Clear any existing content
        element.innerHTML = '';
        
        // Create and render new chart
        const chart = new ApexCharts(element, options);
        chart.render();
        
        // Store the chart instance for later reference
        window.ApexChartsInstances.orderStatusChart = chart;
        
    } catch (error) {
        console.error('Error rendering order status chart:', error);
        element.innerHTML = '<div class="alert alert-warning">Erreur de chargement du graphique</div>';
    }
}

// Render Delivered Orders Chart
function renderDeliveredOrdersChart(data) {
    const element = document.getElementById('deliveredOrdersChart');
    if (!element) return;
    
    console.log('Rendering delivered orders chart with data:', data);
    
    try {
        // Ensure we have proper data
        let chartSeries = [];
        let labels = ['Livrée', 'Non livrée'];
        
        if (data.series && Array.isArray(data.series) && 
            data.series.length > 0 && 
            Array.isArray(data.series[0])) {
            chartSeries = data.series[0].map(val => parseInt(val) || 0);
        } else {
            // Default to zeros matching the number of labels
            chartSeries = [
                parseInt(data.delivered) || 0,
                parseInt(data.notDelivered) || 0
            ];
        }
        
        if (data.labels && Array.isArray(data.labels)) {
            labels = data.labels;
        }
        
        const options = {
            series: chartSeries,
            chart: { 
                type: 'donut', 
                height: 320,
                toolbar: { show: false }
            },
            labels: labels,
            colors: ['#10B981', '#FF5370'],
            legend: { position: 'bottom' }
        };
        
        // Make sure we destroy any existing chart first
        window.ApexChartsInstances = window.ApexChartsInstances || {};
        if (window.ApexChartsInstances.deliveredOrdersChart) {
            try {
                window.ApexChartsInstances.deliveredOrdersChart.destroy();
            } catch (e) {
                console.warn('Failed to destroy delivered orders chart:', e);
            }
        }
        
        // Clear any existing content
        element.innerHTML = '';
        
        // Create and render new chart
        const chart = new ApexCharts(element, options);
        chart.render();
        
        // Store the chart instance for later reference
        window.ApexChartsInstances.deliveredOrdersChart = chart;
        
    } catch (error) {
        console.error('Error rendering delivered orders chart:', error);
        element.innerHTML = '<div class="alert alert-warning">Erreur de chargement du graphique</div>';
    }
}

// Render Delivered Products Chart
function renderDeliveredProductsChart(data) {
    const element = document.getElementById('deliveredProductsChart');
    if (!element) return;
    
    console.log('Rendering delivered products chart with data:', data);
    
    try {
        let productLabels = ['Aucun produit'];
        let productData = [0];
        
        if (data.labels && 
            Array.isArray(data.labels) && 
            data.labels.length > 0 &&
            data.series && 
            Array.isArray(data.series) &&
            data.series.length > 0 &&
            Array.isArr                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  