<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Routing\Registrar as Router;
use Thapp\JitImage\Framework\Common\ProviderHelperTrait;

/**
 * @class JitImageServiceProvider
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class JitImageServiceProvider extends ServiceProvider
{
    use ProviderHelperTrait;

    const VERSION = '1.0.0-dev';

    /**
     * defer
     *
     * @var boolean
     */
    protected $defer = false;

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->prepareConfig();

        $this->app->singleton(
            'Thapp\JitImage\Resolver\ImageResolverInterface',
            'Thapp\JitImage\Resolver\ImageResolver'
        );

        $useImagine = $this->app['config']->get('jmg.imagine', false);

        $procClass = 'imagine' === $useImagine ?
            'Thapp\JitImage\Imagine\Processor' :
            'Thapp\JitImage\Image\Processor';

        $this->app->singleton('Thapp\JitImage\ProcessorInterface', $procClass);

        $this->app->when('Thapp\JitImage\Image\Processor')
            ->needs('Thapp\Image\Driver\SourceInterface')
            ->give($this->getSourceClass($this->app['config']->get('jmg.driver', 'imagick')));

        $this->app->when('Thapp\JitImage\Imagine\Processor')
            ->needs('Imagine\Image\ImagineInterface')
            ->give($this->getImagineClass($this->app['config']->get('jmg.driver', 'imagick')));

        $this->app->singleton(
            'Thapp\JitImage\Resolver\FilterResolverInterface',
            'Thapp\JitImage\Resolver\FilterResolver'
        );

        $this->app->singleton(
            $loader = 'Thapp\JitImage\Resolver\LoaderResolverInterface',
            'Thapp\JitImage\Framework\Laravel\Resolver\LazyLoaderResolver'
            //'Thapp\JitImage\Resolver\LoaderResolver'
        );

        $this->app->singleton(
            'Thapp\JitImage\Resolver\CacheResolverInterface',
            'Thapp\JitImage\Framework\Laravel\Resolver\LazyCacheResolver'
            //'Thapp\JitImage\Resolver\CacheResolver'
        );

        $this->app->singleton(
            'Thapp\JitImage\Http\UrlBuilderInterface',
            'Thapp\JitImage\Http\UrlBuilder'
        );

        if ($this->app['config']->get('jmg.secure', false)) {
            $this->app->singleton('Thapp\JitImage\Http\HttpSignerInterface', function ($app) {
                return new \Thapp\JitImage\Http\UrlSigner(
                    $app['config']->get('jmg.token_secret'),
                    $app['config']->get('jmg.token_key', 'token')
                );
            });
        }

        $this->app->singleton('Thapp\JitImage\Validator\ValidatorInterface', function ($app) {
            return new \Thapp\JitImage\Validator\ModeConstraints($app['config']['jmg']['mode_constraints']);
        });

        $this->app->singleton('Thapp\JitImage\Resolver\RecipeResolverInterface', function ($app) {
            return new \Thapp\JitImage\Resolver\RecipeResolver($app['config']['jmg']['recipes']);
        });

        $this->app->singleton('Thapp\JitImage\Resolver\PathResolverInterface', function ($app) {
            return new \Thapp\JitImage\Resolver\PathResolver($app['config']['jmg']['paths']);
        });

        $this->app->resolving($class = $this->getControllerClass(), function ($ctrl, $app) {
            $ctrl->setRouter($app['router']);
            $ctrl->setRequest($app['request']);
            $ctrl->setRecieps($app->make('Thapp\JitImage\Resolver\RecipeResolverInterface'));
            if ($app['config']->get('jmg.secure', false)) {
                $ctrl->setUrlSigner($app->make('Thapp\JitImage\Http\HttpSignerInterface'));
            }
        });

        // fire an event in case the processor gets instantiated
        $this->app->resolving('Thapp\JitImage\ProcessorInterface', function ($proc, $app) {
            $app['events']->fire('jmg.processor.boot');
        });

        // set options on imagine processor
        $this->app->resolving('Thapp\JitImage\Imagine\Processor', function ($proc, $app) {
            $proc->setOptions($app['config']->get('jmg.imagine'), []);
        });

        // set options on image processor
        $this->app->resolving('Thapp\JitImage\Image\Processor', function ($proc, $app) {
            $proc->setOptions($app['config']->get('jmg.image'), []);
        });

        $this->app->singleton('jmg', function ($app) {
            return new \Thapp\JitImage\View\Jmg(
                $app->make('Thapp\JitImage\Resolver\ImageResolverInterface'),
                $app->make('Thapp\JitImage\Resolver\RecipeResolverInterface'),
                $app->make('Thapp\JitImage\Http\UrlBuilderInterface'),
                '',
                $app['config']->get('jmg.cache_path_prefix', 'cached')
            );
        });

        $this->app->alias('Thapp\JitImage\Resolver\LoaderResolverInterface', 'jmg.loaders');
        $this->app->alias('Thapp\JitImage\Resolver\FilterResolverInterface', 'jmg.filters');
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {

        $this->registerRoutes();
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['jmg', 'jmg.loaders', 'jmg.caches', 'jmg.filters'];
    }

    /**
     * registerRoutes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (file_exists(storage_path().'/framework/routes.php')) {
            return;
        }

        $router = $this->app['router'];

        if (!$this->app['config']['jmg']['disable_dynamic_processing']) {
            $this->registerDynamicRoutes($router);
        }

        $this->registerRecipes($router);
        $this->registerCached($router);
    }

    /**
     * registerCached
     *
     * @param mixed $router
     *
     * @return void
     */
    private function registerCached($router)
    {
        $ctrl   = $this->getControllerClass().'@getCached';
        $caches = $this->app['config']['jmg.caches'];
        $prefix = $this->app['config']['jmg.cache_path_prefix'];

        foreach ($this->app['config']->get('jmg.paths', []) as $alias => $path) {
            if (isset($caches[$alias]) && false === $caches[$alias]) {
                continue;
            }

            $this->registerCachedController($router, $ctrl, $alias, $prefix);
        }
    }

    /**
     * registerRecipes
     *
     * @param mixed $router
     *
     * @return void
     */
    protected function registerRecipes($router)
    {
        $config = $this->app['config']['jmg'];
        $ctrl = $this->getControllerClass().'@getResource';

        foreach ($config['recipes'] as $recipe => $data) {
            if (isset($config['paths'][$data[0]])) {
                $this->registerRecipesController($router, $ctrl, $recipe);
            }
        }
    }

    protected function registerDynamicRoutes($router)
    {
        list (, $params, $source, $filter) = $this->getPathRegexp();

        $pattern = '/{params}/{source}/{filter?}';
        $controller = $this->getControllerClass().'@getImage';

        foreach ($this->app['config']['jmg']['paths'] as $path => $filePath) {
            $this->registerDynamicController($router, $controller, $path, $pattern, $params, $source, $filter);
        }
    }

    /**
     * registerCaches
     *
     * @return void
     */
    protected function registerCaches()
    {
        $config = $this->app['config']['jmg'];

        foreach ($config['paths'] as $prefix => $path) {
        }
    }

    protected function getControllerClass()
    {
        return '\Thapp\JitImage\Framework\Laravel\Http\Controller';
    }

    /**
     * prepareConfig
     *
     * @return void
     */
    protected function prepareConfig()
    {
        if (!file_exists($path = storage_path().'/jmg/config/config.php')) {
        }

        $config = array_merge($this->getDefaultConfig(), $this->app['config']->get('jmg', []));
        $this->app['config']->set('jmg', $config);
    }

    /**
     * getDefaultConfig
     *
     *
     * @return void
     */
    protected function getDefaultConfig()
    {
        return include __DIR__.'/resource/config.php';
    }

    /**
     * registerDynamicController
     *
     * @param Router $router
     * @param mixed $path
     * @param string $pattern
     * @param string $params
     * @param string $source
     * @param string $filter
     *
     * @return void
     */
    private function registerDynamicController(Router $router, $ctrl, $path, $pattern, $params, $source, $filter)
    {
        $router->get(rtrim($path, '/') . $pattern, $ctrl)
            ->where('params', $params)
            ->where('source', $source)
            ->where('filter', $filter);
    }

    /**
     * registerRecipesController
     *
     * @param mixed $router
     * @param mixed $ctrl
     * @param mixed $recipe
     *
     * @return void
     */
    private function registerRecipesController(Router $router, $ctrl, $recipe)
    {
        $router->get(rtrim($recipe, '/').'/{source}', $ctrl)
            ->where('source', '(.*)');
    }

    /**
     * registerCachedController
     *
     * @param Router $router
     * @param mixed $path
     * @param mixed $suffix
     *
     * @return void
     */
    private function registerCachedController(Router $router, $ctrl, $path, $suffix)
    {
        //$r = $router->get(rtrim($path, '/') . '/{suffix}/{id}', $ctrl)
        $router->get('/'.trim($suffix, '/').'/{path}/{id}', $ctrl)
            ->where('id', '(.*\/){1}.*')
            ->where('suffix', $suffix)
            ->defaults('path', $path);
    }
}
