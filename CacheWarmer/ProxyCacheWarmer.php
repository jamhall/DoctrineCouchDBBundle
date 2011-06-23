<?php

namespace Doctrine\Bundle\CouchDBBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProxyCacheWarmer implements CacheWarmerInterface
{
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * This cache warmer is not optional, without proxies fatal error occurs!
     *
     * @return false
     */
    public function isOptional()
    {
        return false;
    }

    public function warmUp($cacheDir)
    {
        foreach ($this->container->getParameter('doctrine_couchdb.document_managers') as $dmName) {
            $dm = $this->container->get($dmName);
            
            // we need the directory no matter the proxy cache generation strategy
            if (!file_exists($proxyCacheDir = $dm->getConfiguration()->getProxyDir())) {
                if (false === @mkdir($proxyCacheDir, 0777, true)) {
                    throw new \RuntimeException(sprintf('Unable to create the Doctrine CouchDB Proxy directory "%s".', dirname($proxyCacheDir)));
                }
            } elseif (!is_writable($proxyCacheDir)) {
                throw new \RuntimeException(sprintf('The Doctrine CouchDB Proxy directory "%s" is not writeable for the current system user.', $proxyCacheDir));
            }

            // if proxies are autogenerated we don't need to generate them in the cache warmer
            /*if ($dm->getConfiguration()->getAutoGenerateProxyClasses()) {
                continue;
            }

            $classes = $dm->getMetadataFactory()->getAllMetadata();
            $dm->getProxyFactory()->generateProxyClasses($classes);*/
        }
    }
}
