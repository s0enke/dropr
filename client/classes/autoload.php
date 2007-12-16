<?php
/**
 * Autoloader for the dropr client classes
 * 
 * @author Soenke Ruempler <soenke@jimdo.com>
 */
function dropr_Server_Autoload($className)
{
    if (strpos($className, 'dropr_') !== 0) { 
       return; 
    } 
    
    $file = substr(str_replace('_', '/', $className), 6) . '.php';
    require realpath(dirname(__FILE__)) . '/' . $file;
}

spl_autoload_register('dropr_Server_Autoload');
