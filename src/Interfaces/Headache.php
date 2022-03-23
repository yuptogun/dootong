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
     * @uses Headache::setHeadacheGetCause()
     * @param null|array $attrs
     * @param null|mixed $cause
     * @return Headache[]
     * @throws Throwable
     */
    public function get(?array $attrs = null, $cause = null): array;

    /**
     * set into repository, according to "variety"
     *
     * @param array $attrs
     * @param null|mixed $cause
     * @return int affected rows count or last insert id
     * @throws Throwable
     */
    public function set(array $attrs, $cause = null): int;

    /**
     * set cause that will be used by get()
     *
     * @param mixed $cause SQL query, callback, whatever
     * @return Headache
     */
    public function setGetCause($cause): Headache;

    /**
     * set cause that will be used by set()
     *
     * @param mixed $cause SQL query, callback, whatever
     * @return Headache
     */
    public function setSetCause($cause): Headache;

    /**
     * get cause that works while get()
     *
     * @return mixed
     */
    public function getGetCause();

    /**
     * get cause that works while set()
     *
     * @return mixed
     */
    public function getSetCause();

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