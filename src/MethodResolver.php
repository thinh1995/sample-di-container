<?php

namespace Lucifer\IoC;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use ReflectionMethod;

class MethodResolver
{
	/**
	 * @param ContainerInterface $container
	 * @param object $instance
	 * @param string $method
	 * @param array $args
	 */
	public function __construct(
		protected ContainerInterface $container,
		protected object $instance,
		protected string $method,
		protected array $args = []
	) {}

    /**
     * @return mixed
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
	public function getValue(): mixed
    {
		// get the class method reflection class
		$method = new ReflectionMethod($this->instance, $this->method);

		// find and resolve the method arguments
		$argumentResolver = new ParameterResolver(
			$this->container,
            get_class($this->instance),
			$method->getParameters(),
			$this->args
		);

		// call the method with the injected arguments
		return $method->invokeArgs(
			$this->instance,
			$argumentResolver->getArguments()
		);
	}
}
