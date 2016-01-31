<?php

namespace Performance\LazyCache;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LazyCacheProvider implements ServiceProviderInterface {

    public function register(Application $app) {

        $app['lazycache_config'] = $app->share(function () use ($app) {
            return new Config($app);
        });

        $app['lazycache'] = $app->share(function () use ($app) {
            return new LazyCache($app);
        });
    }

    public function boot(Application $app) {
        if ($app['lazycache']->isActive()) {
            $app->before(function (Request $request) use ($app) {
                return $app['lazycache']->before($request);
            });
            $app->after(function (Request $request, Response $response) use ($app) {
                return $app['lazycache']->after($request, $response);
            });
        }
    }

}
