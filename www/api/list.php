<?php

require '../../vendor/autoload.php';
use \Asbestos\Response;

Response::contentType('json');

echo json_encode([
    [
        'name' => 'torrent_0',
        'totalSize' => 123,
        'haveValid' => 0,
        'status' => 'downloading'
    ],
    [
        'name' => 'torrent_1',
        'totalSize' => 1024,
        'haveValid' => 250,
        'status' => 'stopped'
    ]
]);

?>
