<?php

use \Asbestos\Response;

class ApiController {

    protected static function sendJSON($data, $error=null) {
        Response::contentType('json');
        echo json_encode(['data' => $data, 'error' => $error]);
    }

    private function __construct() { }

}

?>
