<?php

namespace Propaganistas\LaravelSms\Facades;

use Illuminate\Support\Facades\Facade;
use Propaganistas\LaravelSms\Testing\SmsFake;

class Sms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sms.manager';
    }

    public static function fake()
    {
        $actualManager = static::isFake()
            ? static::getFacadeRoot()->manager
            : static::getFacadeRoot();

        return tap(new SmsFake($actualManager), function ($fake) {
            static::swap($fake);
        });
    }
}
