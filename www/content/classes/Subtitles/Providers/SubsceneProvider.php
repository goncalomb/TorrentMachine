<?php

namespace Subtitles\Providers;

use \Asbestos\Http;

final class SubsceneProvider extends \Subtitles\SubtitlesProvider {

    private const BASE_URL = 'https://subscene.com';
    private const VERIFIED_GOOD_USERS = [
        'elderman', 'GoldenBeard'
    ];

    private function findSubtitlesPage($term) {
        $term_clean = $term;
        $term_search = $term;

        $info = \ReleaseInfo::parseName($term);
        if ($info) {
            if ($info['type'] == 'movie') {
                $term_clean = $info['name'] . ' (' . $info['year'] . ')';
                $term_search = $info['name'];
            } else if ($info['type'] == 'tvshow') {
                $term_clean = $info['name'] . ' ' . \OrdinalNumbers::get($info['s']) . ' season';
                $term_search = $term_clean;
            }
        }

        libxml_use_internal_errors(true);

        $html = Http::requestSimple(static::BASE_URL . '/subtitles/title?r=false&q=' . urlencode($term_search));
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        $xpath = new \DOMXpath($doc);

        $results = [];
        $elems = $xpath->query("//div[@class='search-result']/ul/li/div[@class='title']/a");
        foreach ($elems as $el) {
            $results[] = [
                'text' => $el->textContent,
                'path' => $el->getAttribute('href'),
                'lev' => levenshtein(strtolower($el->textContent), strtolower($term_clean), 1, 2, 1)
            ];
        }

        usort($results, function($a, $b) {
            return $a['lev'] - $b['lev'];
        });

        if (count($results) && $results[0]['lev'] <= 5) {
            return $results[0]['path'];
        }
        return false;
    }

    public function searchSubtitles($name) {
        $name = strtolower($name);

        $page_url = $this->findSubtitlesPage($name);
        if (!$page_url) {
            return [];
        }

        libxml_use_internal_errors(true);

        $html = Http::requestSimple(static::BASE_URL . $page_url);
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        $tbody = $doc->getElementsByTagName('tbody')->item(0);

        if (!$tbody) {
            return [];
        }

        $results = [];
        $trs = $tbody->getElementsByTagName('tr');
        foreach ($trs as $tr) {
            $tds = $tr->getElementsByTagName('td');
            if (count($tds) >= 4) {
                $spans = $tds->item(0)->getElementsByTagName('span');
                if (count($spans) >= 2) {
                    $results[] = [
                        'language' => trim($spans->item(0)->textContent),
                        'release' => trim($spans->item(1)->textContent),
                        'hi' => ($tds->item(2)->getAttribute('class') == 'a41'),
                        'user' => trim($tds->item(3)->textContent),
                        'url' => static::BASE_URL . $tds->item(0)->getElementsByTagName('a')->item(0)->getAttribute('href')
                    ];
                }
            }
        }

        // calculate levenshtein distance
        $name_no_ext = preg_replace('/\.[a-z\d]{3,4}$/i', '', $name);
        $name_no_ext_no_brackets = preg_replace('/\[.*?\]/', '', $name_no_ext);
        foreach ($results as &$result) {
            $rel = strtolower($result['release']);
            $l0 = levenshtein($rel, $name, 1, 2, 1);
            $l1 = levenshtein($rel, $name_no_ext, 1, 2, 1) + 1;
            $l2 = levenshtein($rel, $name_no_ext_no_brackets, 1, 2, 1) + 1;
            $result['levenshtein'] = min($l0, $l1, $l2);
            // max levenshtein is not precise, it uses the largest of the 3 tested strings
            // it does take into account that the superior cost of a replacement
            $result['levenshtein-max'] = 2*min(strlen($rel), strlen($name)) + abs(strlen($rel) - strlen($name));
        }

        // find specials
        foreach ($results as &$result) {
            $result['special'] = in_array($result['user'], self::VERIFIED_GOOD_USERS);
        }

        // order by hi and relevance
        usort($results, function($a, $b) {
            if ($a['language'] == $b['language']) {
                if ($a['levenshtein'] == $b['levenshtein']) {
                    if ($a['hi'] == $b['hi']) {
                        return (($a['special'] == $b['special']) ? 0 : ($a['special'] ? -1 : 1));
                    }
                    return ($a['hi'] ? 1 : -1);
                }
                return $a['levenshtein'] - $b['levenshtein'];
            }
            return ($a['language'] == 'English' ? -1 : 1);
        });

        return $results;
    }

    public function getSubtitle($url) {
        libxml_use_internal_errors(true);

        $html = Http::requestSimple($url);
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        $download_url = static::BASE_URL . $doc->getElementById('downloadButton')->getAttribute('href');

        $tmp_file = tempnam(sys_get_temp_dir(), hash('crc32b', __CLASS__) . '-');
        copy($download_url, $tmp_file);
        $zip = new \ZipArchive();
        $zip->open($tmp_file);
        $srt_contents = $zip->getFromIndex(0);
        $zip->close();
        unlink($tmp_file);

        return $srt_contents;
    }

}

?>
