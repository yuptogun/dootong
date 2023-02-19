<?php declare(strict_types=1);

namespace Yuptogun\Dootong;

use DateTime;
use Generator;
use Throwable;
use JsonSerializable;
use InvalidArgumentException;
use RuntimeException;
use Yuptogun\Dootong\Headache;

/**
 * DTO working with `Headache` repositories
 */
abstract class Dootong implements JsonSerializable
{
    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var null|Headache
     */
    private $repository;

    /**
     * define "deleted at" datetime attribute
     *
     * @var null|string
     */
    protected $deletedAt;

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
    protected function getDeletedAtName(): ?string
    {
        return $this->deletedAt;
    }

    // ---- JsonSerializable implementation ---- //

    public function jsonSerialize(): array
    {
        return $this->getPublicAttributes();
    }

    /**
     * nominal constructor, or Headache-free hydration
     *
     * @param array|null $data\
     */
    public function __construct(?array $data = null)
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * factory method
     *
     * @param Headache $headache
     * @return $this
     */
    public static function sufferFrom(Headache $headache): self
    {
        $dootong = new static;
        $dootong->setRepository($headache);
        return $dootong;
    }

    /**
     * immutably set repository
     *
     * @param Headache $repository
     * @return self
     */
    public function setRepository(Headache $repository): self
    {
        if (!$this->repository) {
            $this->repository = $repository;
        }
        return $this;
    }

    /**
     * mutate already-set repository
     *
     * @param Headache $repository
     * @return self
     */
    public function resetRepository(Headache $repository): self
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * alias of resetRepository()
     *
     * @param Headache $headache
     * @return self
     */
    public function worsen(Headache $headache): self
    {
        return $this->resetRepository($headache);
    }

    /**
     * fetch from repository
     *
     * @param array|null $cause whatever the "diagnose" requires to provide
     * @return Generator|$this[]
     * @throws RuntimeException if repository is not ready
     */
    public function get(?array $cause = null): Generator
    {
        if (!$this->repository || !$this->repository->isDiagnosed()) {
            throw new RuntimeException('Please diagnose() this Headache!');
        }
        return $this->repository->get($this, $cause);
    }

    /**
     * insert into repository
     *
     * @param array $cause whatever the "prescription" requires to provide
     * @return integer how many records are set
     * @throws RuntimeException if repository is not ready
     * @throws InvalidArgumentException if any required attribute is missing in cause
     */
    public function set(array $cause): int
    {
        if (!$this->repository || !$this->repository->isPrescribed()) {
            throw new RuntimeException('Please prescribe() this Headache!');
        }
        foreach ($this->getRequiredAttributes() as $required) {
            if (!array_key_exists($required, $cause) || empty($cause[$required])) {
                throw new InvalidArgumentException(get_class($this) . " requires $required attribute!");
            }
        }
        return $this->repository->set($this, $cause);
    }

    public function withTrashed(): self
    {
        $this->deletedAt = null;
        return $this;
    }

    public function isPassword(string $password, ?string $attribute = null): bool
    {
        if ($attribute === null) {
            foreach ($this->getAttributeCastings() as $attr => $type) {
                if ($type === 'password') {
                    $attribute = $attr;
                    break;
                }
            }
        }
        if (!array_key_exists($attribute, $this->attributes)) {
            return false;
        }
        $hash = $this->attributes[$attribute];
        return password_verify($password, $hash);
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

    protected function setAttribute(string $name, $value)
    {
        if ($this->isAttributeFillable($name)) {
            $this->attributes[$name] = $value;
        }
    }

    protected function getAttribute(string $name)
    {
        if ($this->isAttributeHidden($name)) {
            return null;
        }
        return $this->getCastedValue($name, $this->attributes[$name] ?? null);
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

    protected function isAttributeFillable(string $name): bool
    {
        return in_array($name, $this->getFillableAttributes());
    }

    protected function isAttributeHidden(string $name): bool
    {
        $hidden = $this->getHiddenAttributes();
        foreach ($this->getAttributeCastings() as $attr => $type) {
            if ($type === 'password') {
                $hidden[] = $attr;
            }
        }
        return in_array($name, $hidden);
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
