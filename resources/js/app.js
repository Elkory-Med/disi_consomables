import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import * as Chartist from 'chartist';
import { 
    orderStatusChart,
    deliveredOrdersChart,
    productDistributionChart,
    deliveredProductsChart,
    orderTrendsChart 
} from './charts';

// Explicitly log our imported modules to verify they exist
console.log('ApexCharts imported:', ApexCharts ? 'Yes' : 'No');
console.log('Chartist imported:', Chartist ? 'Yes' : 'No');
console.log('Chart functions imported:', {
    orderStatusChart: typeof orderStatusChart === 'function',
    deliveredOrdersChart: typeof deliveredOrdersChart === 'function',
    productDistributionChart: typeof productDistributionChart === 'function',
    deliveredProductsChart: typeof deliveredProductsChart === 'function',
    orderTrendsChart: typeof orderTrendsChart === 'function'
});

// Register chart functions globally regardless of Alpine initialization
window.orderStatusChart = orderStatusChart;
window.deliveredOrdersChart = deliveredOrdersChart;
window.deliveredProductsChart = deliveredProductsChart;
window.productDistributionChart = productDistributionChart;
window.orderTrendsChart = orderTrendsChart;

// Set ApexCharts and Chartist on window
window.ApexCharts = ApexCharts;
window.Chartist = Chartist;

// Only initialize Alpine if it's not already initialized
if (!window.Alpine) {
    // Initialize Alpine store
    Alpine.store('charts', {
        data: null,
        loading: false,
        error: null
    });

    // Set Alpine on window
    window.Alpine = Alpine;

    // Start Alpine
    document.addEventListener('DOMContentLoaded', () => {
        Alpine.start();
    });
}

// Fix for navigation links requiring double-click
document.addEventListener('DOMContentLoaded', () => {
    // This fixes an issue with links in the admin sidebar sometimes requiring two clicks
    const handleAdminNavLinks = () => {
        const sidebarLinks = document.querySelectorAll('aside a');
        sidebarLinks.forEach(link => {
            // Remove any existing click listeners to prevent multiple bindings
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            
            // Add a proper click handler that prevents event conflicts
            newLink.addEventListener('click', (e) => {
                // Prevent default only if it's not disabled by other means
                if (!newLink.hasAttribute('disabled') && !newLink.classList.contains('disabled')) {
                    const href = newLink.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        // Show visual feedback that the link was clicked
                        newLink.classList.add('opacity-70');
                        
                        // Navigate to the link
                        window.location.href = href;
                    }
                }
            });
        });
    };
    
    // Run initially
    handleAdminNavLinks();
    
    // Also run when Livewire updates the DOM
    document.addEventListener('livewire:navigated', handleAdminNavLinks);
    document.addEventListener('livewire:load', handleAdminNavLinks);
});
