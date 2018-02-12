<?php

namespace Folklore\GraphQL\Relay;

use Illuminate\Database\Eloquent\Collection;

class EdgesCollection extends Collection
{
    /**
     * @var int
     */
    protected $total = 0;
    /**
     * @var mixed
     */
    protected $startCursor = null;
    /**
     * @var mixed
     */
    protected $endCursor = null;
    /**
     * @var mixed
     */
    protected $hasNextPage = false;
    /**
     * @var mixed
     */
    protected $hasPreviousPage = false;

    /**
     * @param $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param $hasNextPage
     */
    public function setHasNextPage($hasNextPage)
    {
        $this->hasNextPage = $hasNextPage;
    }

    /**
     * @return mixed
     */
    public function getHasNextPage()
    {
        return $this->hasNextPage;
    }

    /**
     * @param $hasPreviousPage
     */
    public function setHasPreviousPage($hasPreviousPage)
    {
        $this->hasPreviousPage = $hasPreviousPage;
    }

    /**
     * @return mixed
     */
    public function getHasPreviousPage()
    {
        return $this->hasPreviousPage;
    }

    /**
     * @param $startCursor
     */
    public function setStartCursor($startCursor)
    {
        $this->startCursor = $startCursor;
    }

    /**
     * @return mixed
     */
    public function getStartCursor()
    {
        return $this->startCursor;
    }

    /**
     * @param $endCursor
     */
    public function setEndCursor($endCursor)
    {
        $this->endCursor = $endCursor;
    }

    /**
     * @return mixed
     */
    public function getEndCursor()
    {
        return $this->endCursor;
    }
}
