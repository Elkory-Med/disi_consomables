<?php

namespace App\Services\Charts;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

abstract class BaseChart implements ChartInterface
{
    /**
     * Default cache duration in minutes
     */
    protected $cacheDuration = 30;
    
    /**
     * Chart identifier
     */
    protected $chartId;
    
    /**
     * Whether this chart should be used for Par Directions
     */
    protected $forParDirectionsChart = false;
    
    /**
     * Get chart data with option to bypass cache
     * 
     * @param bool $bypassCache
     * @return array
     */
    public function getData(bool $bypassCache = false): array
    {
        $cacheKey = $this->getCacheKey();
        
        // Clear cache if bypassing
        if ($bypassCache) {
            $this->clearCache();
        }
        
        // Try to get from cache if not bypassing
        if (!$bypassCache && Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            $data['is_cached'] = true;
            Log::info("Using cached data for {$this->chartId}");
            return $data;
        }
        
        try {
            // Get fresh data
            $data = $this->fetchData();
            
            // Add metadata
            $data['is_cached'] = false;
            $data['chart_id'] = $this->chartId;
            $data['for_par_directions_chart'] = $this->forParDirectionsChart;
            $data['generated_at'] = now()->format('Y-m-d H:i:s');
            
            // Store in cache
            Cache::put($cacheKey, $data, now()->addMinutes($this->cacheDuration));
            
            return $data;
        } catch (\Exception $e) {
            Log::error("Error generating chart data for {$this->chartId}: " . $e->getMessage());
            return $this->getErrorData($e->getMessage());
        }
    }
    
    /**
     * Clear chart's cache
     * 
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget($this->getCacheKey());
        Log::info("Cache cleared for {$this->chartId}");
    }
    
    /**
     * Get error data structure
     * 
     * @param string $errorMessage
     * @return array
     */
    protected function getErrorData(string $errorMessage): array
    {
        return [
            'labels' => ['Erreur'],
            'series' => [[0]],
            'chart_id' => $this->chartId,
            'error' => $errorMessage,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Fetch data from data source
     * This method must be implemented by child classes
     * 
     * @return array
     */
    abstract protected function fetchData(): array;
} 