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
        if ($mHandle instanceof SplFileInfo || is_string($mHandle)) {
            // xxx auslagern in eigene funktion

            $proc = $this->buildMessagePath($message, self::SPOOLDIR_TYPE_SPOOL);
            $done = $this->buildMessagePath($message, self::SPOOLDIR_TYPE_PROCESSED);
            
            if (file_exists ($proc) || file_exists ($done)) {
                // the message has already been stored
                // XXX write test!
                return;
            }
            
            if ($mHandle instanceof SplFileInfo) {
                // handle is a file, move it
                $src = $mHandle->getPathname();
    
                // sometimes php throws a warning but returns true and the file is moved
                // .. maybe NFS issue so we have to use the @-operator
                if (!@rename($src, $proc)) {
                    throw new dropr_Server_Exception("Could not save $src to $proc");
                }
            } elseif (is_string($mHandle)) {
                if (!file_put_contents($proc, $mHandle)) {
                    throw new dropr_Server_Exception("Could not write content to $proc!");
                }
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

    private function getChannels($dir)
    {
        $channels = scandir($dir);

        foreach (array('.', '..') as $del) {
            $key = array_search($del, $channels, true);
            if (false !== $key) {
                array_splice($channels, $key, 1);
            }
        }

        foreach ($channels as $k => $channel) {
            $channels[$k] = base64_decode($channel);
        }

        return $channels;
    }

    public function getQueuedChannels()
    {
        $spoolPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_SPOOL, '');
        return $this->getChannels($spoolPath);
    }

    public function getProcessedChannels()
    {
        $spoolPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_PROCESSED, '');
        return $this->getChannels($spoolPath);
    }

    public function countQueuedMessages($channel = 'common')
    {
        $spoolPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_SPOOL, $channel) . DIRECTORY_SEPARATOR;
        return count(scandir($spoolPath))-2;
    }

    public function countProcessedMessages($channel = 'common')
    {
        $spoolPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_PROCESSED, $channel) . DIRECTORY_SEPARATOR;
        return count(scandir($spoolPath))-2;
    }

    public function wipeSentMessages($olderThanMinutes, $channel = 'common')
    {
        $spoolPath = $this->getSpoolPath(self::SPOOLDIR_TYPE_PROCESSED, $channel) . DIRECTORY_SEPARATOR;
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
