<?php

namespace App\Services\Charts;

interface ChartInterface
{
    /**
     * Get chart data with option to bypass cache
     * 
     * @param bool $bypassCache
     * @return array
     */
    public function getData(bool $bypassCache = false): array;
    
    /**
     * Get chart's cache key
     * 
     * @return string
     */
    public function getCacheKey(): string;
    
    /**
     * Clear chart's cache
     * 
     * @return void
     */
    public function clearCache(): void;
} 