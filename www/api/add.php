<?php

require '../../vendor/autoload.php';

if (empty($_POST['url'])) {
    TorrentMachine::sendJSON(null, 'Empty url.');
} else if ($trpc = TorrentMachine::getTransmissionRPC($error)) {
    $url = $_POST['url'];
    if (preg_match('/^[0-9a-f]{40}$/i', $url)) {
        $url = 'magnet:?xt=urn:btih:' . $url;
    }
    $result = $trpc->add_file($url);
    if ($result['result'] == 'success') {
        if (isset($result['arguments']['torrent-duplicate'])) {
            TorrentMachine::sendJSON(null, 'Duplicate torrent (' . $result['arguments']['torrent-duplicate']['name'] . ').');
        } else {
            TorrentMachine::sendJSON('Torrent added (' . $result['arguments']['torrent-added']['name'] . ').');
        }
    } else {
        TorrentMachine::sendJSON(null, 'Error: ' . $result['result']);
    }
} else {
    TorrentMachine::sendJSON(null, 'Error connecting to Transmission: ' . $error);
}

?>
