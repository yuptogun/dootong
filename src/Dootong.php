<?php
namespace Yuptogun\Dootong;

use DateTime;
use JsonSerializable;
use Yuptogun\Dootong\Interfaces\Headache;

abstract class Dootong implements JsonSerializable, Headache
{
    protected $fillable = [];
    protected $required = [];
    protected $hidden = [];
    protected $casts = [];

    /**
     * @var null|string
     */
    protected $deletedAt;

    /**
     * all values stored here
     *
     * @var array
     */
    private $attributes = [];

    public function getFillableAttributes(): array
    {
        $fillables = $this->fillable;
        if ($this->isSoftDeletable()) {
            $fillables[] = $this->getDeletedAtName();
        }
        return $fillables;
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
        $casts = $this->casts;
        if ($this->isSoftDeletable()) {
            $casts[$this->getDeletedAtName()] = 'datetime';
        }
        return $casts;
    }

    public function getDeletedAtName(): ?string
    {
        return $this->deletedAt;
    }

    protected function getAttributes(): array
    {
        return $this->attributes;
    }

    protected function getAttribute(string $name)
    {
        if ($this->isAttributeHidden($name)) {
            return null;
        }
        return $this->getAttributes()[$name] ?? null;
    }

    protected function setAttribute(string $name, $value)
    {
        if ($this->isAttributeFillable($name)) {
            $this->attributes[$name] = $this->getCastedValue($value, $this->getCasting($name));
        }
    }

    protected function isAttributeFillable(string $name): bool
    {
        return in_array($name, $this->getFillableAttributes());
    }

    protected function isAttributeRequired(string $name): bool
    {
        return in_array($name, $this->getRequiredAttributes());
    }

    protected function isAttributeHidden(string $name): bool
    {
        return in_array($name, $this->getHiddenAttributes());
    }

    protected function isSoftDeletable(): bool
    {
        return !empty($this->getDeletedAtName());
    }

    protected function hasIncrementingKey(): bool
    {
        foreach (array_values($this->getAttributeCastings()) as $cast) {
            if ($cast === 'increment') {
                return true;
            }
        }
        return false;
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
            case 'bool': case 'boolean':
                return (bool) $value;
            case 'date': case 'datetime': case 'timestamp':
                return empty($value) ? null : new DateTime($value);
            case 'password':
                return password_hash($value, PASSWORD_DEFAULT);
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

    public function isSoftDeleted(): bool
    {
        return $this->isSoftDeletable() && !empty($this->getAttribute($this->getDeletedAtName()));
    }

    public function withTrashed(): self
    {
        $this->deletedAt = null;
        return $this;
    }
}
