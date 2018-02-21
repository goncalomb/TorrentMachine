<?php

use \Asbestos\Response;

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

    public static function sendJSON($data, $error=null) {
        Response::contentType('json');
        echo json_encode(['data' => $data, 'error' => $error]);
    }

    private function __construct() { }

}

?>
