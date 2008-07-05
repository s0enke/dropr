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
 *   * Neither the name of Sebastian Bergmann nor the names of his
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

abstract class dropr_Server_Storage_Abstract
{

    const TYPE_STREAM = 1;
    const TYPE_MEMORY = 2;
    const TYPE_FILE   = 3;


    public static function factory($type, $dsn)
    {
        $className = 'dropr_Server_Storage_' . ucfirst($type);
        return new $className($dsn);
    }

    abstract public function getType();

    abstract public function put(dropr_Server_Message $message);

    /**
     * @return bool
     */
    abstract public function pollProcessed($messageId);

    /**
     * @return array
     */
    abstract public function getMessages($type = null, $limit = null);

    /**
     * Sets a message to processed state - the implementation must move it out
     * from the list of active messages to it's not in list of getMessages
     * anymore
     *
     * @param 	pmq_Server_Message $message
     * 
     * @throws 	pmq_Server_Exception
     */
    abstract public function setProcessed(dropr_Server_Message $message);

    abstract public function getQueuedChannels();

    abstract public function getProcessedChannels();

    abstract public function countQueuedMessages($channel = 'common');

    abstract public function countProcessedMessages($channel = 'common');

    abstract public function wipeSentMessages($olderThanMinutes, $channel = 'common');

}
