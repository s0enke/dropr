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
$storage = pmq_Client_Storage_Abstract::factory($argv[1], $argv[2]);

$i = 0;
while (true) {
    
    while (!$queuedMessages = $storage->getQueuedMessages(2000)) {
        // wait for ipc signal or sleep
        echo "sleeping\n";
        sleep(1);
    }
    
    echo "got queue messages: " . (time() - $time) . "\n";


    foreach ($queuedMessages as $peerKey => $peerMessages) {

        echo "Messages: " . count($peerMessages) . "\n";
    
        $i += count($peerMessages);
        
        $peer = pmq_Client_Peer_Abstract::getInstance($peerKey);
        $result = $peer->send($peerMessages, $storage);

        echo "have sent queue: " . (time() - $time) . "\n";
        $storage->checkSentMessages($peerMessages, $result);
    }

    unset($queuedMessages);
    #break;
}

echo "Messages: $i\n";
echo "Time: " . (time() - $time) . "\n";

