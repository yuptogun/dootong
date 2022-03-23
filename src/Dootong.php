<?php
namespace Yuptogun\Dootong;

use DateTime;
use JsonSerializable;
use Exception;
use InvalidArgumentException;

use Yuptogun\Dootong\Interfaces\Variety;
use Yuptogun\Dootong\Interfaces\Headache;

class Dootong implements JsonSerializable, Headache
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

    /**
     * what kind of `Headache` is it?
     *
     * @var Variety
     */
    private $variety;

    /**
     * where these `Headache[]` are from?
     *
     * @var mixed
     */
    private $getCause;

    /**
     * how I get this `Headache`?
     *
     * @var mixed
     */
    private $setCause;

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
        foreach ($this->getAttributeCastings() as $attribute => $cast) {
            if ($attr === $attribute) {
                return strtolower($cast);
            }
        }
        return 'string';
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
        $fillables = $this->fillable;
        if ($this->isSoftDeletable()) {
            $fillables[] = $this->getDeletedAtName();
        }
        return $fillables;
    }

    protected function getHiddenAttributes(): array
    {
        return $this->hidden;
    }

    protected function getDeletedAtName(): ?string
    {
        return $this->deletedAt;
    }

    protected function getAttributes(): array
    {
        return $this->attributes;
    }

    protected function isAttributeRequired(string $name): bool
    {
        return in_array($name, $this->getRequiredAttributes());
    }

    protected function getRequiredAttributes(): array
    {
        return $this->required;
    }

    protected function castAttributes(): void
    {
        foreach ($this->getAttributeCastings() as $attr => $type) {
            $this->setCastedValue($attr, $type);
        }
    }

    protected function getAttributeCastings(): array
    {
        $casts = $this->casts;
        if ($this->isSoftDeletable()) {
            $casts[$this->getDeletedAtName()] = 'datetime';
        }
        return $casts;
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
