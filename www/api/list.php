<?php

require '../../vendor/autoload.php';

$trpc = TorrentMachine::getTransmissionRPC($error);
if ($trpc) {
    $result = $trpc->get([], [
        'id', 'hashString', 'name', 'status', 'eta',
        'haveValid', 'totalSize',
        'rateDownload', 'rateUpload',
        'peersConnected', 'maxConnectedPeers', 'peersGettingFromUs', 'peersSendingToUs',
        'error', 'errorString'
    ])['arguments'];
    if (isset($result['torrents'])) {
        $torrents = $result['torrents'];
        foreach ($torrents as &$data) {
            $data['statusString'] = $trpc->getStatusString($data['status']);
        }
        TorrentMachine::sendJSON($torrents);
    } else {
        TorrentMachine::sendJSON([]);
    }
} else {
    TorrentMachine::sendJSON(null, 'Error connecting to Transmission: ' . $error);
}

?>
