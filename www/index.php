<?php

require '../vendor/autoload.php';
use \Asbestos\Asbestos;

Asbestos::startRouting(true);

Asbestos::startThemedPage();

?>

<h2>Files</h2>
<div id="downloads-listing"></div>
<script>window.downloadsListing(document.getElementById('downloads-listing'))</script>

<h2>Torrents<small class="float-right mt-2"><a class="small" href="/files/transmission-log.txt">Transmission Log</a></small></h2>
<div id="torrent-add-form"></div>
<script>window.createTorrentAddForm(document.getElementById('torrent-add-form'))</script>
<div id="torrent-list"></div>
<script>window.createTorrentList(document.getElementById('torrent-list'))</script>
