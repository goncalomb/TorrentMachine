<?php

final class ReleaseInfo {

    public static function parseName($name) {
        if (preg_match('/^(.+)[\. ](\d{4})[\. ](.*)-(.+?)(\[.+\])?(\.\w+)?$/i', $name, $matches)) {
            return [
                'type' => 'movie',
                'name' => trim(str_replace('.', ' ', $matches[1])),
                'year' => $matches[2],
                'tags' => $matches[3],
                'group' => strtoupper($matches[4]),
                'extension' => $matches[6]
            ];
        } if (preg_match('/^(.+)[\. ]S(\d\d)E(\d{2,4})[\. ](.*?)-(.+?)(\[.+\])?(\.\w+)?$/i', $name, $matches)) {
            return [
                'type' => 'tvshow',
                'name' => trim(str_replace('.', ' ', $matches[1])),
                's' => $matches[2],
                'e' => $matches[3],
                'tags' => $matches[4],
                'group' => strtoupper($matches[5]),
                'extension' => $matches[7]
            ];
        }
        return null;
    }

    private function __constructor() { }

}

?>
