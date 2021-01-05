<?php

namespace Framework\Invoker\ParameterResolver;

use ReflectionFunctionAbstract;
use Invoker\ParameterResolver\ParameterResolver;

class ActiveRecordResolver implements ParameterResolver
{
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {
        /** @var \ReflectionParameter[] $reflectionParameters */
        $reflectionParameters = $reflection->getParameters();

        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $providedParameters = array_diff_key($providedParameters, $resolvedParameters);
        }

        foreach($providedParameters as $key => $parameter) {

            if (is_int($key)) {
                continue;
            }

            if ($key === 'id') {
                /** @var \ReflectionParameter $reflectionParameter */
                foreach($reflectionParameters as $index => $reflectionParameter) {
                    $class = $reflectionParameter->getType();

                    if ($class instanceof \ReflectionNamedType) {
                        $class = $class->getName();

                        if (class_exists($class) && in_array(\ActiveRecord\Model::class, class_parents($class))) {
                            $obj = $class::find($parameter);
                            $resolvedParameters[$index] = $obj;
                        }
                    }

                }
            }
        }

        return $resolvedParameters;
    }
}
