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

// dropr will not work with magic_quotes
if (
    (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) ||
    (ini_get('magic_quotes_sybase') && ('off' != strtolower(ini_get('magic_quotes_sybase'))))) {
    throw new Exception('The dropr server part will not work with magic_quotes enabled. Please disable it in your php configuration.');    
}

abstract class dropr_Server_Transport_Abstract
{
    
    /**
     * Transport gets called for new messages
     */
    const CALL_PUT = 1;
    
    /**
     * Transport gets called for new messages
     */
    const CALL_DIRECT = 2;

    /**
     * Transport gets polled for processed messages
     */
    const CALL_POLL = 3;
    
	/**
     * @var dropr_Server_Storage
     */
    private $storage;
    
    
    private $directInvocationHandlers = array();

    /**
     * @return dropr_Server_Transport_Abstract
     */
    public static function factory($type, dropr_Server_Storage_Abstract $storage)
    {
        $className = 'dropr_Server_Transport_' . ucfirst($type);
        return new $className($storage);
    }
    
    public function addDirectInvocationHandler(dropr_Server_DirectInvocation $handler, $type = null)
    {
        $this->directInvocationHandlers[$type] = $handler;
    }
    
    /**
     * this function is called by the transport layer if a direct message
     * has to be processed.
     * 
     * @throws dropr_Server_Exception
     */
    protected function invoke(dropr_Server_Message $message, $type)
    {
        // check if a handler for this type of message is registered
        if (!isset($this->directInvocationHandlers[$type])) {
            throw new dropr_Server_Exception("No handler set for type '$type'");
        }
        
        /* @var $this->directInvocationHandlers[$type] dropr_Server_DirectInvocation */
        
        return $this->directInvocationHandlers[$type]->invokeMessage($message);
    }
    
    
    
    protected function __construct(dropr_Server_Storage_Abstract $storage)
    {
        $this->storage = $storage;
    }
    
    /**
     * @return dropr_Server_Storage_Abstract
     */
    protected function getStorage()
    {
        return $this->storage;
    }
        
    /**
     * Handles the Server process
     */
    abstract public function handle();
    
}
