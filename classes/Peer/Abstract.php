<?php
abstract class pmq_Peer_Abstract
{
    private $dsn;
    
    public function __construct($dsn)
    {
        $this->dsn = $dsn;
    }
    
    abstract public function put($message);    
    
}