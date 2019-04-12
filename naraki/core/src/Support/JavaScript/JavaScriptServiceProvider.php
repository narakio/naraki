<?php namespace Naraki\Core\JavaScript;

use Naraki\Core\JavaScript\Transformers\Transformer;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class JavaScriptServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('JavaScript', function ($app) {
            return new Transformer(
                new LaravelViewBinder(
                    $app['events'],
                    ['partials.javascript_footer']
                ),
                'config'
            );
        });
    }

    /**
     * Publish the plugin configuration.
     */
    public function boot()
    {
        AliasLoader::getInstance()->alias(
            'JavaScript',
            'App\Facades\JavaScript'
        );
    }

}