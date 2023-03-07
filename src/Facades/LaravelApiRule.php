<?php

namespace BrayanCaro\LaravelApiRule\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BrayanCaro\LaravelApiRule\LaravelApiRule
 */
class LaravelApiRule extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \BrayanCaro\LaravelApiRule\LaravelApiRule::class;
    }
}
