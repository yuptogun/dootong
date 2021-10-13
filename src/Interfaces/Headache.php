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
     * @param mixed $cause
     * @param array|null $params
     * @return self[]
     */
    public function get($cause, ?array $params = null): array;

    /**
     * Registers into repository
     *
     * @param mixed $cause
     * @param array $params
     * @return int affected rows count or last insert id
     * @throws Throwable
     */
    public function set($cause, array $params): int;

    public function castAttributes(): void;
    public function getPublicAttributes(): array;
    public function checkRequiredAttributes(): bool;
}