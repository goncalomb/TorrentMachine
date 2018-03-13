<?php

final class TVShowCollection {

    private $_data = [];

    private function parseTVShow($name) {
        if (preg_match('/^(.+)[\. ](S\d\dE\d{2,4})[\. ](.*?)-(.+?)(\[.+\])?(\.mkv|\.mp4|\.avi)$/i', $name, $matches)) {
            return [
                'show' => trim(str_replace('.', ' ', $matches[1])),
                'number' => strtoupper($matches[2]),
                'tags' => $matches[3],
                'group' => strtoupper($matches[4])
            ];
        }
        return false;
    }

    private function countUpperLetters($str) {
        return strlen(preg_replace('/[^A-Z]+/', '', $str));
    }

    public function tryAddFile($path, $have) {
        $info = self::parseTVShow(basename($path));
        if ($info) {
            $show = $info['show'];
            $show_lower = strtolower($info['show']);
            unset($info['show']);
            $info['path'] = $path;
            $info['have'] = $have;

            if (!isset($this->_data[$show_lower])) {
                $this->_data[$show_lower] = ['episodes' => [], 'names' => []];
            }
            $this->_data[$show_lower]['episodes'][] = $info;
            $this->_data[$show_lower]['names'][$show] = self::countUpperLetters($show);

            return true;
        }
        return false;
    }

    public function getJSON() {
        $result = [];
        ksort($this->_data);
        foreach ($this->_data as &$info) {
            // sort episodes
            usort($info['episodes'], function ($a, $b) {
                $x = strcmp($a['number'], $b['number']);
                if ($x == 0) {
                    $x = strcmp($a['tags'], $b['tags']);
                }
                return $x;
            });
            // find best name (more capital letters)
            arsort($info['names']);
            $result[] = [
                'name' => array_keys($info['names'])[0],
                'episodes' => $info['episodes']
            ];
        }
        return json_encode($result, JSON_PRETTY_PRINT);
    }

}

?>
