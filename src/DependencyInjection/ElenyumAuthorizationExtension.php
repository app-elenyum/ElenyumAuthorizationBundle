<?php

namespace Elenyum\Authorization\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ElenyumAuthorizationExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        // add to container parameters
        $container->setParameter('elenyum_authorization.config', $config);
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 2). '/config' )
        );

        $loader->load('services.yaml');
    }
}