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

function dropr_Client_Autoload($className)
{
  	if (strpos($className, 'dropr_') !== 0) {
            return;
        }
  	$file = substr(str_replace('_', '/', $className), 4) . '.php';
    
  	require realpath(dirname(__FILE__)) . '/' . $file;
}

spl_autoload_register('dropr_Client_Autoload');
