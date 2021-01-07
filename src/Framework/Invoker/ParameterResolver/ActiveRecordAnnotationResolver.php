<?php

namespace Framework\Invoker\ParameterResolver;

use ReflectionMethod;
use ReflectionParameter;
use PhpDocReader\PhpDocReader;
use Invoker\ParameterResolver\ParameterResolver;
use ReflectionFunctionAbstract;
use Doctrine\Common\Annotations\AnnotationReader;
use Framework\Invoker\Exception\InvalidAnnotation;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Framework\Invoker\Annotation\ParameterConverter;

class ActiveRecordAnnotationResolver implements ParameterResolver
{

    private $annotationReader;

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
        /** @var ReflectionParameter[] $reflectionParameters */
        $reflectionParameters = $reflection->getParameters();
        $annotation = $this->getMethodAnnotation($reflection);

        dd($annotation);
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
     * @return \Framework\Invoker\Annotation\ParameterConverter|null
     */
    private function getMethodAnnotation(ReflectionMethod $method): ?ParameterConverter
    {
        // Look for @ParameterConverter annotation
        try {
            $annotation = $this->getAnnotationReader()
                            ->getMethodAnnotation(
                                $method,
                                'Framework\Invoker\Annotation\ParameterConverter'
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
}
