#!/usr/bin/php
<?php
/**
 * this is the dropr main client queue process
 * 
 * @author Soenke Ruempler
 * @author Boris Erdmann
 */
ini_set('display_errors', false);

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

pcntl_signal(SIGALRM, 'handleAlarm');

dropr::log("Startup ok!", LOG_DEBUG);

// todo: make this configurable!!
$sleepTimeout = 20;
$peerTimeout  = 60;
$maxMessagesPerLife = 10000;
$maxMessagesPerSend = 100;
$peerKeyBlackList = array();

$msgCount = 0;
declare(ticks = 1);
while ($continue && ($msgCount < $maxMessagesPerLife)) {

    unset($queuedMessages, $peerMessages);

    if ($queuedMessages = $storage->getQueuedMessages($maxMessagesPerSend, $peerKeyBlackList)) {
    
        dropr::log("got " . count($queuedMessages, COUNT_RECURSIVE) . " messages from the storage", LOG_DEBUG);

        foreach ($queuedMessages as $peerKey => $peerMessages) {
        	
            try {
                $peer = dropr_Client_Peer_Abstract::getInstance($peerKey);
                dropr::log("trying to send messages to peer `" . $peer->getUrl() . "' via method `" . $peer->getTransportMethod() . "'", LOG_DEBUG);
                $result = $peer->send($peerMessages, $storage);
                $msgCount += count($peerMessages, COUNT_RECURSIVE);
                $storage->checkSentMessages($peerMessages, $result);
                dropr::log("successfully sent messages to the peer!");
            } catch (Exception $e) {
                $peerKeyBlackList[$peerKey] = time() + $peerTimeout;
                dropr::log("could not sent messages to peer - message was: " . $e->getMessage() . " - blacklisting the peer for $peerTimeout seconds!");
            }
        }
    }
    else {
        dropr::log("nothing to do. going to sleep.", LOG_DEBUG);
        pcntl_alarm($sleepTimeout);
        @msg_receive($ipcChannel, 1, $msgType, 512, $msg, true, 0, $msgError);
        dropr::log("woke up from sleep - checking for messages ...", LOG_DEBUG);
        pcntl_alarm(0);
    }
}

dropr::log("Restarting after sending $maxMessagesPerLife messages into the world.", LOG_INFO);

# msg_remove_queue($qHandle);

function handleUser1($sig)
{
    global $continue;
    $continue = false;
}

function handleAlarm($sig)
{
    return;
}

function handleKill($sig)
{
	// XXX cleanup!
	// XXX this does not work..
    dropr::log("Terminating on request ($sig). Goodbye.", LOG_INFO);
    exit;	
}

pcntl_signal(SIGUSR1, 'handleUser1');
pcntl_signal(SIGTERM, 'handleKill');
pcntl_signal(SIGKILL, 'handleKill');
