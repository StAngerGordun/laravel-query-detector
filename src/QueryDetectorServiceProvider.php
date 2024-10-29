<?php

namespace BeyondCode\QueryDetector;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class QueryDetectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('querydetector.php'),
            ], 'query-detector-config');
        }

        $this->registerMiddleware(QueryDetectorMiddleware::class);

        Queue::before(static function (JobProcessing $event) {
            $jobData = $event->job->payload();
            Log::withContext(['job_name' => $jobData['displayName'] ?? $event->job::class]);
            /** @var QueryDetector $detector */
            $detector = app()->make(QueryDetector::class);
            if ($detector->isEnabled()) {
                $detector->boot();
            }
        });

        Queue::after(static function (JobProcessed $event) {
            /** @var QueryDetector $detector */
            $detector = app()->make(QueryDetector::class);
            if ($detector->isEnabled()) {
                $detector->output(request(), response()->json());
            }
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(QueryDetector::class);

        $this->app->alias(QueryDetector::class, 'querydetector');

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'querydetector');
    }

    /**
     * Register the middleware
     *
     * @param  string $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $kernel = $this->app[Kernel::class];
        $kernel->pushMiddleware($middleware);
    }
}
