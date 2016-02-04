<?php

namespace Performance\LazyCache;

use Silex\Application;
use \InvalidArgumentException;

class Config {

    /** @var  Application */
    protected $app;
    protected $active = true;
    protected $maxAge = 10;
    protected $maxAgeShared = 10;
    protected $cacheableRoutes;
    protected $cacheCompromisingRoutes;

    function __construct(Application $app) {
        $this->app = $app;

        if (!isset($app['lazycache_config.params'])) {
            throw new InvalidArgumentException(__METHOD__ . " failed: config not defined!");
        }
        if (!isset($app['lazycache_config.params']["cacheableRoutes"]) || empty($app['lazycache_config.params']["cacheableRoutes"])) {
            throw new InvalidArgumentException(__METHOD__ . " failed: cacheableRoutes not defined!");
        }
        if (!isset($app['lazycache_config.params']["cacheCompromisingRoutes"]) || empty($app['lazycache_config.params']["cacheCompromisingRoutes"])) {
            throw new InvalidArgumentException(__METHOD__ . " failed: cacheCompromisingRoutes not defined!");
        }
        $this->setCacheableRoutes($app['lazycache_config.params']["cacheableRoutes"]);
        $this->setCacheCompromisingRoutes($app['lazycache_config.params']["cacheCompromisingRoutes"]);

        if (isset($app['lazycache_config.params']["active"])) {
            $this->setActive($app['lazycache_config.params']["active"]);
        }
        if (isset($app['lazycache_config.params']["MaxAge"])) {
            $this->setMaxAge($app['lazycache_config.params']["MaxAge"]);
        }
        if (isset($app['lazycache_config.params']["MaxAgeShared"])) {
            $this->getMaxAgeShared($app['lazycache_config.params']["MaxAgeShared"]);
        }
    }

    public function getMaxAge() {
        return $this->maxAge;
    }

    public function setMaxAge($maxAge) {
        if (!filter_var($maxAge, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)))) {
            throw new InvalidArgumentException(__METHOD__ . " failed: value must be >= 0!");
        }
        $this->maxAge = $maxAge;
    }

    public function getMaxAgeShared() {
        return $this->maxAgeShared;
    }

    public function setMaxAgeShared($maxAgeShared) {
        if (!filter_var($maxAgeShared, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)))) {
            throw new InvalidArgumentException(__METHOD__ . " failed: value must be >= 0!");
        }
        $this->maxAgeShared = $maxAgeShared;
    }

    public function getActive() {
        return $this->active;
    }

    public function setActive($active) {
        $this->active = $active;
    }

    public function getCacheableRoutes() {
        return $this->cacheableRoutes;
    }

    public function getCacheCompromisingRoutes() {
        return $this->cacheCompromisingRoutes;
    }

    public function setCacheableRoutes(array $cacheableRoutes) {
        if (!isset($cacheableRoutes) || empty($cacheableRoutes)) {
            throw new InvalidArgumentException(__METHOD__ . " failed: value must  not be empty!");
        }
        $this->cacheableRoutes = $cacheableRoutes;
    }

    /**
     * Sets routes-compromisers ( entering to one of these routes will compromise all cache information)
     * @param array $cacheCompromisingRoutes Routes names
     */
    public function setCacheCompromisingRoutes(array $cacheCompromisingRoutes) {
        if (!isset($cacheCompromisingRoutes) || empty($cacheCompromisingRoutes)) {
            throw new InvalidArgumentException(__METHOD__ . " failed: value must  not be empty!");
        }
        $this->cacheCompromisingRoutes = $cacheCompromisingRoutes;
//        $values = array();
//        foreach ($cacheCompromisingRoutes as $routeName) {
//            if (strstr($routeName, "|get")) {
//                $values["get"] = str_replace("|get", "", $routeName);
//            } elseif (strstr($routeName, "|post")) {
//                $values["post"] = str_replace("|post", "", $routeName);
//            } else {
//                $values["get"] = str_replace("|get", "", $routeName);
//                $values["post"] = str_replace("|post", "", $routeName);
//            }
//        }
//
//        $this->cacheCompromisingRoutes = $values;
    }

}
