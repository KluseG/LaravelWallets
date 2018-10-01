<?php

namespace KluseG\LaravelWallets\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelWallets extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravelwallets';
    }
}
