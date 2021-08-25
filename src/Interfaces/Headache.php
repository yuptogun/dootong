<?php
namespace Yuptogun\Dootong\Interfaces;

use Throwable;

/**
 * All `Dootong` instance should implement `Headache`
 */
interface Headache
{
    /**
     * Fetches from repository
     *
     * @param mixed $source
     * @return self[]
     */
    public static function get($source): array;

    /**
     * Registers into repository
     *
     * @param mixed $source
     * @param array $entry
     * @return self
     * @throws Throwable
     */
    public static function set($source, array $entry): self;
}