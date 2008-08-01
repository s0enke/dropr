<?php
/**
 * dropr
 *
 * Copyright (c) 2007 - 2008 by the dropr project https://www.dropr.org/
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of dropr nor the names of its
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    dropr
 * @author     Soenke Ruempler <soenke@jimdo.com>
 * @author     Boris Erdmann <boris@jimdo.com>
 * @copyright  2007-2008 Soenke Ruempler, Boris Erdmann
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */

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
