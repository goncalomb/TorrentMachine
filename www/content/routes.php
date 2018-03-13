<?php

use \Asbestos\Routing\Router;

Router::match('GET', '/fs/?', ['PageController', 'fs']);
Router::match('GET', '/tvshows/?', ['PageController', 'tvshows']);

Router::match('GET,POST', '/api/filesystem/?', ['ApiFilesystemController', 'handle']);
Router::match('GET,POST', '/api/transmission/?', ['ApiTransmissionController', 'handle']);
Router::match('GET', '/subtitles/?', ['SubtitlesController', 'get']);

?>
