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

class dropr_Client_Peer_HttpUpload extends dropr_Client_Peer_Abstract
{
    const TYPE_QUEUE = 1;
    
    const TYPE_DIRECT = 2;
    
    public function send(array &$messages)
    {
        return $this->httpFileUpload($messages, self::TYPE_QUEUE);
    }

    /**
     * Upload Messages to peer
     *
     * @param array $messages
     * @param int $type     This is the TYPE_QUEUE or TYPE_DIRECT
     * @return array    The response from the server
     */
    private function httpFileUpload(array &$messages, $type) {

        $uploadFields = array();
        
        // set the client name
        // XXX: geloet
        $uploadFields['client'] = trim(`hostname`);
        
        $metaData = array();
        foreach ($messages as $k => $message) {
            /* @var $message dropr_Client_Message */
            
            $metaData[$k] = array(
                'message'       => 'f_' . $k,
                'messageId'     => $message->getId(),
                'channel'		=> $message->getChannel(),
                'priority'      => $message->getPriority(),
                'messageType'	=> $type,
            );
            
            $content = &$message->getMessage();
            if ($content instanceof SplFileInfo) {
                // the message is a file, we can use curl directly
                $uploadFields['f_' . $k] = '@' . $content->getPathname();
            } elseif (is_string($content)) {
                // message content is a string, we've to build a tempfile first
                if (!$filename = tempnam('/tmp', 'dropr')) {
                    throw new Exception();
                }
                if (!file_put_contents($filename, $content)) {
                    throw new Exception();
                }
            } else {
                throw new dropr_Client_Exception("Currently only file transport is implemented.");
            }
        }
        
        $uploadFields['metaData'] = serialize($metaData);
        
        // ACHTUNG: extremes Geloet!!!

        $connomains = array(
            $this->getUrl()
        );
        
        $mh = curl_multi_init();

        foreach ($connomains as $i => $url) {
            $conn[$i] = curl_init();
            
            // set the timeout to 60 seconds
            curl_setopt($conn[$i], CURLOPT_TIMEOUT, 60);
            curl_setopt($conn[$i], CURLOPT_URL, $url);
            curl_setopt($conn[$i], CURLOPT_POST, true);
            curl_setopt($conn[$i], CURLOPT_POSTFIELDS, $uploadFields);
            
            //Set the expect header empty as workaround for cURL Bug
            //Bug description: cURL adds Expect header if POST size is greater
            //then 1024. Most Servers (e.x. lighttpd) do not understand this header
            //and fail on request.
            //Ticket #42 on dropr.org
            curl_setopt($conn[$i], CURLOPT_HTTPHEADER, array('Expect:'));
            
            curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
            
            curl_multi_add_handle ($mh,$conn[$i]);
        }
        
        // start performing the request
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        
        while ($active and $mrc == CURLM_OK) {
            // wait for network
            if (curl_multi_select($mh) != -1) {
                // pull in any new data, or at least handle timeouts
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        
        if ($mrc != CURLM_OK) {
            throw new dropr_Client_Exception("Curl multi read error $mrc");
        }
        
        // retrieve data
        foreach ($connomains as $i => $url) {
            if (($err = curl_error($conn[$i])) == '') {
                $res[$i]=curl_multi_getcontent($conn[$i]);
            } else {
                throw new dropr_Client_Exception("Curl error on handle $i: $err");
            }
            curl_multi_remove_handle($mh,$conn[$i]);
            curl_close($conn[$i]);
        }
        curl_multi_close($mh);
        
        #echo $res[0];
        
        if (!$return = unserialize($res[0])) {
            // XXX NO exception but return false and LOG!!
            throw new dropr_Client_Exception("Could not unserialize the result!");;
        }
        
        return $return;
    }
    
    /**
     * synchronous message transfer
     * 
     * this is not implemented completely
     *
     * @param dropr_Client_Message $message
     * @return bool
     */
    public function sendDirectly(dropr_Client_Message $message)
    {
        return $this->httpFileUpload($messages, self::TYPE_DIRECT);
    }
}
