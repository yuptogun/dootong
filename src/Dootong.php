<?php declare(strict_types=1);

namespace Yuptogun\Dootong;

use DateTime;
use JsonSerializable;
use Throwable;
use Exception;
use InvalidArgumentException;

use Yuptogun\Dootong\Interfaces\Variety;
use Yuptogun\Dootong\Interfaces\Headache;

abstract class Dootong implements JsonSerializable, Headache
{
    /**
     * @var array
     */
    private $attributes = [];

    // ---- abstract methods --- //

    /**
     * define the attributes here
     *
     * @return string[]
     */
    abstract protected function getAttributes(): array;

    /**
     * define the "must-set" attributes here
     *
     * @return string[] return an empty array if every attribute is optional
     */
    abstract protected function getRequiredAttributes(): array;

    /**
     * define the "set-only" attributes here
     *
     * @return string[] return an empty array if nothing should be invisible
     */
    abstract protected function getHiddenAttributes(): array;

    /**
     * define the "types" of attributes here
     *
     * ['foo' => 'int', 'bar' => 'password', 'id' => 'increment', ...]
     *
     * @return array return an empty array if everything is a string
     */
    abstract protected function getAttributeCastings(): array;

    /**
     * define the "deleted at" attribute here
     *
     * @return string|null
     */
    abstract protected function getDeletedAtName(): ?string;

    // ---- JsonSerializable implementation ---- //

    public function jsonSerialize(): array
    {
        return $this->getPublicAttributes();
    }

    // ---- Headache implementation ---- //

    public function __construct(?Variety $variety = null)
    {
        if ($variety) {
            $this->setVariety($variety);
        }
    }

    public static function suffer(Variety $variety): Headache
    {
        $dootong = new static;
        $dootong->setVariety($variety);
        return $dootong;
    }

    public function get(?array $attrs = null, $cause = null): array
    {
        if ($cause !== null) {
            $this->setGetCause($cause);
        }
        return $this->getVariety()->get($this, $attrs);
    }

    public function set(array $attrs, $cause = null): int
    {
        if ($cause !== null) {
            $this->setSetCause($cause);
        }
        if (is_array($attrs)) {
            $keys = array_keys($attrs);
            foreach ($this->getRequiredAttributes() as $required) {
                if (!array_key_exists($required, $keys)) {
                    throw new InvalidArgumentException(self::class . " requires $required attribute!");
                }
            }
        }
        return $this->getVariety()->set($this, $attrs);
    }

    public function setGetCause($cause): Headache
    {
        $this->getCause = $cause;
        return $this;
    }

    public function setSetCause($cause): Headache
    {
        $this->setCause = $cause;
        return $this;
    }

    public function getGetCause()
    {
        return $this->getCause;
    }

    public function getSetCause()
    {
        return $this->setCause;
    }

    public function getVariety(): Variety
    {
        if (!$this->variety) {
            throw new Exception(get_called_class()." Dootong is unknown of its Variety! Must suffer() from one first.");
        }
        return $this->variety;
    }

    public function setVariety(Variety $variety): void
    {
        $this->variety = $variety;
    }

    public function getCastedValue(string $attr, $value)
    {
        $type = $this->getCasting($attr);
        $isTypeNullable = substr($type, 0, 1) === '?';
        if ($isTypeNullable) {
            $type = substr($type, 1);
        }

        if (strpos($type, '::class') !== false) {
            try {
                return new $type($value);
            } catch (Throwable $th) {
                if ($isTypeNullable) {
                    return null;
                }
                throw $th;
            }
        }

        if ($isTypeNullable && $value === null) {
            return null;
        }

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

    public function getCasting(string $attr): string
    {
        if ($attr === $this->getDeletedAtName()) {
            return '?datetime';
        }
        foreach ($this->getAttributeCastings() as $attribute => $cast) {
            if ($attr === $attribute) {
                return strtolower($cast);
            }
        }
        return '?string';
    }

    public function hasIncrementingKey(): bool
    {
        foreach (array_values($this->getAttributeCastings()) as $cast) {
            if ($cast === 'increment') {
                return true;
            }
        }
        return false;
    }

    public function isSoftDeletable(): bool
    {
        return !empty($this->getDeletedAtName());
    }

    public function isSoftDeleted(): bool
    {
        return $this->isSoftDeletable() && !empty($this->getAttribute($this->getDeletedAtName()));
    }

    public function withTrashed(): Headache
    {
        $this->deletedAt = null;
        return $this;
    }

    // ---- functions of abstract class Dootong ---- //

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

    protected function setAttribute(string $name, $value)
    {
        if ($this->isAttributeFillable($name)) {
            $this->attributes[$name] = $this->getCastedValue($name, $value);
        }
    }

    protected function getAttribute(string $name)
    {
        if ($this->isAttributeHidden($name)) {
            return null;
        }
        return $this->getAttributes()[$name] ?? null;
    }

    protected function isAttributeFillable(string $name): bool
    {
        return in_array($name, $this->getFillableAttributes());
    }

    protected function isAttributeHidden(string $name): bool
    {
        return in_array($name, $this->getHiddenAttributes());
    }

    protected function getFillableAttributes(): array
    {
        $fillables = $this->getAttributes();
        if ($this->isSoftDeletable()) {
            $fillables[] = $this->getDeletedAtName();
        }
        return $fillables;
    }

    protected function isAttributeRequired(string $name): bool
    {
        return in_array($name, $this->getRequiredAttributes());
    }

    protected function castAttributes(): void
    {
        foreach ($this->getAttributeCastings() as $attr => $type) {
            $this->setCastedValue($attr, $type);
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

    protected function getPublicAttributes(): array
    {
        return array_filter($this->attributes, function ($attr) {
            return !in_array($this->getCasting($attr), ['password'])
                && !in_array($attr, $this->getHiddenAttributes());
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function checkRequiredAttributes(): bool
    {
        foreach ($this->getRequiredAttributes() as $attr) {
            if (!$this->{$attr}) {
                return false;
            }
        }
        return true;
    }
}
