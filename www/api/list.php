<?php

require '../../vendor/autoload.php';

$trpc = TorrentMachine::getTransmissionRPC($error);
if ($trpc) {
    TorrentMachine::sendJSON($trpc->get()->arguments->torrents);
} else {
    TorrentMachine::sendJSON(null, 'Error connecting to Transmission: ' . $error);
}

?>
