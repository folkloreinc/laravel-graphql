<?php

namespace Folklore\GraphQL\Relay\Support;

interface NodeContract
{
    /**
     * @param $id
     */
    public function resolveById($id);
}
