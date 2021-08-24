<?php

namespace Innoractive\HuaweiPushService\Support;

trait Singleton
{
    /**
     * The singleton instance.
     *
     * @var object
     */
    protected static $instance;

    /**
     * Get the singleton instance.
     *
     * @param  mixed  ...$arguments
     * @return object
     */
    final public static function getInstance(...$arguments)
    {
        if (static::$instance === null) {
            static::$instance = new static(...$arguments);
        }

        return static::$instance;
    }
}
