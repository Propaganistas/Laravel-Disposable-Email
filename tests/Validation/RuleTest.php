<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Validation;

use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelDisposableEmail\Tests\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;
use ReflectionClass;

class RuleTest extends TestCase
{
    #[Test]
    public function it_defaults_to_not_inspecting_mx_records()
    {
        $rule = new Indisposable;

        $this->assertFalse($this->getProtectedProperty($rule, 'inspectMxRecords'));
    }

    #[Test]
    public function it_enables_mx_record_inspection()
    {
        $rule = (new Indisposable)->inspectMxRecords();

        $this->assertTrue($this->getProtectedProperty($rule, 'inspectMxRecords'));
    }

    #[Test]
    public function it_converts_validation_parameters()
    {
        $rule = (new Indisposable)->setParameters(['mx']);

        $this->assertTrue($this->getProtectedProperty($rule, 'inspectMxRecords'));
    }

    protected function getProtectedProperty(object $object, string $property)
    {
        $property = (new ReflectionClass($object))->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
