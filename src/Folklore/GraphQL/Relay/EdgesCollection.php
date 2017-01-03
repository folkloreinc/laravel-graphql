<?php

namespace Folklore\GraphQL\Relay;

use Illuminate\Database\Eloquent\Collection;

class EdgesCollection extends Collection
{
    protected $hasNextPage = false;
    protected $hasPreviousPage = false;
    
    public function setHasNextPage($hasNextPage)
    {
        $this->hasNextPage = $hasNextPage;
    }
    
    public function getHasNextPage()
    {
        return $this->hasNextPage;
    }
    
    public function setHasPreviousPage($hasPreviousPage)
    {
        $this->hasPreviousPage = $hasPreviousPage;
    }
    
    public function getHasPreviousPage()
    {
        return $this->hasPreviousPage;
    }
}
