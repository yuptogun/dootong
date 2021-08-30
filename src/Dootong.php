<?php
namespace Yuptogun\Dootong;

use JsonSerializable;
use Yuptogun\Dootong\Interfaces\Headache;

abstract class Dootong implements JsonSerializable, Headache
{
    protected $fillable = [];
    protected $required = [];
    protected $hidden = [];
    protected $casts = [];

    /**
     * all values stored here
     *
     * @var array
     */
    private $attributes = [];

    public function getFillableAttributes(): array
    {
        return $this->fillable;
    }

    public function getRequiredAttributes(): array
    {
        return $this->required;
    }

    public function getHiddenAttributes(): array
    {
        return $this->hidden;
    }

    public function getAttributeCastings(): array
    {
        return $this->casts;
    }

    protected function getAttributes(): array
    {
        return $this->attributes;
    }

    protected function getAttribute(string $name)
    {
        return $this->getAttributes()[$name] ?? null;
    }

    protected function setAttribute(string $name, $value)
    {
        $this->attributes[$name] = $this->getCastedValue($value, $this->getCasting($name));
    }

    protected function getCasting(string $name): string
    {
        foreach ($this->getAttributeCastings() as $attr => $cast) {
            if ($name === $attr) {
                return $cast;
            }
        }
        return 'string';
    }

    protected function getCastedValue($value, $type = 'string')
    {
        $type = strtolower($type);
        switch ($type) {
            case 'int': case 'integer': case 'increment':
                return (int) $value;
            case 'string':
            default:
                return (string) $value;
        }
    }

    protected function setCastedValue($attr, $type)
    {
        if ($type === 'increment') {
            unset($this->{$attr});
        } else {
            $this->setAttribute($attr, $this->getAttribute($attr));
        }
    }

    public function __set(string $name, $value): void
    {
        $this->setAttribute($name, $value);
    }

    public function __get(string $name)
    {
        return $this->getAttribute($name);
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function __unset(string $name): void
    {
        $attrs = $this->attributes;
        unset($attrs[$name]);
        $this->attributes = $attrs;
    }

    public function jsonSerialize(): array
    {
        return $this->getPublicAttributes();
    }

    public function castAttributes(): void
    {
        foreach ($this->getAttributeCastings() as $attr => $type) {
            $this->setCastedValue($attr, $type);
        }
    }

    public function getPublicAttributes(): array
    {
        return array_filter($this->attributes, function ($attr) {
            return !in_array($this->getCasting($attr), ['password'])
                && !in_array($attr, $this->getHiddenAttributes());
        }, ARRAY_FILTER_USE_KEY);
    }

    public function checkRequiredAttributes(): bool
    {
        foreach ($this->getRequiredAttributes() as $attr) {
            if (!$this->{$attr}) {
                return false;
            }
        }
        return true;
    }
}
