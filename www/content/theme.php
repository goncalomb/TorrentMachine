<?php

use \Asbestos\Page;

Page::metaTag('viewport', 'width=device-width, initial-scale=1');

Page::title("Torrent Machine");

Page::stylesheetFile('https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css');
Page::scriptFile('https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.slim.min.js');
Page::scriptFile('https://cdn.jsdelivr.net/npm/vue@2.5.13/dist/vue.min.js');

Page::stylesheetFile('/static/main.css');
Page::scriptFile('/static/main.js');

?>

<nav class="navbar navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="/">Torrent Machine</a>
    </div>
</nav>

<?php

Page::createZone('main', 'main')->attribute('class', 'container');
Page::setOutputZone('main');

?>
