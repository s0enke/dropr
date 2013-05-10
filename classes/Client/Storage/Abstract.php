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

abstract class dropr_Client_Storage_Abstract
{
    const TYPE_STREAM = 1;
    const TYPE_MEMORY = 2;
    const TYPE_FILE   = 3;

    private static $instances = array();
    
    private $dsn;
    
    public static function factory($type, $dsn)
    {
        if (!isset(self::$instances[$dsn])) {
            // Guess the classname from the dsn
            $className = 'dropr_Client_Storage_' . ucfirst($type);
            self::$instances[$dsn] = new $className($dsn);
            self::$instances[$dsn]->dsn = $dsn;
        }
        
        return self::$instances[$dsn];
    }

    protected function __construct($dsn)
    {
        $this->dsn = $dsn;
    }

    public function getDsn()
    {
        return $this->dsn;
    }
    
	/**
     * @return int	identifier of the message in queue
     */
    abstract public function saveMessage(dropr_Client_Message $message);
    
    /**
     * returns the most recent messages  out of the storage ordered by
     * priority and create-time
     * 
     * @return array	An array of dropr_Client_Message objects 
     */
    abstract public function getQueuedMessages($limit = null, &$peerKeyBlackList = null);
    
    /**
     * @return dropr_Client_Message
     */
    abstract public function getMessage($messageId, dropr_Client_Peer_Abstract $peer);
    
    abstract public function getType();

    abstract public function checkSentMessages(array &$messages, array &$result);

    abstract public function countQueuedMessages();

    abstract public function countSentMessages();

    abstract public function wipeSentMessages($olderThanMinutes);
}
