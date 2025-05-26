/**
 * Dashboard CSS Cleanup Utility
 * This script helps identify any elements that might still be relying on the old CSS files
 * Run this in the browser console after implementing the new unified CSS to ensure a safe transition
 */

(function() {
    console.log('Running Dashboard CSS Cleanup Utility...');
    
    // Check for the presence of the new unified CSS
    const hasUnifiedCSS = Array.from(document.styleSheets).some(sheet => {
        try {
            return sheet.href && sheet.href.includes('dashboard-charts-unified.css');
        } catch (e) {
            return false;
        }
    });
    
    if (!hasUnifiedCSS) {
        console.error('ERROR: The unified CSS file is not loaded. Please make sure dashboard-charts-unified.css is properly linked.');
        return;
    }
    
    console.log('✓ Unified CSS file is properly loaded');
    
    // Check for elements that might be using old CSS classes
    const oldClassChecks = [
        { selector: '.chart-btn', expected: true, message: 'New chart-btn class is applied correctly' },
        { selector: '.chart-select', expected: true, message: 'New chart-select class is applied correctly' },
        { selector: '.page-indicator', expected: true, message: 'New page-indicator class is applied correctly' },
        { selector: '.chart-loading-spinner', expected: true, message: 'New chart-loading-spinner class is applied correctly' }
    ];
    
    oldClassChecks.forEach(check => {
        const elements = document.querySelectorAll(check.selector);
        const exists = elements.length > 0;
        
        if (exists === check.expected) {
            console.log(`✓ ${check.message}`);
        } else {
            console.error(`ERROR: ${check.selector} elements ${check.expected ? 'not found' : 'still exist'}`);
        }
    });
    
    // Check if charts are rendered correctly
    const chartContainers = [
        'orderStatusChart',
        'deliveredOrdersChart',
        'deliveredProductsChart',
        'administrationChart',
        'orderTrendsChart',
        'userDeliveryChart'
    ];
    
    chartContainers.forEach(id => {
        const container = document.getElementById(id);
        if (!container) {
            console.warn(`⚠ Chart container #${id} not found`);
            return;
        }
        
        // Check if the chart is rendered
        const hasChart = container.querySelector('.apexcharts-canvas');
        if (hasChart) {
            console.log(`✓ Chart in #${id} is rendered correctly`);
        } else {
            console.warn(`⚠ Chart in #${id} may not be rendered`);
        }
        
        // Check for any inline styles that might be overriding CSS
        if (container.hasAttribute('style')) {
            console.warn(`⚠ #${id} has inline styles that might override CSS: ${container.getAttribute('style')}`);
        }
    });
    
    // Summary message about cleanup
    console.log('');
    console.log('CSS Cleanup Recommendations:');
    console.log('1. Once you confirm all charts are displaying correctly with the unified CSS:');
    console.log('   - Remove dashboard-charts-style.css');
    console.log('   - Remove dashboard-charts.css');
    console.log('   - Remove any inline styles from chart containers');
    console.log('2. Make sure any JavaScript code is using the new class names');
    console.log('3. Update any other views that might be using the old CSS files');
    
    console.log('');
    console.log('Dashboard CSS Cleanup Utility completed');
})(); 