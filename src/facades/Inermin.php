<?php

namespace Tokalink\Inermin\facades;

use Illuminate\Support\Facades\Facade;

class Inermin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'inermin';
    }
}
