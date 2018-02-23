<?php

require '../vendor/autoload.php';
use \Asbestos\Asbestos;

Asbestos::startRouting(true);

Asbestos::startThemedPage();

?>

<h2>Files</h2>
<div id="downloads-listing"></div>
<script>window.downloadsListing(document.getElementById('downloads-listing'))</script>

<h2>Torrrents</h2>
<div id="torrent-add-form"></div>
<script>window.createTorrentAddForm(document.getElementById('torrent-add-form'))</script>
<div id="torrent-list"></div>
<script>window.createTorrentList(document.getElementById('torrent-list'))</script>
