<?php

namespace Wassa\CacheInvalidationBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postUpdate'
        ];
    }
    
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->flushCache($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->flushCache($args);
    }
    
    public function flushCache(LifecycleEventArgs $args)
    {
        $cacheManager = $this->container->get('fos_http_cache.cache_manager');
        $routes = $this->container->getParameter('wassa_cache_invalidation.routes');
        $entity = $args->getObject();

        foreach ($routes as $name => $route) {
            foreach ($route as $classes) {
                foreach ($classes as $class) {
                    if ($entity instanceof $class) {
                        $rep = $cacheManager->invalidateRoute($name, [], ['apikey' => $this->container->getParameter('api_key')])->flush();
                        
                        return;
                    }
                }
            }
        }
    }
}