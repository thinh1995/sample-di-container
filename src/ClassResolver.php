<?php

namespace Lucifer\IoC;

use Lucifer\IoC\Exceptions\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;

class ClassResolver
{
    /**
     * @param ContainerInterface $container
     * @param string $namespace
     * @param array $args
     */
    public function __construct(
        protected ContainerInterface $container,
        protected string $namespace,
        protected array $args = []
    ) {}

    /**
     * @return mixed
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getInstance(): mixed
    {
        // check for container entry
        if ($this->container->has($this->namespace)) {
            $binding = $this->container->get($this->namespace);

            // return if there is a container instance / singleton
            if (is_callable($binding)) {
                return $binding();
            }

            if (is_object($binding)) {
                return $binding;
            }

            // sets the namespace to the bound container namespace
            $this->namespace = $binding;
        }

        // create a reflection class
        $refClass = new ReflectionClass($this->namespace);

        if (! $refClass->isInstantiable()) {
            throw new ContainerException('Class "' . $this->namespace . '" is not instantiable');
        }

        // get the constructor
        $constructor = $refClass->getConstructor();

        // check constructor exists and is accessible
        if ($constructor && $constructor->isPublic()) {
            // check constructor has parameters and resolve them
            if (count($constructor->getParameters()) > 0) {
                $argumentResolver = new ParameterResolver(
                    $this->container,
                    $this->namespace,
                    $constructor->getParameters(),
                    $this->args
                );
                // resolve the constructor arguments
                $this->args = $argumentResolver->getArguments();
            }

            // create the new instance with the constructor arguments
            return $refClass->newInstanceArgs($this->args);
        }

        // no arguments so create the new instance without calling the constructor
        return $refClass->newInstanceWithoutConstructor();
    }
}
