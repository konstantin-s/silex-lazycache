<?php

namespace Performance\LazyCache;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \InvalidArgumentException;
/**
 * @todo Add request type configuration for routers-compromisers (may be by postfixes)
 */
class LazyCache {

    /** @var  Application */
    protected $app;

    /** @var  Config */
    protected $config;

    function __construct(Application $app) {
        $this->app = $app;
        $this->config = $app['lazycache_config'];
    }

    public function before(Request $request) {

        $currentRouteName = $request->attributes->get("_route");
        if (!$this->isRouteCacheable($currentRouteName)) {
            return;
        }

        $mapper = new CacheRecordSQLMapper($this->app["db"]);
//        $mapper->createTable();
        $validCacheRecord = $mapper->findByUriValid($this->getCurrentUri());
        if (!$validCacheRecord) {
            return;
        }

        $response = $this->createResponse($validCacheRecord->getLmt());

        if ($response->isNotModified($request)) {
            return $response;
        }
    }

    public function after(Request $request, Response $response) {
        $now = new \DateTime("now");
        $mapper = new CacheRecordSQLMapper($this->app["db"]);
        $currentRouteName = $request->attributes->get("_route");

        if ($this->isCacheCompromiser($currentRouteName)) {
            /** @todo method check */
            $mapper = new CacheRecordSQLMapper($this->app["db"]);
            $compromisedCount = $mapper->compromiseAll();
            return;
        }

        if ($this->isRouteCacheable($currentRouteName) && $response->isOk()) {

            $cacheRecord = $mapper->findByUri($this->getCurrentUri());
            if (!$cacheRecord) {
//                $this->createCacheRecord();
                $data = [];
                $data["uri"] = $this->getCurrentUri();
                $data["hash"] = md5($response->getContent());
                $data["lmt"] = $now->format("Y-m-d H:i:s");
                $cacheRecord = new CacheRecord($data);
                $mapper->create($cacheRecord);

                $response->setPublic();
                $response->setDate($now);
                $response->setMaxAge($this->config->getMaxAge());
                $response->setSharedMaxAge($this->config->getMaxAgeShared());
                $response->setLastModified($now);
                $response->headers->addCacheControlDirective('must-revalidate', true);
                return $response;
            }
            if ($cacheRecord->getCompromised()) {
                if ($cacheRecord->getHash() === md5($response->getContent())) {
                    $cacheRecord->setCompromised(false);
                    $mapper->update($cacheRecord->getId(), $cacheRecord);
                    $response = $this->createResponse($cacheRecord->getLmt());
                    $response->setNotModified();
                    return $response;
                } else {
                    $cacheRecord->setCompromised(false);
                    $cacheRecord->setHash(md5($response->getContent()));
                    $cacheRecord->setLmt(date("Y-m-d H:i:s"));
                    $mapper->update($cacheRecord->getId(), $cacheRecord);
                    return $response;
                }
            } else {
                $now = new \DateTime("2015-12-12");
                $response->setPublic();
                $response->setDate($now);
                $response->setMaxAge($this->config->getMaxAge());
                $response->setSharedMaxAge($this->config->getMaxAgeShared() + 1);
                $response->setLastModified(new \DateTime($cacheRecord->getLmt()));
                return $response;
            }
        }
    }

    public function createResponse($lMT) {
        $response = new Response();
        $response->setPublic();
        $response->setDate(new \DateTime("now"));
        $response->setMaxAge($this->config->getMaxAge());
        $response->setSharedMaxAge($this->config->getMaxAgeShared());
        $response->setLastModified(new \DateTime($lMT));
        return $response;
    }

    private function getCurrentUri() {
        return $this->app["request"]->server->get("REQUEST_URI");
    }

    public function isActive() {
        return $this->config->getActive();
    }

    private function isRouteCacheable($routeName) {
        return in_array($routeName, $this->config->getCacheableRoutes());
    }

    private function isCacheCompromiser($routeName) {
        return in_array($routeName, $this->config->getCacheCompromisingRoutes());
    }

}
