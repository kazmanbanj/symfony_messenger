<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    /**
     * @param RoutingConfigurator|\Symfony\Component\Routing\RouteCollectionBuilder $routes
     */
    protected function configureRoutes($routes): void
    {
        $confDir = $this->getProjectDir().'/config';

        // Support both RoutingConfigurator (Symfony 5.1+) and RouteCollectionBuilder (older)
        if ($routes instanceof RoutingConfigurator) {
            $routes->import($confDir.'/routes.yaml');
            $routes->import($confDir.'/routes/{*.yaml,*.xml,*.php}', 'glob');
            $routes->import($confDir.'/routes/'.$this->environment.'/{*.yaml,*.xml,*.php}', 'glob');
            return;
        }

        // For older Symfony versions using RouteCollectionBuilder
        $routes->import($confDir.'/routes.yaml');
        $routes->import($confDir.'/routes/', '/', 'glob');
        $routes->import($confDir.'/routes/'.$this->environment.'/', '/', 'glob');
    }
}
