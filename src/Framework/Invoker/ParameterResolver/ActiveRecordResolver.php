<?php

namespace Framework\Invoker\ParameterResolver;

use ReflectionNamedType;
use ReflectionParameter;
use ReflectionFunctionAbstract;
use Invoker\ParameterResolver\ParameterResolver;

/**
 * Subtitue un paramètre $id d'une route
 * par le model ActiveRecord attendu par la fonction
 */
class ActiveRecordResolver implements ParameterResolver
{

    /**
     * nom du champ id par défaut id
     *
     * @var string
     */
    private $id = 'id';

    /**
     * Alias pour le champ $id par défaut null
     *
     * @var string|null Si non null sera utilsé à la place de $id
     */
    private $alias;

    /**
     * Constructor
     *
     * @param string $key
     * @param string|null $alias
     */
    public function __construct(?string $alias = null)
    {
        $this->alias = $alias;
    }

    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {
        /** @var \ReflectionParameter[] $reflectionParameters */
        $reflectionParameters = $reflection->getParameters();

        foreach($providedParameters as $key => $parameter) {

            if (is_int($key)) {
                continue;
            }

            $id = $this->alias ?: $this->id;

            if ($key === $id) {
                foreach($reflectionParameters as $index => $reflectionParameter) {
                    /** @var ReflectionParameter $reflectionParameter */
                    $parameterType = $reflectionParameter->getType();

                    if (!$parameterType) {
                        // No type
                        continue;
                    }
                    /** @var ReflectionNamedType $parameterType */
                    if ($parameterType->isBuiltin()) {
                        // Primitive types are not supported
                        continue;
                    }
                    if (!$parameterType instanceof ReflectionNamedType) {
                        // Union types are not supported
                        continue;
                    }

                    $class = $parameterType->getName();

                    if (class_exists($class) && in_array(\ActiveRecord\Model::class, class_parents($class))) {
                        $obj = $class::find($parameter);
                        $resolvedParameters[$index] = $obj;
                    }
                }
            }
        }
        return $resolvedParameters;
    }
}
