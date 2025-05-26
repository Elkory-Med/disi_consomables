<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ChartService
{
    /**
     * Get order status chart configuration
     * 
     * @param array $data The chart data
     * @return array Chart configuration for ApexCharts
     */
    public function getOrderStatusChartConfig(array $data): array
    {
        try {
            // Ensure we have proper data structure
            $labels = $data['labels'] ?? ['En attente', 'Approuvée', 'Rejetée', 'Livrée'];
            
            // Extract series data, ensuring they are numbers
            $series = [];
            if (isset($data['series']) && is_array($data['series'])) {
                if (isset($data['series'][0]) && is_array($data['series'][0])) {
                    $series = array_map(function($val) {
                        return (int) $val;
                    }, $data['series'][0]);
                } else {
                    $series = array_map(function($val) {
                        return (int) $val;
                    }, $data['series']);
                }
            } else {
                // Use individual status counts if available
                $series = [
                    (int) ($data['pending']['orders'] ?? 0),
                    (int) ($data['approved']['orders'] ?? 0),
                    (int) ($data['rejected']['orders'] ?? 0),
                    (int) ($data['delivered']['orders'] ?? 0)
                ];
            }
            
            return [
                'chart' => [
                    'type' => 'pie',
                    'height' => 320,
                    'toolbar' => ['show' => false],
                    'animations' => [
                        'enabled' => true,
                        'speed' => 300,
                    ],
                ],
                'series' => $series,
                'labels' => $labels,
                'colors' => ['#FFB64D', '#10B981', '#FF5370', '#4680FF'],
                'legend' => [
                    'position' => 'bottom',
                    'horizontalAlign' => 'center',
                    'fontSize' => '14px',
                ],
                'dataLabels' => [
                    'enabled' => true,
                    'formatter' => 'function(val, opts) { return opts.w.globals.series[opts.seriesIndex]; }',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
                'responsive' => [
                    [
                        'breakpoint' => 480,
                        'options' => [
                            'chart' => [
                                'height' => 280
                            ],
                            'legend' => [
                                'position' => 'bottom'
                            ]
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error creating order status chart config: ' . $e->getMessage());
            return $this->getEmptyChartConfig('pie', 'Statut des commandes');
        }
    }
    
    /**
     * Get delivered orders chart configuration
     * 
     * @param array $data The chart data
     * @return array Chart configuration for ApexCharts
     */
    public function getDeliveredOrdersChartConfig(array $data): array
    {
        try {
            // Ensure we have proper data structure
            $labels = $data['labels'] ?? ['Livrée', 'Non livrée'];
            
            // Extract series data, ensuring they are numbers
            $series = [];
            if (isset($data['series']) && is_array($data['series'])) {
                if (isset($data['series'][0]) && is_array($data['series'][0])) {
                    $series = array_map(function($val) {
                        return (int) $val;
                    }, $data['series'][0]);
                } else {
                    $series = array_map(function($val) {
                        return (int) $val;
                    }, $data['series']);
                }
            } else {
                // Use direct counts if available
                $series = [
                    (int) ($data['delivered'] ?? 0),
                    (int) ($data['notDelivered'] ?? 0)
                ];
            }
            
            return [
                'chart' => [
                    'type' => 'pie',
                    'height' => 320,
                    'toolbar' => ['show' => false],
                ],
                'series' => $series,
                'labels' => $labels,
                'colors' => ['#10B981', '#EF4444'],
                'legend' => [
                    'position' => 'bottom',
                    'horizontalAlign' => 'center',
                    'fontSize' => '14px',
                ],
                'dataLabels' => [
                    'enabled' => true,
                    'formatter' => 'function(val, opts) { return opts.w.globals.series[opts.seriesIndex]; }',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
                'responsive' => [
                    [
                        'breakpoint' => 480,
                        'options' => [
                            'chart' => [
                                'height' => 280
                            ]
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error creating delivered orders chart config: ' . $e->getMessage());
            return $this->getEmptyChartConfig('pie', 'Commandes livrées');
        }
    }
    
    /**
     * Get order trends chart configuration
     * 
     * @param array $data The chart data
     * @return array Chart configuration for ApexCharts
     */
    public function getOrderTrendsChartConfig(array $data): array
    {
        try {
            // Ensure we have proper data structure
            $labels = $data['labels'] ?? $this->generateDefaultDates();
            
            // Extract series data
            $series = [];
            if (isset($data['series']) && is_array($data['series'])) {
                if (isset($data['series'][0]) && is_array($data['series'][0])) {
                    $series = array_map(function($val) {
                        return (int) $val;
                    }, $data['series'][0]);
                } else {
                    $series = array_map(function($val) {
                        return (int) $val;
                    }, $data['series']);
                }
            } else {
                // Default to zeros
                $series = array_fill(0, count($labels), 0);
            }
            
            return [
                'chart' => [
                    'type' => 'line',
                    'height' => 320,
                    'toolbar' => ['show' => false],
                    'zoom' => ['enabled' => true],
                ],
                'series' => [
                    [
                        'name' => 'Commandes',
                        'data' => $series
                    ]
                ],
                'xaxis' => [
                    'categories' => $labels,
                    'labels' => [
                        'rotate' => -45,
                        'rotateAlways' => false,
                    ]
                ],
                'yaxis' => [
                    'labels' => [
                        'formatter' => 'function(val) { return parseInt(val); }'
                    ]
                ],
                'stroke' => [
                    'curve' => 'smooth',
                    'width' => 3
                ],
                'colors' => ['#4680FF'],
                'markers' => [
                    'size' => 5
                ],
                'grid' => [
                    'borderColor' => '#e0e0e0',
                    'strokeDashArray' => 4,
                    'padding' => [
                        'left' => 0,
                        'right' => 0
                    ]
                ],
                'tooltip' => [
                    'shared' => true,
                    'intersect' => false
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error creating order trends chart config: ' . $e->getMessage());
            return $this->getEmptyChartConfig('line', 'Tendance des commandes');
        }
    }
    
    /**
     * Get delivered products chart configuration
     * 
     * @param array $data The chart data
     * @return array Chart configuration for ApexCharts
     */
    public function getDeliveredProductsChartConfig(array $data): array
    {
        try {
            // Ensure we have proper data structure for ALL products first
            $allLabels = $data['labels'] ?? [];
            $allSeriesData = [];
            if (isset($data['series']) && is_array($data['series'])) {
                if (isset($data['series'][0]) && is_array($data['series'][0])) {
                    $allSeriesData = array_map('intval', $data['series'][0]);
                } else {
                    $allSeriesData = array_map('intval', $data['series']);
                }
            } else {
                // Handle case where data might be flat key-value pairs
                if (!empty($data) && count($allLabels) == 0) {
                    $allLabels = array_keys($data);
                    $allSeriesData = array_map('intval', array_values($data));
                }
            }
            
            // Combine labels and series, then sort by series data (descending)
            $combinedData = [];
            for ($i = 0; $i < count($allLabels); $i++) {
                if (isset($allSeriesData[$i])) { // Ensure series data exists for the label
                    $combinedData[] = [
                        'label' => $allLabels[$i],
                        'value' => $allSeriesData[$i]
                    ];
                }
            }
            
            usort($combinedData, function($a, $b) {
                return $b['value'] <=> $a['value'];
            });
            
            // Settings for pagination
            $limit = 15;
            $total = count($combinedData);
            $paginationEnabled = $total > $limit;
            
            // Get the current page data (first page by default)
            $currentPageData = array_slice($combinedData, 0, $limit);
            $currentLabels = array_column($currentPageData, 'label');
            $currentSeries = array_column($currentPageData, 'value');

            // Handle empty state AFTER processing potential data
            if ($total === 0) {
                $currentLabels = ['Aucun produit'];
                $currentSeries = [0];
            }

            // For large datasets, use pagination options
            $chartHeight = max(min(count($currentLabels) * 35, 600), 350); // Adjust height based on current page items
            
            return [
                'chart' => [
                    'type' => 'bar',
                    'height' => $chartHeight,
                    'toolbar' => ['show' => false],
                    'animations' => [
                        'enabled' => count($currentLabels) < 20, // Disable animations for very large datasets
                    ],
                ],
                'series' => [
                    [
                        'name' => 'Quantité',
                        'data' => $currentSeries // Use current page series
                    ]
                ],
                'plotOptions' => [
                    'bar' => [
                        'horizontal' => true,
                        'distributed' => true,
                        'barHeight' => '80%',
                        'dataLabels' => [
                            'position' => 'top'
                        ]
                    ]
                ],
                'dataLabels' => [
                    'enabled' => count($currentLabels) <= $limit, // Show labels only if not exceeding limit (or always? user pref?)
                    'formatter' => 'function(val) { return val; }',
                    'offsetX' => 30
                ],
                'xaxis' => [
                    'categories' => $currentLabels, // Use current page labels
                ],
                'yaxis' => [
                    'labels' => [
                        'maxWidth' => 150
                    ]
                ],
                'tooltip' => [
                    'enabled' => true,
                    'shared' => false,
                ],
                'grid' => [
                    'show' => true,
                    'padding' => [
                        'left' => 0,
                        'right' => 0
                    ]
                ],
                // Add pagination data for the frontend
                'pagination' => [
                    'enabled' => $paginationEnabled,
                    'total' => $total,
                    'limit' => $limit,
                    'all_data' => $combinedData // Send combined label/value pairs
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error creating delivered products chart config: ' . $e->getMessage());
            return $this->getEmptyChartConfig('bar', 'Produits livrés');
        }
    }
    
    /**
     * Get user distribution chart configuration (Par Directions)
     * 
     * @param array $data The chart data
     * @return array Chart configuration for ApexCharts
     */
    public function getUserDistributionChartConfig(array $data): array
    {
        try {
            // Check for empty data
            if (empty($data['labels']) || 
                (isset($data['labels'][0]) && in_array($data['labels'][0], ['Aucun utilisateur', 'Aucune direction']))) {
                return $this->getEmptyChartConfig('bar', 'Répartition des utilisateurs');
            }
            
            // Default values
            $labels = $data['labels'] ?? ['Aucune direction'];
            $series = [];
            
            // Parse series data
            if (isset($data['series'])) {
                if (isset($data['series'][0]) && is_array($data['series'][0])) {
                    $series = array_map('intval', $data['series'][0]);
                } else if (is_array($data['series'])) {
                    $series = array_map('intval', $data['series']);
                }
            }
            
            // Handle large datasets - limit to top 10 by default and add pagination
            $allData = [];
            $limit = 10; // Default limit
            
            if (isset($data['all_data']) && is_array($data['all_data']) && count($data['all_data']) > 0) {
                $allData = $data['all_data'];
                
                // Sort by delivered_orders (descending)
                usort($allData, function($a, $b) {
                    return $b['delivered_orders'] - $a['delivered_orders'];
                });
                
                // Take top N items
                $limitedData = array_slice($allData, 0, $limit);
                
                // Extract labels and series
                $labels = array_map(function($item) {
                    return $item['administration'] ?? $item['name'] ?? 'Non spécifié';
                }, $limitedData);
                
                $series = array_map(function($item) {
                    return (int) ($item['delivered_orders'] ?? 0);
                }, $limitedData);
            }
            
            // Dynamic height based on number of categories
            $height = max(min(count($labels) * 40, 500), 320);
            
            return [
                'chart' => [
                    'type' => 'bar',
                    'height' => $height,
                    'toolbar' => ['show' => false],
                    'animations' => [
                        'enabled' => count($labels) < 20,
                    ],
                ],
                'series' => [
                    [
                        'name' => 'Commandes livrées',
                        'data' => $series
                    ]
                ],
                'plotOptions' => [
                    'bar' => [
                        'horizontal' => false,
                        'columnWidth' => '55%',
                        'distributed' => true
                    ]
                ],
                'dataLabels' => [
                    'enabled' => count($labels) < 15,
                    'style' => [
                        'fontSize' => '12px',
                        'colors' => ['#fff']
                    ]
                ],
                'legend' => [
                    'show' => false
                ],
                'xaxis' => [
                    'categories' => $labels,
                    'labels' => [
                        'rotate' => -45,
                        'style' => [
                            'fontSize' => '12px'
                        ]
                    ]
                ],
                'yaxis' => [
                    'title' => [
                        'text' => 'Commandes livrées'
                    ]
                ],
                'tooltip' => [
                    'enabled' => true,
                    'y' => [
                        'formatter' => 'function(value) { return value + " commandes"; }'
                    ]
                ],
                // Add pagination support for large datasets
                'pagination' => [
                    'enabled' => count($allData) > $limit,
                    'total' => count($allData),
                    'limit' => $limit,
                    'all_data' => $allData
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error creating user distribution chart config: ' . $e->getMessage());
            return $this->getEmptyChartConfig('bar', 'Répartition des utilisateurs');
        }
    }
    
    /**
     * Generate empty chart configuration with "No data" message
     * 
     * @param string $type Chart type
     * @param string $title Chart title
     * @return array Empty chart configuration
     */
    private function getEmptyChartConfig(string $type, string $title): array
    {
        return [
            'chart' => [
                'type' => $type,
                'height' => 320,
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
            ],
            'series' => $type === 'pie' ? [0, 0, 0] : [['data' => [0]]],
            'labels' => $type === 'pie' ? ['Aucune donnée'] : null,
            'xaxis' => $type !== 'pie' ? ['categories' => ['Aucune donnée']] : null,
            'title' => [
                'text' => $title,
                'align' => 'center'
            ],
            'noData' => [
                'text' => 'Aucune donnée disponible',
                'align' => 'center',
                'verticalAlign' => 'middle',
                'style' => [
                    'color' => '#6c757d',
                    'fontSize' => '16px',
                    'fontFamily' => 'Helvetica, Arial, sans-serif'
                ]
            ]
        ];
    }
    
    /**
     * Generate default dates for the last 7 days
     * 
     * @return array Array of formatted dates
     */
    private function generateDefaultDates(): array
    {
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = new \DateTime();
            $date->modify("-$i days");
            $dates[] = $date->format('d/m');
        }
        return $dates;
    }
} 