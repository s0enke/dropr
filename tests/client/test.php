<?php
require '../../client/classes/autoload.php';		

$storage = dropr_Client_Storage_Abstract::factory('filesystem', '/home/soenke/droprclientqueue');
$queue = new dropr_Client($storage);

$peer = dropr_Client_Peer_Abstract::getInstance('HttpUpload', 'http://soenkedroprserver/server/server.php');

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
