#!/usr/bin/php
<?php

$dt = time();

$i=0;
while ($i < 1000) {
    $m = createMessage(10000);
    putToQueue($m);
    $i++;
}

$dt = time() - $dt;
echo $dt."\n";

function createMessage($len,
    $chars = '0123456789 ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz')
{
    $charsSize = strlen($chars)-1;
    $string = '';
    for ($i = 0; $i < $len; $i++)
    {
        $pos = rand(0, $charsSize);
        $string .= $chars{$pos};
    }
    return $string;
}

function getMsgId() {
    $tName = (string)microtime();
    $spPos = strpos($tName, ' ');
    return substr($tName, $spPos+1).'-'.substr($tName, 2, $spPos-2);
}

function putToQueue($rawMessage) {
    $temp = 'queue/client/in/';
    $proc = 'queue/client/proc/';

    $badName = true;
    while ($badName) {
        $msgId = getMsgId();
        $fName = $temp . $msgId;
        $fh = @fopen($fName,'x');
        $badName = ($fh === false);
        if ($badName) {
            echo '*';
        }
    }
    $message = array(
        "mId" => $msgId,
        "msg" => $rawMessage
    );

    fwrite($fh, serialize($message));
    fclose($fh);

    rename($temp.$msgId, $proc.$msgId);
}
