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


abstract class dropr_Client_Peer_Abstract
{
    private static $instances = array();

    private $transportMethod;
    private $peerUrl;
    private $key;

    /**
     * Singleton-Factory for Peer-Instances
     *
     * @param string $method
     * @param string $url
     * @return dropr_Client_Peer_Abstract
     */
    public static function getInstance($method, $url = false)
    {
        if ($url === false) {
            if (!list($method, $url) = explode(';', $method)) {
                throw new dropr_Client_Exception("Could not explode method and url from '$method'");
            }
        }

        $key = $method . ';' . $url;
        if (!isset(self::$instances[$key])) {
            // Guess the classname from transport method
            $className = 'dropr_Client_Peer_' . ucfirst($method);
            self::$instances[$key] = new $className($method, $url);
        }

        return self::$instances[$key];
    }
    
    protected function __construct($method, $url)
    {
        $this->transportMethod = $method;
        $this->peerUrl = $url;
        $this->key = $method.';'.$url;
    }

    public function getTransportMethod()
    {
        return $this->transportMethod;
    }

    public function getUrl()
    {
        return $this->peerUrl;
    }

    public function getKey() {
        return $this->key;
    }

    abstract public function send(array &$messages);    

    /**
     * sends a message directly and give feedback immediately
     * 
     * this isn't implemented for now ...
     * 
     * @return string	the answer
     * @throws	dropr_Client_Exception	If something went wrong 
     */
    abstract public function sendDirectly(dropr_Client_Message $message);
}
