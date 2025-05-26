/**
 * Dashboard Spacing Fix Utility
 * This script helps identify and fix any remaining spacing issues in the dashboard charts
 * Run this in the browser console if you notice spacing problems
 */

(function() {
    console.log('Running Dashboard Spacing Fix Utility...');
    
    // Define the chart containers to fix
    const chartContainers = [
        'orderStatusChart',
        'deliveredOrdersChart',
        'deliveredProductsChart', 
        'administrationChart',
        'orderTrendsChart',
        'userDeliveryChart'
    ];
    
    // Fix the chart containers
    chartContainers.forEach(id => {
        const container = document.getElementById(id);
        if (!container) {
            console.warn(`Chart container #${id} not found`);
            return;
        }
        
        // Remove height attributes that might be causing issues
        if (container.hasAttribute('style')) {
            const style = container.getAttribute('style');
            // Only remove height-related styles
            if (style.includes('height') || style.includes('min-height')) {
                console.log(`Fixing inline styles for #${id}`);
                const newStyle = style
                    .replace(/height\s*:\s*[^;]+;?/g, '')
                    .replace(/min-height\s*:\s*[^;]+;?/g, '');
                container.setAttribute('style', newStyle);
            }
        }
        
        // Ensure the chart has the correct height class
        if (!container.classList.contains('h-72')) {
            console.log(`Adding h-72 class to #${id}`);
            container.classList.add('h-72');
        }
        
        // Remove any potentially conflicting height classes
        ['h-80', 'h-96', 'h-full'].forEach(cls => {
            if (container.classList.contains(cls) && cls !== 'h-72') {
                console.log(`Removing ${cls} class from #${id}`);
                container.classList.remove(cls);
            }
        });
    });
    
    // Fix card padding if it's too large
    document.querySelectorAll('.bg-white.rounded-lg.shadow-md').forEach(card => {
        if (card.classList.contains('p-6')) {
            console.log('Fixing card padding from p-6 to p-4');
            card.classList.remove('p-6');
            card.classList.add('p-4');
        }
    });
    
    // Fix margins between elements
    document.querySelectorAll('.mb-4').forEach(el => {
        if (el.tagName === 'H2' || el.classList.contains('flex')) {
            console.log('Fixing margin-bottom from mb-4 to mb-2');
            el.classList.remove('mb-4');
            el.classList.add('mb-2');
        }
    });
    
    console.log('Dashboard Spacing Fix Utility completed');
    console.log('Refresh the page to see the changes');
})(); 