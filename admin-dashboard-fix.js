// Admin Chart Fix - Replace unwanted labels
const unwantedLabels = [
    'user', 'admin', 'utilisateur', 'administrateur', 
    'utilisateurs', 'administrateurs', 
    'en attente', 'en_attente', 'attente',
    'rejeté', 'rejetés', 'rejet', 
    'pending', 'approved', 'rejected'
];

// Common user patterns - including ID/matricule formats
const userPatterns = [
    /\(\d+\)/,       // Any number in parentheses like (2025), (8889)
    /utilisateur/i,  // Case insensitive "utilisateur"
    /user(\s|_)/i,   // "user" followed by space or underscore
    /matricule/i,    // Any reference to "matricule"
    /^[A-Z]\d+$/,    // Format like B1, A1 followed by numbers
    /^[A-Z]\d+\s/    // Format like B1, A1 at the beginning of string
];

// Function to clean up chart labels on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - Setting up administration chart cleaning');
    
    // Always force chart refresh at page load
    setTimeout(forceAdministrationChartRefresh, 1000);
    setTimeout(forceAdministrationChartRefresh, 3000);
    
    // Wait for charts to be initialized
    const checkInterval = setInterval(function() {
        const administrationChart = document.getElementById('administrationChart');
        
        // Check all possible chart instance locations
        if (administrationChart && (
            (window.charts && window.charts.administrationChart) || 
            (window.chartInstances && window.chartInstances.administrationChart) ||
            (window.adminData && window.adminData.chartInstance) ||
            (window.administrationChart)
        )) {
            console.log('Found administration chart, cleaning labels...');
            clearInterval(checkInterval);
            cleanAdministrationChart();
        }
    }, 1000);
    
    // Also try after a delay anyway
    setTimeout(cleanAdministrationChart, 3000);
});

// Force refresh of administration chart with correct data from server
function forceAdministrationChartRefresh() {
    console.log('Forcing administration chart refresh');
    
    // Look for data in multiple locations
    let chartData = null;
    let chart = findAdministrationChart();
    
    if (!chart) {
        console.log('No administration chart instance found');
        return;
    }
    
    // Fetch fresh data from server
    fetch('/admin/dashboard/data?refresh=1&nocache=' + Date.now())
        .then(response => response.json())
        .then(data => {
            if (!data || !data.administrationStats) {
                console.warn('No administration data in response');
                return;
            }
            
            // Get clean administration data
            const adminData = data.administrationStats;
            
            // Ensure we're dealing with administration data, not user data
            if (adminData.chart_type === 'administration' || 
                (adminData.labels && adminData.labels.length > 0 && 
                 !isUserLabel(adminData.labels[0]))) {
                
                console.log('Found valid administration data, updating chart');
                
                // Get clean labels and data
                const labels = adminData.labels || [];
                const seriesData = adminData.series && adminData.series[0] && 
                                  adminData.series[0].data ? 
                                  adminData.series[0].data : 
                                  [0];
                
                // Update the chart
                chart.updateOptions({
                    series: [{
                        name: 'Commandes livrées',
                        data: seriesData
                    }],
                    xaxis: {
                        categories: labels
                    }
                });
                
                console.log('Administration chart updated with fresh data');
            } else {
                console.warn('Received data does not appear to be valid administration data');
            }
        })
        .catch(error => {
            console.error('Error fetching fresh administration data:', error);
        });
}

// Helper function to find administration chart instance
function findAdministrationChart() {
    // Try all possible locations
    let chart = null;
    
    if (window.charts && window.charts.administrationChart) {
        console.log('Found chart in window.charts.administrationChart');
        chart = window.charts.administrationChart;
    } else if (window.chartInstances && window.chartInstances.administrationChart) {
        console.log('Found chart in window.chartInstances.administrationChart');
        chart = window.chartInstances.administrationChart;
    } else if (window.adminData && window.adminData.chartInstance) {
        console.log('Found chart in window.adminData.chartInstance');
        chart = window.adminData.chartInstance;
    } else if (window.administrationChart) {
        console.log('Found chart in window.administrationChart');
        chart = window.administrationChart;
    }
    
    return chart;
}

// Helper function to check if a label is likely a user label
function isUserLabel(label) {
    if (!label || typeof label !== 'string') return false;
    
    // Check against all user patterns
    for (const pattern of userPatterns) {
        if (pattern.test(label)) {
            console.log(`Label "${label}" matched user pattern ${pattern}`);
            return true;
        }
    }
    
    return false;
}

// Function to clean unwanted labels from administration chart
function cleanAdministrationChart() {
    // Find the chart instance
    let chart = findAdministrationChart();
    
    if (!chart) {
        console.log('Administration chart not available yet for cleaning');
        return;
    }
    
    if (!chart.w || !chart.w.globals || !chart.w.globals.labels) {
        console.log('Chart data not available yet');
        return;
    }
    
    const labels = chart.w.globals.labels;
    const series = chart.w.config.series[0].data;
    
    console.log('Original labels:', labels);
    console.log('Original series:', series);
    
    // Check if this appears to be user data instead of administration data
    const hasUserData = labels.some(label => isUserLabel(label));
    
    if (hasUserData) {
        console.log('Detected user data in administration chart, forcing refresh with server data');
        forceAdministrationChartRefresh();
        return;
    }
    
    // Filter out unwanted labels
    const filteredData = [];
    
    for (let i = 0; i < labels.length; i++) {
        const label = labels[i];
        const lowerLabel = typeof label === 'string' ? label.toLowerCase().trim() : '';
        
        // Skip this label if it matches or contains any of the unwanted terms
        let shouldKeep = true;
        
        // Check against unwanted terms
        for (const unwanted of unwantedLabels) {
            if (lowerLabel === unwanted || lowerLabel.includes(unwanted)) {
                shouldKeep = false;
                console.log('Filtering out unwanted label:', label);
                break;
            }
        }
        
        // Check against user patterns
        if (shouldKeep && typeof label === 'string') {
            for (const pattern of userPatterns) {
                if (pattern.test(label)) {
                    shouldKeep = false;
                    console.log('Filtering out user pattern match:', label);
                    break;
                }
            }
        }
        
        // Keep this data point only if label is valid
        if (shouldKeep && lowerLabel.length > 3) {
            filteredData.push({
                label: label,
                value: series[i]
            });
        }
    }
    
    console.log('Filtered data:', filteredData);
    
    // If we have no valid data, fetch from server
    if (filteredData.length === 0) {
        console.log('No valid administration data after filtering, fetching from server');
        forceAdministrationChartRefresh();
        return;
    }
    
    // Sort by value (descending)
    filteredData.sort((a, b) => b.value - a.value);
    
    // Extract back to arrays
    const newLabels = filteredData.map(item => item.label);
    const newSeries = filteredData.map(item => item.value);
    
    // Update the chart
    chart.updateOptions({
        series: [{
            name: 'Commandes livrées',
            data: newSeries
        }],
        xaxis: {
            categories: newLabels
        }
    });
    
    console.log('Chart updated with cleaned data');
}

// Expose function globally to allow manual triggering
window.cleanAdministrationChart = cleanAdministrationChart;
window.forceAdministrationChartRefresh = forceAdministrationChartRefresh; 