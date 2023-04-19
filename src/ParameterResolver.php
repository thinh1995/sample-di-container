<?php

namespace Lucifer\IoC;

use Lucifer\IoC\Exceptions\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

class ParameterResolver
{
    /**
     * @param ContainerInterface $container
     * @param string $namespace
     * @param array $parameters
     * @param array $args
     */
    public function __construct(
        protected ContainerInterface $container,
        public string $namespace,
        public array $parameters,
        public array $args = []
    ) {}

    /**
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getArguments(): array
    {
        // loop through the parameters
        return array_map(
            function (ReflectionParameter $param) {
                $name = $param->getName();
                $type = $param->getType();

                // if an additional arg that was passed in return that value
                if (array_key_exists($name, $this->args)) {
                    return $this->args[$name];
                }

                if (! $param->hasType()) {
                    throw new ContainerException(
                        'Failed to resolve class "' . $this->namespace . '" because param "' . $name . '" is missing a type hint'
                    );
                }

                if ($type instanceof ReflectionNamedType && $type->isBuiltin() && $param->isDefaultValueAvailable()) {
                    return $param->getDefaultValue();
                }

                if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                    return $this->getClassInstance($type);
                }

                throw new ContainerException(
                    'Failed to resolve class "' . $this->namespace . '" because invalid param "' . $name . '"'
                );
            },
            $this->parameters
        );
    }

    /**
     * @param string $namespace
     * @return object
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function getClassInstance(string $namespace): object
    {
        return (new ClassResolver($this->container, $namespace))->getInstance();
    }
}
