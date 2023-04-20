<?php

namespace Tests;

class AppClass
{
    public function __construct(protected ExampleInterface $example, string $name = 'Test') {}

    public function handle(ExampleInterface $example)
    {
        return 'Class Example injected';
    }
}
