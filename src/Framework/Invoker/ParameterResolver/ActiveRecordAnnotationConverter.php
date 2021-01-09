<?php

namespace Framework\Invoker\ParameterResolver;

use ActiveRecord\RecordNotFound;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionFunctionAbstract;
use Invoker\ParameterResolver\ParameterResolver;

class ActiveRecordAnnotationConverter implements ParameterResolver
{
    /**
     * Nom du paramètre de la methode à injecter
     *
     * @var string
     */
    private $methodParam;

    /**
     * Other field to find Record
     *
     * @var array
     */
    private $findBy;

    public function __construct(string $methodParam, array $findBy)
    {
        $this->methodParam = $methodParam;
        $this->findBy = $findBy;
    }

    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {

        if (empty($this->findBy)) {
            return $resolvedParameters;
        }

        /** @var \ReflectionParameter[] $reflectionParameters */
        $reflectionParameters = $reflection->getParameters();
        // Skip parameters already resolved
        if (!empty($resolvedParameters)) {
            $reflectionParameters = array_diff_key($reflectionParameters, $resolvedParameters);
        }

        foreach ($providedParameters as $key => $parameter) {
            if (is_int($key)) {
                continue;
            }

            $findByKey = array_key_first($this->findBy);

            if ($key === $this->findBy[$findByKey]) {
                /** @var ReflectionParameter[] $reflectionParameters */
                foreach ($reflectionParameters as $index => $reflectionParameter) {
                    $name = $reflectionParameter->getName();

                    if ($name === $this->methodParam) {
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
                            if ($findByKey === 'id') {
                                $obj = $class::find((int) $parameter);
                            } else {
                                $method = "find_by_" . $findByKey;
                                $obj = $class::$method($parameter);
                                if (!$obj) {
                                    throw new RecordNotFound("Couldn't find $class with slug=$this->slug");
                                }
                            }
                            $resolvedParameters[$index] = $obj;
                        }
                    }
                }
            }
        }
        return $resolvedParameters;
    }
}
