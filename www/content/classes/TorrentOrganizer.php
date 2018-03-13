<?php

final class TorrentOrganizer {

    public static function organize() {
        $trpc = TorrentMachine::getTransmissionRPC($error);
        if ($trpc) {
            $result = $trpc->get([], [ 'name', 'isFinished', 'files' ])['arguments'];
            if (isset($result['torrents'])) {
                $tvshows = new TVShowCollection();
                foreach ($result['torrents'] as $torrent) {
                    foreach ($torrent['files'] as $file) {
                        $tvshows->tryAddFile($file['name'], ($file['bytesCompleted'] == $file['length']));
                    }
                }
                file_put_contents(ASBESTOS_ROOT_DIR . '/files/tvshows.json', $tvshows->getJSON());
            }
        }
    }

    private function __construct() { }

}

?>
