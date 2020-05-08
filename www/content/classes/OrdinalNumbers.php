<?php

final class OrdinalNumbers {

    private const FIRST_ORD = [
        'first', 'second', 'third', 'fourth', 'fifth',
        'sixth', 'seventh', 'eighth', 'ninth', 'tenth',
        'eleventh', 'twelfth', 'thirteenth', 'fourteenth', 'fifteenth',
        'sixteenth', 'seventeenth', 'eighteenth', 'nineteenth', 'twentieth'
    ];
    private const N10 = ['twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
    private const N10_ORD = ['thirtieth', 'fortieth', 'fiftieth', 'sixtieth', 'seventieth', 'eightieth', 'ninetieth', 'hundredth'];

    public static function get($n) {
        if ($n > 0) {
            if ($n <= 20) {
                return self::FIRST_ORD[$n - 1];
            } else if ($n <= 100) {
                if ($n%10 == 0) {
                    return self::N10_ORD[floor($n/10) - 3];
                } else {
                    return self::N10[floor($n/10) - 2] . ' ' . self::FIRST_ORD[$n%10 - 1];
                }
            }
        }
        return null;
    }

    private function __constructor() { }

}

?>
