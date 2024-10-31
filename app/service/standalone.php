<?php
namespace PSSG_Sync_Sheet\App\Service;

/**
 * To make your class Standalone
 * We have to use this Trait
 * 
 * 
 * @since 1.0.0.11
 * @author Saiful Islam <codersaiful@gmail.com>
 */
trait Standalone
{
    /**
     * Initializing static property for Class
     * We will use this Trait acutally
     *
     * @var null|object
     */
    public static $init;

    /**
     * Initializing method init()
     * If we want to make any Class as Standalone 
     * then we have to use this trait
     * 
     * @since 1.0.0.11
     * 
     * @author Saiful Islam <codersaiful@gmail.com>
     * @return null|object
     */
    public static function init()
    {
        if( self::$init && self::$init instanceof self ) return self::$init;

        self::$init = new self();

        return self::$init;
    }
}