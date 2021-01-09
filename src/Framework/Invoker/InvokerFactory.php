<?php

namespace Framework\Invoker;

use Invoker\Invoker;
use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Invoker\ParameterResolver\ResolverChain;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Framework\Invoker\ParameterResolver\ActiveRecordResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;

class InvokerFactory
{

    /**
     * Create Invoker
     *
     * @param \Psr\Container\ContainerInterface $c
     * @return \Invoker\InvokerInterface
     */
    public function __invoke(ContainerInterface $container): InvokerInterface
    {
        /*$writeProxiesToFile = ($container->get('env') === 'production');

        $proxyFactory = new ProxyFactory(
            $writeProxiesToFile,
            App::PROXY_DIRECTORY
        );

        $definitionResolver = new ResolverDispatcher($container, $proxyFactory);
        */
        $parameterResolver = new ResolverChain([
            //new DefinitionParameterResolver($definitionResolver),
            // Must before TypeHintContainerResolver
            new ActiveRecordResolver(),
            new NumericArrayResolver(),
            new AssociativeArrayResolver(),
            new DefaultValueResolver(),
            new TypeHintContainerResolver($container)
        ]);

        return new Invoker($parameterResolver, $container);
    }
}
