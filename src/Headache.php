<?php declare(strict_types=1);

namespace Yuptogun\Dootong;

use Generator;

/**
 * Repository working with `Dootong`-implemented DTOs
 */
abstract class Headache
{
    /**
     * define "how the records should be fetched"
     *
     * @var mixed SQL, Redis commands...
     */
    protected $getter;

    /**
     * define "how the record should be inserted"
     *
     * @var mixed SQL, Redis commands...
     */
    protected $setter;

    /**
     * fetch from repository
     *
     * @uses getGetter()
     * @param Dootong $dootong
     * @param array|null $cause a pack of whatever is required to fetch
     * @return Generator|Dootong[] the clones of the argument-given $dootong DTO
     */
    abstract public function get(Dootong $dootong, ?array $cause = null): Generator;

    /**
     * insert into repository
     *
     * @uses getSetter()
     * @param Dootong $dootong
     * @param array $cause a pack of whatever is required to insert
     * @return integer it varies. (the id of the inserted record, how many records are inserted, etc.)
     */
    abstract public function set(Dootong $dootong, array $cause): int;

    /**
     * define getter on the go
     *
     * @param mixed $getter
     * @return self
     */
    public function setGetter($getter): self
    {
        $this->getter = $getter;
        return $this;
    }

    /**
     * define setter on the go
     *
     * @param mixed $setter
     * @return self
     */
    public function setSetter($setter): self
    {
        $this->setter = $setter;
        return $this;
    }

    /**
     * alias of setGetter
     *
     * @param mixed $cause
     * @return self
     */
    public function diagnose($cause): self
    {
        return $this->setGetter($cause);
    }

    /**
     * alias of setSetter
     *
     * @param mixed $cause
     * @return self
     */
    public function prescribe($cause): self
    {
        return $this->setSetter($cause);
    }

    public function isDiagnosed(): bool
    {
        return $this->getter !== null;
    }

    public function isPrescribed(): bool
    {
        return $this->setter !== null;
    }

    protected function getGetter()
    {
        return $this->getter;
    }

    protected function getSetter()
    {
        return $this->setter;
    }
}
