<?php

namespace Nano\Core;

use Nano\Core\Database;
use Exception;
use PDO;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class Container
{
    private array $bindings = [];
    private array $instances = [];
    private array $resolving = [];

    public function bind(string $abstract, string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function set(string $key, mixed $value): void
    {
        $this->instances[$key] = $value;
    }

    public function resolve(string $class): mixed
    {
        if (isset($this->bindings[$class])) {
            $class = $this->bindings[$class];
        }

        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        $reflector = new ReflectionClass($class);

        if ($reflector->isInternal()) {
            if ($class === PDO::class || is_subclass_of($class, PDO::class)) {
                return $this->instances[$class] = Database::instance();
            }

            if ($reflector->isInstantiable() && $reflector->getConstructor() === null) {
                return $this->instances[$class] = new $class();
            }

            throw new Exception("The inner class '{$class}' requires manual configuration and cannot be resolved automatically");
        }

        if (in_array($class, $this->resolving)) {
            throw new Exception("Circular dependency detected while trying to resolve: {$class}");
        }

        if (!$reflector->isInstantiable()) {
            throw new Exception("The class '{$class}' cannot be instantiated. If it's an interface, use the bind() method");
        }

        $this->resolving[] = $class;

        try {
            $constructor = $reflector->getConstructor();

            if (!$constructor) {
                return $this->instances[$class] = new $class();
            }

            $dependencies = $this->resolveDependencies($constructor);

            return $this->instances[$class] = $reflector->newInstanceArgs($dependencies);
        } finally {
            array_pop($this->resolving);
        }
    }

    private function resolveDependencies(ReflectionMethod $constructor): array
    {
        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            $name = $parameter->getName();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->resolve($type->getName());

                continue;
            }

            if (isset($this->instances[$name])) {
                $dependencies[] = $this->instances[$name];

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();

                continue;
            }

            throw new Exception("Could not resolve '{$name}' in '{$constructor->getDeclaringClass()->getName()}'");
        }

        return $dependencies;
    }
}
