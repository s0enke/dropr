#!/usr/bin/php
<?php
/**
 * dropr
 *
 * Copyright (c) 2008, by the dropr project https://www.dropr.org/
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

/**
 * this is the dropr main client queue process
 * 
 * @author Soenke Ruempler
 * @author Boris Erdmann
 */
ini_set('display_errors', false);
declare(ticks = 1);

require realpath(dirname(__FILE__) . '/../..') . '/classes/dropr.php';

/*
 * Config:
 * - storage-type
 * - storage-dsn
 */
if (!isset($argv[2])) {
    echo "usage: $argv[0] <storage-type> <storage-dsn>\n";
    exit(1);
}


/*
 * setting the dropr logging to syslog
 */
//syslog(LOG_DEBUG, "log level from command line is " . $argv[3]);
if (!isset($argv[3]) || !($logLevel = constant('LOG_' . $argv[3]))) {
    // use default (INFO)
    $logLevel = LOG_INFO;

}

dropr::setLogLevel($logLevel);
dropr::log("logLevel is $logLevel", LOG_DEBUG);

dropr::log("********************************************************************", LOG_INFO);
dropr::log("Starting up with que type $argv[1] and DSN $argv[2]", LOG_INFO);
dropr::log("********************************************************************", LOG_INFO);

try {
    $storage    = dropr_Client_Storage_Abstract::factory($argv[1], $argv[2]);
    $qInstance  = new dropr_Client($storage);
    $ipcChannel = $qInstance->getIpcChannel();
} catch (Exception $e) {
    dropr::log("Could not startup: {$e->getMessage()} - sleeping 5 seconds and then exiting ...", LOG_ERR);
    sleep(5);
    exit(1);
}

$continue = true;


dropr::log("Startup ok!", LOG_DEBUG);

// todo: make this configurable!!
$sleepTimeout = 20;
$peerTimeout  = 60;
$maxMessagesPerLife = 10000;
$maxMessagesPerSend = 1000;
$peerKeyBlackList = array();

$msgCount = 0;


function handleKill($sig)
{
    global $continue;
    $continue = false;
}

/*
 * register the signal handlers
 *
 * we try to cleanup and stop gracefully in any case
 * (SIGUSR1 is the default kill signal used by "droprd" angel
 */
pcntl_signal(SIGUSR1, 'handleKill');
pcntl_signal(SIGTERM, 'handleKill');
pcntl_signal(SIGINT,  'handleKill');
pcntl_signal(SIGALRM, 'handleKill');


while ($continue && ($msgCount < $maxMessagesPerLife)) {

    unset($queuedMessages, $peerMessages);

    if ($queuedMessages = $storage->getQueuedMessages($maxMessagesPerSend, $peerKeyBlackList)) {
    
        dropr::log("got " . count($queuedMessages, COUNT_RECURSIVE) . " messages from the storage", LOG_DEBUG);

        foreach ($queuedMessages as $peerKey => $peerMessages) {
        	
            /*
             * count the messages regardless they can be sent or not
             */
            $msgCount += count($peerMessages, COUNT_RECURSIVE);
            try {
                $peer = dropr_Client_Peer_Abstract::getInstance($peerKey);
                dropr::log("trying to send messages to peer `" . $peer->getUrl() . "' via method `" . $peer->getTransportMethod() . "'", LOG_DEBUG);
                $result = $peer->send($peerMessages, $storage);
                $storage->checkSentMessages($peerMessages, $result);
                dropr::log("successfully sent messages to the peer!", LOG_DEBUG);
            } catch (Exception $e) {
                $peerKeyBlackList[$peerKey] = time() + $peerTimeout;
                dropr::log("could not sent messages to peer {$peer->getUrl()}- message was: {$e->getMessage()}  - blacklisting the peer for $peerTimeout seconds!");
            }
        }
    } else {
        dropr::log("nothing to do. going to sleep.", LOG_DEBUG);
        pcntl_alarm($sleepTimeout);
        @msg_receive($ipcChannel, 1, $msgType, 512, $msg, true, 0, $msgError);
        dropr::log("woke up from sleep - checking for messages ...", LOG_DEBUG);
        pcntl_alarm(0);
    }
}

if ($continue) {
    dropr::log("Restarting after sending $maxMessagesPerLife messages into the world.", LOG_INFO);
} else {
    dropr::log("Cleaning up and terminating on request. Goodbye.", LOG_INFO);
}
