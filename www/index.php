<?php

require '../vendor/autoload.php';
use \Asbestos\Asbestos;

Asbestos::startRouting(true);

Asbestos::startThemedPage();

?>

<h2>Torrents</h2>
<div id="torrent-add-form"></div>
<script>window.createTorrentAddForm('#torrent-add-form');</script>
<div id="torrent-list"></div>
<script>window.createTorrentList('#torrent-list');</script>
