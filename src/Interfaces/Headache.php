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
     * @param array|null $params
     * @return self[]
     */
    public function get($source, ?array $params = null): array;

    /**
     * Registers into repository
     *
     * @param mixed $source
     * @param array $entry
     * @return int affected rows count or last insert id
     * @throws Throwable
     */
    public function set($source, array $entry): int;

    public function castAttributes(): void;
    public function getPublicAttributes(): array;
    public function checkRequiredAttributes(): bool;
}