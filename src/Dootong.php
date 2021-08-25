<?php
namespace Yuptogun\Dootong;

use JsonSerializable;
use Yuptogun\Dootong\Interfaces\Headache;

abstract class Dootong implements JsonSerializable, Headache
{
    /**
     * all values stored here
     *
     * @var array
     */
    private $attributes = [];

    public function jsonSerialize(): array
    {
        return $this->attributes;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __get(string $name)
    {
        return $this->getAttribute($name);
    }

    public function __unset(string $name): void
    {
        $attrs = $this->attributes;
        unset($attrs[$name]);
        $this->attributes = $attrs;
    }

    protected function getAttribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }
}
