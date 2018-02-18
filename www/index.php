<?php

require '../vendor/autoload.php';
use \Asbestos\Asbestos;

Asbestos::startThemedPage();

?>

<h2>Torrent List</h2>
<div id="torrent-list"></div>
<script>window.createTorrentList(document.getElementById('torrent-list'))</script>
