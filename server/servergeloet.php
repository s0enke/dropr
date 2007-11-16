<?php

$destination = '/home/erdmann/webs/test/queue/server/in/';

header('Content-Type: text/plain');

foreach ($_FILES as $k => $fStruct) {
    $_FILES[$k]['inqueue'] = true;

#   continue;

    $_FILES[$k]['inqueue'] = move_uploaded_file(
        $fStruct['tmp_name'],
        $destination.$fStruct['name']
    );

}

print(serialize($_FILES));