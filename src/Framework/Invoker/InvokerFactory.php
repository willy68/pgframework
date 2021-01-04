<?php

namespace Framework\Invoker;

use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use DI\Proxy\ProxyFactory;
use Psr\Container\ContainerInterface;
use DI\Invoker\DefinitionParameterResolver;
use Invoker\ParameterResolver\ResolverChain;
use DI\Definition\Resolver\ResolverDispatcher;

class InvokerFactory
{

    /**
     * Create Invoker
     *
     * @param \Psr\Container\ContainerInterface $c
     * @param bool $writeProxyToFile
     * @param string|null $proxyDirectory
     * @return \Invoker\InvokerInterface
     */
    public function __invoke(
        ContainerInterface $container,
        bool $writeProxiesToFile = false,
        string $proxyDirectory = null
    ): InvokerInterface
    {
        $proxyFactory = new ProxyFactory(
            $writeProxiesToFile,
            $proxyDirectory
        );

        $definitionResolver = new ResolverDispatcher($container, $proxyFactory);
        
        $parameterResolver = new ResolverChain([
            new DefinitionParameterResolver($definitionResolver),
            new NumericArrayResolver,
            new AssociativeArrayResolver,
            new DefaultValueResolver,
            new TypeHintContainerResolver($container),
        ]);

        return new Invoker($parameterResolver, $container);
    }
}
