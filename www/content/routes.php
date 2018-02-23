<?php

use \Asbestos\Routing\Router;

Router::match('GET,POST', '/api/transmission/?', ['ApiTransmissionController', 'handle']);

?>
