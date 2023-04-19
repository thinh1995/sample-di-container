<?php

namespace Lucifer\IoC;

use Closure;
use Exception;
use Lucifer\IoC\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class Container implements ContainerInterface
{
	private static Container|null $instance = null;

	protected array $bindings = [];

	/**
	 * @return static
	 */
	public static function instance(): static
	{
		if (self::$instance === null) {
			self::$instance = new Container;
		}

		return self::$instance;
	}

    /**
     * @param string $id
     * @param string|Closure $namespace
     * @return $this
     */
	public function bind(string $id, string|Closure $namespace): Container
	{
		$this->bindings[$id] = $namespace;

		return $this;
	}

	/**
	 * @param string $id
	 * @param object $instance
	 * @return $this
	 */
	public function singleton(string $id, object $instance): Container
	{
		$this->bindings[$id] = $instance;

		return $this;
	}

	/**
	 * @param string $id
	 * @return mixed
	 * @throws Exception
	 */
	public function get(string $id): mixed
    {
		if ($this->has($id)) {
			return $this->bindings[$id];
		}

		throw new NotFoundException("Container entry not found for: {$id}");
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function has(string $id): bool
	{
		return array_key_exists($id, $this->bindings);
	}

    /**
     * @param string $namespace
     * @param array $args
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws Exceptions\ContainerException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
	public function resolve(string $namespace, array $args = []):mixed
	{
		return (new ClassResolver($this, $namespace, $args))->getInstance();
	}

    /**
     * @param object $instance
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
	public function resolveMethod(object $instance, string $method, array $args = []): mixed
	{
		return (new MethodResolver($this, $instance, $method, $args))->getValue();
	}
}