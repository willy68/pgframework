<?php

namespace Framework\Invoker\ParameterResolver;

use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use PhpDocReader\PhpDocReader;
use ReflectionFunctionAbstract;
use Invoker\ParameterResolver\ParameterResolver;
use Doctrine\Common\Annotations\AnnotationReader;
use Framework\Invoker\Exception\InvalidAnnotation;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Framework\Invoker\Annotation\ParameterConverter;

class ActiveRecordAnnotationsResolver implements ParameterResolver
{

    /**
     * Reader
     *
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * Reflection param to convert
     *
     * @var ParameterResolver[]
     */
    protected $converters;

    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {

        $annotation = $this->getMethodAnnotation($reflection);
        if (empty($annotation)) {
            return $resolvedParameters;
        }

        $this->parseAnnotation($annotation, $reflection);

        /** @var ReflectionParameter[] $reflectionParameters */
        $reflectionParameters = $reflection->getParameters();
        // Skip parameters already resolved
        if (!empty($resolvedParameters)) {
            $reflectionParameters = array_diff_key($reflectionParameters, $resolvedParameters);
        }

        foreach($this->converters as $converter) {
            $resolvedParameters = $converter->getParameters($reflection, $providedParameters, $resolvedParameters);

            $diff = array_diff_key($reflectionParameters, $resolvedParameters);
            if (empty($diff)) {
                // Stop traversing: all parameters are resolved
                return $resolvedParameters;
            }
        }

        return $resolvedParameters;
    }

    /**
     * @return AnnotationReader The annotation reader
     */
    public function getAnnotationReader(): AnnotationReader
    {
        if ($this->annotationReader === null) {
            AnnotationRegistry::registerLoader('class_exists');
            $this->annotationReader = new AnnotationReader();
        }

        return $this->annotationReader;
    }

    /**
     * @return PhpDocReader
     */
    private function getPhpDocReader(): PhpDocReader
    {
        if ($this->phpDocReader === null) {
            $this->phpDocReader = new PhpDocReader($this->ignorePhpDocErrors);
        }

        return $this->phpDocReader;
    }

    /**
     * Get annotation method
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    private function getMethodAnnotation(ReflectionMethod $method): array
    {
        // Look for @ParameterConverter annotation
        try {
            $annotation = $this->getAnnotationReader()
                            ->getMethodAnnotations(
                                $method,
                                ParameterConverter::class
                            );
        } catch (InvalidAnnotation $e) {
            throw new InvalidAnnotation(sprintf(
                '@ParameterConverter annotation on %s::%s is malformed. %s',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $e->getMessage()
            ), 0, $e);
        }
        return $annotation;
    }

    /**
     * Parse le tableau d'annotations
     *
     * @param array $annotations
     * @param \ReflectionMethod $method
     * @return void
     */
	protected function parseAnnotation(array $annotations, ReflectionMethod $method): void
	{
        foreach($annotations as $annotation) {
            $annotationParams = $annotation->getParameters();
            if (!isset($annotationParams["value"]) || !isset($annotationParams["options"])) {
                throw new InvalidAnnotation(sprintf(
                    '@ParameterConverter annotation on %s::%s is malformed.',
                    $method->getDeclaringClass()->getName(),
                    $method->getName()
                ));
            }
            $this->converters[] = new ActiveRecordConverter($annotationParams['value'], $annotationParams['options']);
        }
	}
}
