<?php
abstract class pmq_Server_Storage_Abstract
{

    const TYPE_STREAM = 1;
    const TYPE_MEMORY = 2;
    const TYPE_FILE   = 3;
    
    public static function factory($type, $dsn)
    {
        $className = 'pmq_Server_Storage_' . ucfirst($type);
        return new $className($dsn);
    }
    
    abstract public function getType();
    
    abstract public function put($messageHandle);
    
}