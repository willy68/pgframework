<?php

namespace Framework\Invoker\ParameterResolver;

use ActiveRecord\RecordNotFound;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionFunctionAbstract;
use Invoker\ParameterResolver\ParameterResolver;

class ActiveRecordConverter implements ParameterResolver
{
    /**
     * Nom du paramètre de la methode à injecter
     *
     * @var string
     */
    private $methodParam;

    /**
     * Nom de l'ID comme paramètre, peut-être null
     *
     * @var string
     */
    private $id;

    /**
     * Nom du Slug comme paramètre, peut-être null
     *
     * @var string
     */
    private $slug;

    /**
     * Other field to find Record
     *
     * @var array
     */
    private $findBy;

    public function __construct(string $methodParam, array $findBy)
    {
        $this->methodParam = $methodParam;
        if (isset($findBy['id'])) {
            $this->id = $findBy['id'];
        }
        elseif (isset($findBy['slug'])) {
            $this->slug = $findBy['slug'];
        }
        else {
            $this->findBy = $findBy;
        }
        
    }

    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters, 
        array $resolvedParameters
    ): array
    {

        if (is_null($this->id) && is_null($this->slug)) {
            return $resolvedParameters;
        }

        /** @var \ReflectionParameter[] $reflectionParameters */
        $reflectionParameters = $reflection->getParameters();
        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $reflectionParameters = array_diff_key($reflectionParameters, $resolvedParameters);
        }

        foreach($providedParameters as $key => $parameter) {

            if (is_int($key)) {
                continue;
            }

            $findBy = $this->id ?: $this->slug;

            if ($key === $findBy) {
                /** @var ReflectionParameter[] $reflectionParameters */
                foreach($reflectionParameters as $index => $reflectionParameter) {
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
                            if ($this->id) {
                                $obj = $class::find((int) $parameter);
                            }
                            elseif ($this->slug) {
                                $method = "find_by_slug";
                                $obj = $class::$method($parameter);
                                if (!$obj) {
                                    throw new RecordNotFound("Couldn't find $class with slug=$this->slug");
                                }
                            }
                            // todo Other findBy method
                            
                            $resolvedParameters[$index] = $obj;
                        }
                    }
                }
            }
        }
        return $resolvedParameters;
    }
}
