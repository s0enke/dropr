<?php
class dropr_Server_Storage_Filesystem extends dropr_Server_Storage_Abstract 
{
    
    const SPOOLDIR_TYPE_SPOOL = 'proc';
    
    const SPOOLDIR_TYPE_PROCESSED = 'done';
    
    /**
     * This is the separator of metadata encoding in the filename
     *
     */
    const SPOOLFILE_METADATA_SEPARATOR = ':';
    
    private $path;
	
	protected function __construct($path)
	{
	    
	    // XXX: Code duplication with client
	    
	    if (!is_string($path)) {
	        throw new dropr_Server_Exception("No valid path given");
	    }
	    
	    if (!is_dir($path)) {
	        if (!@mkdir($path, 0755)) {
	            throw new dropr_Server_Exception("Could not create Queue Directory $path");
	        }
	    }
	    
	    if (!is_writeable($path)) {
	        throw new dropr_Server_Exception("$path is not writeable!");
	    }
	    
	    $this->path = realpath($path);
	}

    public function put(dropr_Server_Message $message)
    {
        $mHandle = $message->getMessage();
        if ($mHandle instanceof SplFileInfo) {
            // XXX typ!
            // xxx auslagern in eigene funktion

            $src  = $mHandle->getPathname();
            $proc = $this->buildMessagePath($message, self::SPOOLDIR_TYPE_SPOOL);
            $done = $this->buildMessagePath($message, self::SPOOLDIR_TYPE_PROCESSED);

            if (file_exists ($proc) || file_exists ($done)) {
                return;
            }
            
            // sometimes php throws a warning but returns true and the file is moved
            // .. maybe NFS issue so we have to use the @-operator
            if (!@rename($src, $proc)) {
                throw new dropr_Server_Exception("Could not save $src to $proc");
            }
        } else {
            throw new dropr_Server_Exception('not implemented');
        }
    }

    private function getSpoolPath($type, $channel = 'common')
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . base64_encode($channel);
        
        if (!is_dir($path)) {
            if (!mkdir($path, 0775, true)) {
                throw new dropr_Server_Exception("Could not create directory $path!");
            }
        }
        
        return $path;
    }
    
    public function getType()
    {
        return self::TYPE_FILE;
    }
    
    public function getMessages($channel = 'common', $limit = null)
    {
        $spoolDir = $this->getSpoolPath(self::SPOOLDIR_TYPE_SPOOL, $channel) . DIRECTORY_SEPARATOR;
        $fNames = scandir($spoolDir);

        // unset the "." and the ".."
        unset($fNames[0]);
        unset($fNames[1]);

        $messages = array();
        foreach($fNames as $k => $fName) {

            if ($limit && $k > $limit) {
                break;
            }
                        
            list($priority, $messageId, $client) = explode(self::SPOOLFILE_METADATA_SEPARATOR, $fName, 3);
            
            $filePath = $spoolDir . DIRECTORY_SEPARATOR . $fName; 

            $message = new dropr_Server_Message($client, $messageId, new SplFileInfo($filePath), $channel, $priority, filectime($filePath), $this);

            $messages[] = $message;
        }

        return $messages;
    }
    
    public function setProcessed(dropr_Server_Message $message)
    {
        return rename($this->buildMessagePath($message, self::SPOOLDIR_TYPE_SPOOL), $this->buildMessagePath($message, self::SPOOLDIR_TYPE_PROCESSED));
    }
    
    public function pollProcessed($messageId)
    {
        
    }
    
    
    /**
     * Build the spoolpath for a message
     */
    private function buildMessagePath(dropr_Server_Message $message, $type)
    {
        /*
         * The server storage metadata separator is ":" because there
         * is a conflict with the client using underscores
         */
       
    	return $this->getSpoolPath($type, $message->getChannel()) . DIRECTORY_SEPARATOR . $message->getPriority() . self::SPOOLFILE_METADATA_SEPARATOR . $message->getId() .  self::SPOOLFILE_METADATA_SEPARATOR . $message->getClient();        
    }
    
}
