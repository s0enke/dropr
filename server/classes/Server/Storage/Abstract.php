<?php
abstract class pmq_Server_Storage_Abstract
{

    public static function factory($type, $dsn)
    {
        $className = 'pmq_Server_Storage_' . ucfirst($type);
        return new $className($dsn);
    }
    
    
    
}