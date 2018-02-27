<?php

use \Asbestos\Routing\Router;

Router::match('GET,POST', '/api/filesystem/?', ['ApiFilesystemController', 'handle']);
Router::match('GET,POST', '/api/transmission/?', ['ApiTransmissionController', 'handle']);
Router::match('GET', '/subtitles/?', ['SubtitlesController', 'get']);

?>
