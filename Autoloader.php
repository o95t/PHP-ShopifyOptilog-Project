<?php
namespace Aramex;

/**
 * include configuration file
 */
require_once 'config.php';

/**
 * Custom autoloder class
 */
class MyAutoload
{
    /**
     * Autoload files from folders like namespace
     * @param string $classname - classname
     * @return void
     */
    public static function autoload($classname){
        
        $classname = ltrim($classname, '\\');
        $classname = ltrim($classname, __NAMESPACE__);
        $classname = ltrim($classname, '\\');
        $classname = str_replace('\\', DIRECTORY_SEPARATOR, $classname);
        $filename = $classname .".php";
        if (file_exists($filename)){
            include_once($filename);
        }
    }
    
    /**
     * Autoload files from folder classes
     * @param string $classname - classname
     * @return void
     */
    public static function autoload_from_classes($classname){
        $path = explode('\\', $classname);
        $classShort = array_pop($path);
        $filename = 'classes'.DIRECTORY_SEPARATOR.$classShort.".php";
        if (file_exists($filename)){
            include_once($filename);
        }
    }
}

/**
 * register autoload methods
 */
spl_autoload_register(array('Aramex\MyAutoload', 'autoload'));
spl_autoload_register(array('Aramex\MyAutoload', 'autoload_from_classes'));