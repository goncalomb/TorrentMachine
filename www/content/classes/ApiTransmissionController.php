<?php

final class ApiTransmissionController extends ApiController {

    public static function handle($params) {
        $action = (isset($_GET['action']) ? $_GET['action'] : '');

        if ($action == 'torrent-start' || $action == 'torrent-stop' || $action == 'torrent-verify' || $action == 'torrent-reannounce') {
            $fn = [__CLASS__, 'torrent_action_request'];
        } else {
            $fn = [__CLASS__, 'action_' . str_replace('-', '_', $action)];
        }

        if (method_exists($fn[0], $fn[1])) {
            if ($trpc = TorrentMachine::getTransmissionRPC($error)) {
                call_user_func($fn, $action, $trpc);
            } else {
                static::sendJSON(null, 'Transmission Error: ' . $error);
            }
        } else {
            static::sendJSON(null, 'API Error: Invalid \'action\' parameter.');
        }
    }

    private static function torrent_action_request($action, $trpc) {
        if (empty($_POST['ids'])) {
            static::sendJSON(null, 'API Error: Invalid \'ids\' parameter.');
        } else {
            $result = call_user_func([$trpc, substr($action, 8)], array_map('intval', explode(',', $_POST['ids'])));
            if ($result['result'] == 'success') {
                static::sendJSON(null);
            } else {
                static::sendJSON(null, 'Error: ' . $result['result']);
            }
        }
    }

    private static function action_torrent_get($action, $trpc) {
        $result = $trpc->get([], [
            'id', 'hashString', 'name', 'status', 'eta',
            'haveValid', 'totalSize',
            'rateDownload', 'rateUpload',
            'recheckProgress',
            'peersConnected', 'maxConnectedPeers', 'peersGettingFromUs', 'peersSendingToUs',
            'error', 'errorString'
        ])['arguments'];
        if (isset($result['torrents'])) {
            $torrents = $result['torrents'];
            foreach ($torrents as &$data) {
                $data['statusString'] = $trpc->getStatusString($data['status']);
            }
            static::sendJSON($torrents);
        } else {
            static::sendJSON([]);
        }
    }

    private static function action_torrent_add($action, $trpc) {
        if (empty($_POST['url'])) {
            static::sendJSON(null, 'API Error: Invalid \'url\' parameter.');
        } else {
            $url = $_POST['url'];
            if (preg_match('/^[0-9a-f]{40}$/i', $url)) {
                $url = 'magnet:?xt=urn:btih:' . $url;
            }
            $result = $trpc->add_file($url);
            if ($result['result'] == 'success') {
                if (isset($result['arguments']['torrent-duplicate'])) {
                    static::sendJSON(null, 'Duplicate torrent (' . $result['arguments']['torrent-duplicate']['name'] . ').');
                } else {
                    static::sendJSON('Torrent added (' . $result['arguments']['torrent-added']['name'] . ').');
                }
            } else {
                static::sendJSON(null, 'Error: ' . $result['result']);
            }
        }
    }

    private static function action_torrent_remove($action, $trpc) {
        if (empty($_POST['ids'])) {
            static::sendJSON(null, 'API Error: Invalid \'ids\' parameter.');
        } else {
            $result = $trpc->remove(array_map('intval', explode(',', $_POST['ids'])), true);
            if ($result['result'] == 'success') {
                static::sendJSON(null);
            } else {
                static::sendJSON(null, 'Error: ' . $result['result']);
            }
        }
    }

}

?>
