#!/usr/bin/php
<?php
require realpath(dirname(__FILE__) . '/..') . '/classes//autoload.php';

if (!isset($argv[2])) {
    echo "usage: $argv[0] <storage-type> <storage-dsn>\n";
    exit;
}

/*
 * Config
 */
$storage = pmq_Client_Storage_Abstract::factory($argv[1], $argv[2]);


while (true) {
    
    if (!$messages = $storage->getRecentMessages()) {
        // wait for ipc signal or sleep
        sleep(1);
        echo "sleeping\n";
    }
    
    $sortedMessages = array();
    foreach ($messages as $message) {
        $sortedMessages[$message->getDsn()][] = $message;
    }
    
    foreach ($sortedMessages as $dsn => $peerMessages) {
        $peer = pmq_Client_Peer_ConnectionPool::getPeer($dsn);
        $result = $peer->put($peerMessages);
        
        // peerMessages as Klasse mit sschalter streamable
    }
    
    unset($messages);
    unset($sortedMessages);
    
}
