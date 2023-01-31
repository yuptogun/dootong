<?php declare(strict_types=1);

namespace Yuptogun\Dootong\Interfaces;

use Yuptogun\Dootong\Dootong;
use Yuptogun\Dootong\Interfaces\Headache;

/**
 * A type of Headache
 */
interface Variety
{
    /**
     * take a repository to get/set inputs
     *
     * @param mixed $repository proper repository (PDO, JSON, etc)
     */
    public function __construct($repository);

    /**
     * get from repository
     *
     * @param Dootong $dootong instance to handle
     * @param array|null $params
     * @return Headache[]
     */
    public function get(Dootong $dootong, ?array $params = null): array;

    /**
     * set into repository
     *
     * @param Dootong $dootong instance to handle
     * @param array $params
     * @return int affected rows count or last insert id
     * @throws Throwable
     */
    public function set(Dootong $dootong, array $params): int;

    public function getDootong(): Dootong;
    public function setDootong(Dootong $dootong): void;
}
