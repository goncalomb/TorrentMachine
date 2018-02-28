<?php

use \Asbestos\Http;
use \Asbestos\Response;

final class SubtitlesController {

    private static $_specialSubtitleUsers = [
        'elderman', 'GoldenBeard'
    ];

    private static function serveSrtAsVtt($data) {
        $data = str_replace("\xEF\xBB\xBF", '', $data); // remove BOM
        Response::contentType('text/vtt');
        echo "WEBVTT FILE\n\n";
        echo preg_replace('/(\d\d:\d\d:\d\d),(\d\d\d)/', '$1.$2', $data);
    }

    private static function searchSubScene($name) {
        $html = Http::requestSimple('https://subscene.com/subtitles/release?q=' . urlencode($name));
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $tbody = $dom->getElementsByTagName('tbody')->item(0);
        if (!$tbody) {
            return [];
        }

        $result = [];
        $trs = $tbody->getElementsByTagName('tr');
        foreach ($trs as $tr) {
            $tds = $tr->getElementsByTagName('td');
            $spans = $tds->item(0)->getElementsByTagName('span');
            $url = 'https://subscene.com' . $tds->item(0)->getElementsByTagName('a')->item(0)->getAttribute('href');
            $result[] = [
                'language' => trim($spans->item(0)->textContent),
                'release' => trim($spans->item(1)->textContent),
                'hi' => ($tds->item(2)->getAttribute('class') == 'a41'),
                'user' => trim($tds->item(3)->textContent),
                'fetch' => function() use ($url) {
                    $html = Http::requestSimple($url);
                    $dom = new DOMDocument();
                    libxml_use_internal_errors(true);
                    $dom->loadHTML($html);
                    $download_url = 'https://subscene.com' . $dom->getElementById('downloadButton')->getAttribute('href');

                    $tmp_file = tempnam(sys_get_temp_dir(), 'subscene');
                    copy($download_url, $tmp_file);
                    $zip = new ZipArchive();
                    $zip->open($tmp_file);
                    $srt_contents = $zip->getFromIndex(0);
                    $zip->close();
                    unlink($tmp_file);

                    return $srt_contents;
                }
            ];
        }
        return $result;
    }

    public static function get() {
        if (empty($_GET['name'])) {
            Response::contentType('plain', 400);
            echo "Invalid 'name' parameter.\n";
        } else {
            $name = strtolower($_GET['name']);

            // search for subtitles
            $subtitles = self::searchSubScene($name);
            // english only
            $subtitles = array_filter($subtitles, function ($sub) {
                return (strtolower($sub['language']) == 'english');
            });
            // calculate levenshtein distance
            $name_no_ext = preg_replace('/\.[a-z\d]{3,4}$/i', '', $name);
            $name_no_ext_no_brackets = preg_replace('/\[.*?\]/', '', $name_no_ext);
            foreach ($subtitles as &$s) {
                $rel = strtolower($s['release']);
                $l0 = levenshtein($rel, $name, 1, 2, 1);
                $l1 = levenshtein($rel, $name_no_ext, 1, 2, 1) + 1;
                $l2 = levenshtein($rel, $name_no_ext_no_brackets, 1, 2, 1) + 1;
                $s['levenshtein'] = min($l0, $l1, $l2);
            }
            // find specials
            foreach ($subtitles as &$s) {
                $s['special'] = in_array($s['user'], self::$_specialSubtitleUsers);
            }
            // order by hi and relevance
            usort($subtitles, function($a, $b) use ($name) {
                if ($a['levenshtein'] == $b['levenshtein']) {
                    if ($a['hi'] == $b['hi']) {
                        return (($a['special'] == $b['special']) ? 0 : ($a['special'] ? -1 : 1));
                    }
                    return ($a['hi'] ? 1 : -1);
                }
                return $a['levenshtein'] - $b['levenshtein'];
            });

            if (count($subtitles)) {
                // use the first one
                $sub = $subtitles[0];
                $data = $sub['fetch']();
                static::serveSrtAsVtt("0\n00:00:00,000 --> 00:00:05,000\n{$sub['release']}\n[{$sub['user']} - " . ($sub['hi'] ? 'H.I.' : 'Clean') . "]\n\n" . $data);
            } else {
                static::serveSrtAsVtt("0\n00:00:00,000 --> 99:00:00,000\n[Subtitle Not Found]\n");
            }
        }
    }

    private function __construct() { }

}

?>
