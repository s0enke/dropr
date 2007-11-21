<?php
require '../../client/classes/autoload.php';		

$storage = pmq_Client_Storage_Abstract::factory('filesystem', '/home/soenke/pmqclientqueue');
$queue = new pmq_Client($storage);

$peer = pmq_Client_Peer_Abstract::getInstance('HttpUpload', 'http://soenkepmqserver/server/server.php');

$dt = time();
$i=0;

$m = json_encode(array("ich bin eine test message von " . date("H:i:s"), 'wurstarrayarray'));

while ($i < 10000) {

    $msg = $queue->createMessage($m, $peer);
    $msg->queue();
    $i++;
    echo '.';
}

echo "\n";