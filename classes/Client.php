<?php
class dropr_Client
{

	/**
	 * @var	dropr_Client_Storage
	 */
    private $storage;
    
    private $ipcChannel;

	public function __construct(dropr_Client_Storage_Abstract $storage)
	{
	    $ipcPath       = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'droprIpc' . DIRECTORY_SEPARATOR;
	    $channelName   = $ipcPath . hash('sha1', realpath($storage->getDsn()));
        $this->storage = $storage;

        if (!is_dir($ipcPath)) {
            mkdir($ipcPath, 0777, true);
        }
        if (!is_file($channelName)) {
            if (!posix_mknod($channelName, 0666)) {
            	throw new Exception("could not mknod $channelName!");
            }    
        }
        
        dropr::log("doing ftok($channelName)", LOG_DEBUG);
        $this->ipcChannel = msg_get_queue(ftok($channelName, '*'));
	}

	public function getIpcChannel()
	{
	    return $this->ipcChannel;
	}

    public function putMessage(&$message)
    {
        $messageId = $this->storage->saveMessage($message);

        // notify queue via ipc
        $ipcStat = msg_stat_queue($this->ipcChannel);

        if (is_array($ipcStat) && ($ipcStat['msg_qnum'] < 5)) {

            msg_send($this->ipcChannel, 1, '*', false, false, $ipcError);
        }
        return $messageId;
    }

	/**
	 * @return dropr_Client_Message
	 */
    public function createMessage(
	    &$message = NULL,
	    $peer = NULL,
	    $channel = 'common',
	    $priority = 9,
	    $sync = NULL)
	{
	    return new dropr_Client_Message(
	        $this,
	        $message,
	        $peer,
	        $channel,
	        $priority,
	        $sync
        );
	}

	public function getStorage()
	{
	    return $this->storage;
	}
}
