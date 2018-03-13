<?php

final class TorrentMachine {

    public static function getTransmissionRPC(&$error) {
        $error = null;
        try {
            return new TransmissionRPC('http://localhost:9100/transmission/rpc', null, null, true);
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
