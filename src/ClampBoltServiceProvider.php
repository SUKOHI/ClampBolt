<?php namespace Sukohi\ClampBolt;

use Illuminate\Support\ServiceProvider;
use Sukohi\ClampBolt\Commands\AttachmentClearCommand;

class ClampBoltServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var  bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if($this->app->runningInConsole()) {
            $this->commands([
                AttachmentClearCommand::class
            ]);
        }
		$this->publishes([
			__DIR__.'/migrations/' => database_path('migrations')
		], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['clamp-bolt'];
    }

}