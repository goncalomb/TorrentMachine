# TorrentMachine

TorrentMachine is a system to manage and download torrents using [Transmission](https://transmissionbt.com/). It includes a clean Web interface with torrent controls, file tree view and media player.

This is a work in progress.

## Install

For now, the TorrentMachine can be installed on a basic Linux + Apache2 + PHP stack:

* Install Apache2, PHP, transmission-daemon and composer.
* Run `composer install` to download dependencies.
* Run `sudo vendor/bin/asbestos install --port 9000 --indexes --reload` to register a new site and reload apache.
* Run `scripts/transmission.sh` to start the transmission daemon.

The web interface should be available at `http://localhost:9000`.

You can manually configure [mod_auth_digest](http://httpd.apache.org/docs/current/mod/mod_auth_digest.html) to limit access to the web interface, integrated login is not available yet.

## License

TorrentMachine is released under the terms of the GNU General Public License version 3, or (at your option) any later version. See [LICENSE.txt](LICENSE.txt) for details.
