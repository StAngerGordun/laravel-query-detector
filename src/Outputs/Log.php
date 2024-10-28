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
            $this->log('Detected N+1 Query', $detectedQuery);
        }
    }

    private function log(string $message, array $context = [])
    {
        LaravelLog::channel(config('querydetector.log_channel'))->info($message, $context);
    }
}
