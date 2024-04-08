<?php

namespace Propaganistas\LaravelSms\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Propaganistas\LaravelSms\SmsServiceProvider;
use ReflectionClass;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [SmsServiceProvider::class];
    }

    protected function getProtectedProperty(object $object, string $property)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
