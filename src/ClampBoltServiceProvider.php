<?php

namespace Sukohi\ClampBolt;

use Illuminate\Support\ServiceProvider;
use Sukohi\ClampBolt\Commands\AttachmentClearCommand;

class ClampBoltServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var  bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Command
        if($this->app->runningInConsole()) {
            $this->commands([
                AttachmentClearCommand::class
            ]);
        }

        // Publish
		$this->publishes([
			__DIR__.'/migrations/' => database_path('migrations')
		], 'migrations');

        // Validator
        $this->app['validator']->extendImplicit('total_attachment', function($attribute, $value, $parameters, $validator) {

            $table = $parameters[0];
            $model_id = intval($parameters[1]);
            $attachment_key = $parameters[2];
            $min = intval($parameters[3]);
            $max = intval($parameters[4]);
            $detaching_count = intval($parameters[5]);
            $adding_count = (is_null($value)) ? 0 : count($value);
            $model_name = '\\App\\' . studly_case(strtolower(str_singular($table)));
            $validator->addReplacer('total_attachment', function($message) use($min, $max) {

                $search = [':min', ':max'];
                $replace = [$min, $max];
                return str_replace($search, $replace, $message);

            });

            if(class_exists($model_name)) {

                $model = with(new $model_name)->find($model_id);
                $attachment_count = 0;

                if(!is_null($model)) {

                    $attachments = $model->getAttachment($attachment_key .'.*');
                    $attachment_count = $attachments->count();

                }

                $total = $attachment_count + $adding_count - $detaching_count;
                return ($total >= $min && $total <= $max);

            }

            return false;

        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('clamp-bolt', function ($app) {
            return null;
        });
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