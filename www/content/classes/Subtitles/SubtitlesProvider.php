<?php

namespace Subtitles;

use \Asbestos\Response;

abstract class SubtitlesProvider {

    abstract public function searchSubtitles($name);
    abstract public function getSubtitle($url);

    private static function serveSrtAsVtt($data) {
        $data = str_replace("\xEF\xBB\xBF", '', $data); // remove BOM
        Response::contentType('text/vtt');
        echo "WEBVTT FILE\n\n";
        echo preg_replace('/(\d\d:\d\d:\d\d),(\d\d\d)/', '$1.$2', $data);
    }

    public static function serveBestSubtitleForRelease($name) {
        $provider = new Providers\SubsceneProvider();
        $subtitles = $provider->searchSubtitles($name);
        if (count($subtitles)) {
            $sub = $subtitles[0];
            $confidence = round((1 - ($sub['levenshtein']/$sub['levenshtein-max']))*100, 1);
            $data = $provider->getSubtitle($sub['url']);
            static::serveSrtAsVtt("0\n00:00:00,000 --> 00:00:10,000\n{$sub['release']}\n[$confidence% - {$sub['user']} - " . ($sub['hi'] ? 'H.I.' : 'Clean') . "]\n\n" . $data);
        } else {
            static::serveSrtAsVtt("0\n00:00:00,000 --> 99:00:00,000\n[Subtitle Not Found]\n");
        }
    }

}

?>
