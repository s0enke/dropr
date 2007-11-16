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
    
    while (!$queuedHandles = $storage->getQueuedHandles()) {
        // wait for ipc signal or sleep
        echo "sleeping\n";
        sleep(1);
    }
    
    foreach ($queuedHandles as $peerDsn => $peerHandles) {
        if (!list($method, $url) = explode(';', $peerDsn)) {
            echo "Peer $peer is obstructed!\n";
            continue;
        }
        $peer = pmq_Client_Peer_Abstract::getInstance($method, $url);
        $result = $peer->send($peerHandles, $storage);

        $storage->checkSentHandles($result, $peerDsn);
    }
    
    unset($queuedHandles);
}
