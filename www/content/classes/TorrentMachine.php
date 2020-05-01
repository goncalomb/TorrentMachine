<?php

final class TorrentMachine {

    public static function getTransmissionRPC(&$error) {
        $url = getenv('TM_TRANSMISSION_URL');
        if (!$url) {
            $url = 'http://localhost:9091/transmission/rpc';
        }
        $error = null;
        try {
            return new TransmissionRPC($url, null, null, true);
        } catch (TransmissionRPCException $e) {
            $error = $e->getMessage();
            return null;
        }
    }

    public static function doWork() {
        echo "organizing torrents\n";
        TorrentOrganizer::organize();
    }

    private function __construct() { }

}

?>
