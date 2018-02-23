<?php

final class ApiFilesystemController extends ApiController {

    public static function handle($params) {
        $action = (isset($_GET['action']) ? $_GET['action'] : '');
        $fn = [__CLASS__, 'action_' . str_replace('-', '_', $action)];
        if (method_exists($fn[0], $fn[1])) {
            call_user_func($fn);
        } else {
            static::sendJSON(null, 'API Error: Invalid \'action\' parameter.');
        }
    }

    private static function mergePath($root, $path) {
        $root = realpath($root);
        if (!$root) {
            return false;
        }
        $parts = [];
        foreach (preg_split('/[\\\\\\/]+/', $path) as $part) {
            if ($part == '.' || $part == '..') {
                return false;
            }
            $parts[] = $part;
        }
        return realpath($root . '/' . implode('/', $parts));
    }

    private static function action_list() {
        if (!isset($_POST['path'])) {
            static::sendJSON(null, 'API Error: Invalid \'path\' parameter.');
            return;
        }

        $files_dir = realpath(ASBESTOS_ROOT_DIR . DIRECTORY_SEPARATOR . 'files');
        $path_full = self::mergePath($files_dir, $_POST['path']);

        if ($path_full && is_dir($path_full)) {
            $result = [];
            $handle = opendir($path_full);
            while (($entry = readdir($handle)) !== false) {
                if ($entry == '.' || $entry == '..') continue;
                $path = $path_full . DIRECTORY_SEPARATOR . $entry;
                if (is_dir($path)) {
                    $result[] = ['type' => 'dir', 'name' => $entry, 'size' => -1];
                } else if (is_file($path)) {
                    $result[] = ['type' => 'file', 'name' => $entry, 'size' => filesize($path)];
                }
            }
            static::sendJSON($result);
        } else {
            static::sendJSON(null, 'Path not found.');
        }
    }

}

?>
