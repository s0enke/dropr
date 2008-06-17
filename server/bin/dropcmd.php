#!/usr/bin/php
<?php

$_STORAGE = $argv[1];
$_DSN     = $argv[2];

if ('' == $_STORAGE) {
    die("\nERROR: Missing storage type!\n\n");
}
if ('' == $_DSN) {
    die("\nERROR: Missing DSN!\n\n");
}

$_COMMAND = $argv[3];
$_PARAM1  = $argv[4];
$_PARAM2  = $argv[5];

require realpath(dirname(__FILE__) . "/../..") . "/classes/dropr.php";
$storage    = dropr_Server_Storage_Abstract::factory($_STORAGE, $_DSN);


switch ($_COMMAND) {
    case "get_channels":
        echo "\n";
        foreach($storage->getQueuedChannels() as $channel) {
            echo $channel . "\n";
        }
        echo "\n";
    break;

    case "count_queued":
        if ('' == $_PARAM1) {
            die("\nERROR: Missing channel name!\n\n");
        }
        echo $storage->countQueuedMessages($_PARAM1) . "\n";
    break;
    case "list_queued":
        foreach($storage->getQueuedChannels() as $channel) {
            echo $channel . ': ' . $storage->countQueuedMessages($channel) . "\n";
        }
    break;
    case "sum_queued":
        $c = 0;
        foreach($storage->getQueuedChannels() as $channel) {
            $c += (int) $storage->countQueuedMessages($channel) . "\n";
        }
        echo $c . "\n";
    break;
    
    case "count_processed":
        if ('' == $_PARAM1) {
            die("\nERROR: Missing channel name!\n\n");
        }
        echo $storage->countProcessedMessages($_PARAM1) . "\n";
    break;
    case "list_processed":
        foreach($storage->getProcessedChannels() as $channel) {
            echo $channel . ': ' . $storage->countProcessedMessages($channel) . "\n";
        }
    break;
    case "sum_processed":
        $c = 0;
        foreach($storage->getProcessedChannels() as $channel) {
            $c += (int) $storage->countProcessedMessages($channel) . "\n";
        }
        echo $c . "\n";
    break;

    case "wipe_processed":
        if ('' == $_PARAM1) {
            die("\nERROR: Missing channel name!\n\n");
        }
        if ('' == $_PARAM2) {
            die("\nERROR: Missing expiration time in minutes!\n\n");
        }

        $min = (int) $_PARAM2;
        if ($min > 0) {
            echo $storage->wipeSentMessages($min, $_PARAM1) . "\n";
        }
    break;
    case "wipe_all_processed":
        if ('' == $_PARAM1) {
            die("\nERROR: Missing expiration time in minutes!\n\n");
        }

        $min = (int) $_PARAM1;
        if ($min > 0) {
            foreach($storage->getProcessedChannels() as $channel) {
                echo $channel . ': ' . $storage->wipeSentMessages($min, $channel) . "\n";
            }            
        }
    break;

}
