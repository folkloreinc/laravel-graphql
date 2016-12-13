<?php

namespace Folklore\GraphQL\Relay\Support;

interface NodeContract
{
    public function resolveById(string $id);
}
