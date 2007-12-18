#!/usr/bin/php
<?php
ini_set('display_errors', false);

// logging is not completed
#ini_set('log_errors', 1);
#ini_set('error_log', dirname(__FILE__) . '/dropr.log');

require realpath(dirname(__FILE__) . '/..') . '/classes/autoload.php';
/*
 * Config
 */
if (!isset($argv[2])) {
    echo "usage: $argv[0] <storage-type> <storage-dsn>\n";
    exit;
}
$storage    = dropr_Client_Storage_Abstract::factory($argv[1], $argv[2]);
$qInstance  = new dropr_Client($storage);
$ipcChannel = $qInstance->getIpcChannel();


$continue = true;
function handleUser1($sig)
{
    global $continue;
    $continue = false;
}
pcntl_signal(SIGUSR1, 'handleUser1');

function handleAlarm($sig)
{
    return;
}
pcntl_signal(SIGALRM, 'handleAlarm');

$msgCount = 0;
declare(ticks = 1);
while ($continue && ($msgCount < 1000)) {
    unset($queuedMessages, $peerMessages);

    if ($queuedMessages = $storage->getQueuedMessages(1000)) {

        $msgCount += count($queuedMessages, COUNT_RECURSIVE);

        foreach ($queuedMessages as $peerKey => $peerMessages) {
    
            $peer = dropr_Client_Peer_Abstract::getInstance($peerKey);
            try {
                $result = $peer->send($peerMessages, $storage);
            } catch (Exception $e) {
                // something went wrong, lets log it
                //error_log("Caught an Exception while sending messages: " . $e->getMessage());
            }
            $storage->checkSentMessages($peerMessages, $result);
        }
    }
    else {
        pcntl_alarm(4);
        @msg_receive($ipcChannel, 1, $msgType, 512, $msg, true, 0, $msgError);
        pcntl_alarm(0);
    }
}
# msg_remove_queue($qHandle);
