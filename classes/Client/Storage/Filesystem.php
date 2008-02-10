<?php
/**
 * Filesystem Storage Driver
 * 
 * @author Soenke Ruempler
 * @author Boris Erdmann
 *
 */
class dropr_Client_Storage_Filesystem extends dropr_Client_Storage_Abstract
{

    const SPOOLDIR_TYPE_IN = 'in';

    const SPOOLDIR_TYPE_SPOOL = 'proc';

    const SPOOLDIR_TYPE_SENT = 'sent';

    private $path;

	protected function __construct($path)
	{
	    
	    if (!is_string($path)) {
	        throw new dropr_Client_Exception("No valid path given");
	    }
	    
	    if (!is_dir($path)) {
	        if (!@mkdir($path, 0755)) {
	            throw new dropr_Client_Exception("Could not create Queue Directory $path");
	        }
	    }
	    
	    if (!is_writeable($path)) {
	        throw new dropr_Client_Exception("$path is not writeable!");
	    }
	    
	    $this->path = realpath($path);
	}

    public function saveMessage(dropr_Client_Message $message)
    {
        $inPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_IN) . DIRECTORY_SEPARATOR;
        $spoolPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_SPOOL) . DIRECTORY_SEPARATOR;

        $priority = $message->getPriority();
        $peer = $this->encodeForFs($message->getPeer()->getKey());
        $channel = $this->encodeForFs($message->getChannel());

        $badName = true;
        while ($badName) {

            $fName = join('_', array(
                $priority,
                $this->getTimeStamp(),
                $peer,
                $channel
            ));

            $fh = @fopen($inPath . $fName, 'x');
            $badName = ($fh === false);
        }

        fwrite($fh, $message->getMessage());
        fclose($fh);

        if (!rename($inPath . $fName, $spoolPath . $fName)) {
            throw new dropr_Client_Exception("Could not move spoolfile " . $fName . "!");
        }
        return $fName;
    }

    public function getQueuedMessages($limit = null, &$peerKeyBlackList = null)
    {
        // expire blacklisted peers
        $now = time();
        foreach ($peerKeyBlackList as $peerKey => $timeout) {
            if ($now > $timeout) {
                unset($peerKeyBlackList[$peerKey]);
            }
        }

        $spoolDir = $this->getSpoolPath(self::SPOOLDIR_TYPE_SPOOL) . DIRECTORY_SEPARATOR;
        $fNames = scandir($spoolDir);

        // unset the "." and the ".."
        unset($fNames[0]);
        unset($fNames[1]);

        $c = 1;
        $messages = array();
        foreach($fNames as $k => $fName) {

            if ($limit && ($c > $limit)) {
                break;
            }

            list($priority, $timeStamp, $encodedPeerKey, $encodedChannel) = explode('_', $fName, 4);
            $decodedPeerKey = $this->decodeFromFs($encodedPeerKey);
            $decodedChannel = $this->decodeFromFs($encodedChannel);

            $message = new dropr_Client_Message(
                NULL,
                new SplFileInfo($spoolDir . $fName),
                dropr_Client_Peer_Abstract::getInstance($decodedPeerKey),
                $decodedChannel,
                $priority
            );
            $message->restoreId($fName);

            if (!isset($peerKeyBlackList[$decodedPeerKey])) {
                $messages[$decodedPeerKey][] = $message;
                $c++;
            }
        }

        return $messages;
    }

    /**
     * 
     */
    public function getMessage($messageId, dropr_Client_Peer $peer)
    {
        return $this->getPeerSpoolPath($peer, self::SPOOLDIR_TYPE_SPOOL) . DIRECTORY_SEPARATOR . $messageId;
    }

    private function encodeForFs($val)
    {
        return base64_encode($val);
    }

    private function decodeFromFs($val)
    {
        return base64_decode($val);
    }

    private function getSpoolPath($type = self::SPOOLDIR_TYPE_IN)
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $type;
        
        if (!is_dir($path)) {
            if (!mkdir($path, 0775)) {
                throw new dropr_Client_Exception("Could not create directory $path!");
            }
        }

        return $path;
    }
    
    private function getTimeStamp()
    {
        $tName = (string)microtime();
        $spPos = strpos($tName, ' ');
        return substr($tName, $spPos+1).'-'.substr($tName, 2, $spPos-2);
    }
    
    public function getType()
    {
        return self::TYPE_FILE;
    }
    
    public function checkSentMessages(array &$messages, array &$result)
    {
        $spoolPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_SPOOL) . DIRECTORY_SEPARATOR;
        $sentPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_SENT) . DIRECTORY_SEPARATOR;

        foreach ($messages as $k => $message) {

            $msgId = $message->getId();

            if (isset($result[$msgId]['inqueue']) && ($result[$msgId]['inqueue'] === true)) {
                if (!rename($spoolPath . $msgId, $sentPath . $msgId)) {
                    throw new dropr_Client_Exception("Could not move spoolfile!");
                }                
            }
            
            unset($message);
        }
        
    }

    public function countQueuedMessages()
    {
        $spoolPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_SPOOL) . DIRECTORY_SEPARATOR;
        return count(scandir($spoolPath))-2;
    }

    public function countSentMessages()
    {
        $spoolPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_SENT) . DIRECTORY_SEPARATOR;
        return count(scandir($spoolPath))-2;
    }

    public function wipeSentMessages($olderThanMinutes)
    {
        $spoolPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_SENT) . DIRECTORY_SEPARATOR;
        $dirIter = new DirectoryIterator($spoolPath);
        $time = time()-($olderThanMinutes*60);
        $c = 0;
        foreach ($dirIter as $fInfo) {
            if (($fInfo->getATime() < $time) && $fInfo->isFile()) {

                unlink($spoolPath . $fInfo->getFilename());
                $c++;
            }
        }
        return $c;
    }
}
