<?php

namespace Makraz\VvvebJsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class VvvebJsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
