<?php
class pmq_Server_Storage_Filesystem extends pmq_Server_Storage_Abstract 
{
    
    const SPOOLDIR_TYPE_SPOOL = 'proc';
    
    const SPOOLDIR_TYPE_PROCESSED = 'done';
    
    private $path;
	
	protected function __construct($path)
	{
	    
	    // XXX: Code duplication with client
	    
	    if (!is_string($path)) {
	        throw new pmq_Server_Exception("No valid path given");
	    }
	    
	    if (!is_dir($path)) {
	        if (!@mkdir($path, 0755)) {
	            throw new Pmq_Server_Exception("Could not create Queue Directory $path");
	        }
	    }
	    
	    if (!is_writeable($path)) {
	        throw new pmq_Server_Exception("$path is not writeable!");
	    }
	    
	    $this->path = realpath($path);
	}
	
    public function put($messageHandle)
    {
        
    }
    
    public function getDestination() {
        return $this->getSpoolPath(self::SPOOLDIR_TYPE_SPOOL);
    }
	
   
    private function getSpoolPath($type)
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $type;
        
        if (!is_dir($path)) {
            if (!mkdir($path, 0775)) {
                throw new pmq_Server_Exception("Could not created directory $path!");
            }
        }
        
        return $path;
        
    }
    
    public function getType() {
        return self::TYPE_FILE;
    }
    
}