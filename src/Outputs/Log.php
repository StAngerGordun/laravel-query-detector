<?php

namespace BeyondCode\QueryDetector\Outputs;

use Illuminate\Support\Facades\Log as LaravelLog;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class Log implements Output
{
    public function boot()
    {
        //
    }

    public function output(Collection $detectedQueries, Response $response)
    {
        foreach ($detectedQueries as $detectedQuery) {
            $sources = [];
            foreach ($detectedQuery['sources'] as $source) {
                $sources[] = '#'.$source->index.' '.$source->name.':'.$source->line . PHP_EOL;
            }
            $detectedQuery['sources'] = $sources;
            $this->log($detectedQuery);
        }
    }

    private function log(array $context = [])
    {
        LaravelLog::channel(config('querydetector.log_channel'))->warning('Detected N+1 Query', $context);
    }
}
