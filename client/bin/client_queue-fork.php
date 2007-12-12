#!/usr/bin/php
<?php
require realpath(dirname(__FILE__) . '/..') . '/classes//autoload.php';

if (!isset($argv[2])) {
    echo "usage: $argv[0] <storage-type> <storage-dsn>\n";
    exit;
}

$time = time();

/*
 * Config
 */
$storage = dropr_Client_Storage_Abstract::factory($argv[1], $argv[2]);

// huh? does it work?
posix_setsid();

// fork the sender queue
$pid = pcntl_fork();

if ($pid == -1) {
    die("could not fork\n");
} else if ($pid) {
     // we are the parent

    // fork the second daemon for polling
    /*
    $pollPid = pcntl_fork();
    if ($pollPid == -1) {
        die("could not fork\n");
    } else if ($pollPid) {
         // we are the parent
    } else {
        // we are the child - main loop for sending messages
        echo 'polling!' . getmypid() . "\n";
        sleep(100);
    }*/
    
} else {
     // we are the child - main loop for sending messages
    sendMessages($storage);
    

}

function sendMessages(dropr_Client_Storage_Abstract $storage)
{
    
    #echo getmypid() . "\n";
    
    while (true) {
        
        while (!$queuedMessages = $storage->getQueuedMessages(1000)) {
            // wait for ipc signal or sleep
            sleep(1);
        }
        
        foreach ($queuedMessages as $peerKey => $peerMessages) {
    
            #echo "Messages: " . count($peerMessages) . "\n";
        
            $peer = dropr_Client_Peer_Abstract::getInstance($peerKey);
            $result = $peer->send($peerMessages, $storage);
    
            #echo "have sent queue: " . (time() - $time) . "\n";
            $storage->checkSentMessages($peerMessages, $result);
        }
    
        unset($queuedMessages, $peerMessages);
        #break;
    }
    
}

function pollMessages(dropr_Client_Storage_Abstract $storage)
{
    
    while (true) {
        
        while (!$queuedMessages = $storage->get(1000)) {
            // wait for ipc signal or sleep
            echo "sleeping\n";
            sleep(1);
        }
        
        echo "got queue messages: " . (time() - $time) . "\n";
    
    
        foreach ($queuedMessages as $peerKey => $peerMessages) {
    
            echo "Messages: " . count($peerMessages) . "\n";
        
            $peer = dropr_Client_Peer_Abstract::getInstance($peerKey);
            $result = $peer->send($peerMessages, $storage);
    
            echo "have sent queue: " . (time() - $time) . "\n";
            $storage->checkSentMessages($peerMessages, $result);
        }
    
        unset($queuedMessages, $peerMessages);
        #break;
    }
    
}
