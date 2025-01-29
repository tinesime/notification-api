<?php

namespace Notification;

use Closure;
use Exception;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $key, Closure $resolver, bool $shared = true): void
    {
        $this->bindings[$key] = [
            'resolver' => $resolver,
            'shared'   => $shared
        ];
    }

    /**
     * @throws Exception
     */
    public function get(string $key)
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        if (!isset($this->bindings[$key])) {
            throw new Exception("No service bound for key: {$key}");
        }

        $binding = $this->bindings[$key];
        $object = $binding['resolver']($this);

        if ($binding['shared']) {
            $this->instances[$key] = $object;
        }

        return $object;
    }
}