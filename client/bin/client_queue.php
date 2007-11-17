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
    
    while (!$queuedMessages = $storage->getQueuedMessages()) {
        // wait for ipc signal or sleep
        echo "sleeping\n";
        sleep(1);
    }

    #var_dump($queuedHandles);

    foreach ($queuedMessages as $peerKey => $peerMessages) {

        $peer = pmq_Client_Peer_Abstract::getInstance($peerKey);
        $result = $peer->send($peerMessages, $storage);

        $storage->checkSentHandles($peer, $peerMessages, $result);
    }

    unset($queuedHandles);
}
