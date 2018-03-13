<?php

use \Asbestos\Asbestos;

class PageController {

    public static function fs() {
        Asbestos::startThemedPage();
?>
<h2>Files</h2>
<div id="downloads-listing"></div>
<script>window.createDownloadsListing('#downloads-listing');</script>
<?php
    }

    public static function tvshows() {
        Asbestos::startThemedPage();
?>
<h2>TVShows</h2>
<div id="tvshows"></div>
<script>window.createTVShowsList('#tvshows');</script>
<?php
    }

    private function __construct() { }

}

?>
