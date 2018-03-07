<?php

require '../vendor/autoload.php';
use \Asbestos\Asbestos;

Asbestos::startRouting(true);

Asbestos::startThemedPage();

?>

<div id="media-player"></div>
<script>window.createMediaPlayer('#media-player')</script>

<h2>Files</h2>
<div id="downloads-listing"></div>
<script>window.createDownloadsListing('#downloads-listing');</script>

<h2>Torrents</h2>
<div id="torrent-add-form"></div>
<script>window.createTorrentAddForm('#torrent-add-form');</script>
<div id="torrent-list"></div>
<script>window.createTorrentList('#torrent-list');</script>
