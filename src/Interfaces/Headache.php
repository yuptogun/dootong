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
    public function get($source): array;

    /**
     * Registers into repository
     *
     * @param mixed $source
     * @param array $entry
     * @return self
     * @throws Throwable
     */
    public function set($source, array $entry): self;

    public function castAttributes(): void;
    public function getPublicAttributes(): array;
    public function checkRequiredAttributes(): bool;
}