<?php
/**
 * Autoloader for the Message Queue client
 * 
 * @author Soenke Ruempler 
 */

/*
 * set the include path
 */
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)));

function pmq_Client_Autoload($className)
{
  	$file = substr(str_replace('_', '/', $className), 4) . '.php';
    echo $file . "\n";
    
  	require realpath(dirname(__FILE__)) . '/' . $file;
}

spl_autoload_register('pmq_Client_Autoload');
