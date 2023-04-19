<?php

namespace Tests\IoC;

use Exception;
use Lucifer\IoC\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Tests\AppClass;
use Tests\ExampleClass;
use Tests\ExampleInterface;

class ContainerTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    protected function resetContainer(): void
    {
        $container = Container::instance();
        $reflection = new ReflectionClass($container);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null);
        $instance->setAccessible(false);
    }

    public function test_instance_method()
    {
        $container = Container::instance();
        $classTest = 'classB';
        $container->bind($classTest, 'ClassA');
        $newContainer = Container::instance();
        $this->assertTrue($newContainer->has($classTest), 'Failed to find classA in Container');
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws Exception
     */
    public function test_bind_method()
    {
        $this->resetContainer();

        $container = Container::instance();

        $container->bind(ExampleInterface::class, ExampleClass::class);

        $this->assertEquals(
            ExampleClass::class,
            $container->get(ExampleInterface::class),
            'Failed to get ExampleClass namespace'
        );

        $container->bind('Name', function () {
            return 'You';
        });

        $this->assertSame('You', $container->resolve('Name'), 'Failed to get Name function');
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function test_singleton_method()
    {
        $this->resetContainer();

        $container = Container::instance();

        $container->singleton(ExampleInterface::class, new ExampleClass());

        $this->assertInstanceOf(
            ExampleClass::class,
            $container->get(ExampleInterface::class),
            'Failed to get ExampleClass instance'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
    public function test_resolve_method()
    {
        $this->resetContainer();

        $container = Container::instance();

        $container->bind(ExampleInterface::class, ExampleClass::class);

        $this->assertInstanceOf(
            ExampleClass::class,
            $container->resolve(ExampleInterface::class),
            'Failed to resolve ExampleInterface'
        );

        $instance = $container->resolve(AppClass::class);

        $this->assertInstanceOf(AppClass::class, $instance, 'Failed to resolve AppClass');
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function test_resolve_method_method()
    {
        $this->resetContainer();

        $container = Container::instance();

        $container->bind(ExampleInterface::class, ExampleClass::class);

        $instance = $container->resolve(AppClass::class);

        $value = $container->resolveMethod($instance, 'handle');

        $this->assertEquals(
            'Class Example injected',
            $value,
            'Failed to resolve method handle of AppClass instance'
        );
    }
}
