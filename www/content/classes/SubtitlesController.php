<?php

use \Asbestos\Http;
use \Asbestos\Response;

final class SubtitlesController {

    public static function get() {
        if (empty($_GET['name'])) {
            Response::contentType('plain', 400);
            echo "Invalid 'name' parameter.\n";
        } else {
            Subtitles\SubtitlesProvider::serveBestSubtitleForRelease($_GET['name']);
        }
    }

    private function __construct() { }

}

?>
