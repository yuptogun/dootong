<?php
namespace Yuptogun\Dootong\Interfaces;

use Yuptogun\Dootong\Interfaces\Variety;

/**
 * Every `Dootong` instance should implement `Headache`
 */
interface Headache
{
    /**
     * `Dootong` is a `new Headache` of a `Variety`
     *
     * @param Variety|null $variety
     */
    public function __construct(?Variety $variety = null);

    /**
     * `Dootong` is what you suffer from a `Variety` of `Headache`
     *
     * @param Variety $variety
     * @return Headache
     */
    public static function suffer(Variety $variety): Headache;

    /**
     * get from repository, according to "variety"
     *
     * @param mixed $cause
     * @param array|null $attrs
     * @return Headache[]
     * @throws Throwable
     */
    public function get($cause, ?array $attrs = null): array;

    /**
     * set into repository, according to "variety"
     *
     * @param mixed $cause
     * @param array $attrs
     * @return int affected rows count or last insert id
     * @throws Throwable
     */
    public function set($cause, array $attrs): int;

    /**
     * cast user input while set
     *
     * @param string $attr return of getCasting()
     * @param mixed $value
     * @return mixed
     */
    public function getCastedValue(string $attr, $value);

    /**
     * get casting for attribute while set
     *
     * @param string $attr
     * @return string
     */
    public function getCasting(string $attr): string;

    /**
     * any `increment` casting set?
     *
     * @return boolean
     */
    public function hasIncrementingKey(): bool;

    /**
     * `deletedAt` property set?
     *
     * @return boolean
     */
    public function isSoftDeletable(): bool;

    /**
     * `deletedAt` < now?
     *
     * @return boolean
     */
    public function isSoftDeleted(): bool;

    /**
     * ignore soft delete setting while get
     *
     * @return Headache
     */
    public function withTrashed(): Headache;

    public function getVariety(): Variety;
    public function setVariety(Variety $variety): void;
}