<?php

namespace eru123\helper;

use eru123\types\Number;

define('FORMAT_NUMBER_BYTE', 0);
define('FORMAT_NUMBER_BIT', 1);
define('FORMAT_NUMBER_UNIT', 2);
define('FORMAT_NUMBER_PRECISION', 3);

class Format
{
    public static function number(string $int, int $flag = FORMAT_NUMBER_UNIT, int $precision = 0)
    {
        $bytes_units = [
            'YB' => 1208925819614629174706176,
            'ZB' => 1180591620717411303424,
            'EB' => 1152921504606846976,
            'PB' => 1125899906842624,
            'TB' => 1099511627776,
            'GB' => 1073741824,
            'MB' => 1048576,
            'KB' => 1024,
            'B' => 1,
        ];

        $bits_units = [
            'Yb' => 1208925819614629174706176,
            'Zb' => 1180591620717411303424,
            'Eb' => 1152921504606846976,
            'Pb' => 1125899906842624,
            'Tb' => 1099511627776,
            'Gb' => 1073741824,
            'Mb' => 1048576,
            'Kb' => 1024,
            'b' => 1,
        ];

        $units = [
            'Y' => 1000000000000000000000000,
            'Z' => 1000000000000000000000,
            'E' => 1000000000000000000,
            'P' => 1000000000000000,
            'T' => 1000000000000,
            'B' => 1000000000,
            'M' => 1000000,
            'K' => 1000,
        ];

        switch ($flag) {
            case FORMAT_NUMBER_BYTE:
                foreach ($bytes_units as $unit => $value) {
                    if ($int >= $value) {
                        return Number::div($int, $value, $precision) . $unit;
                    }
                }
                return $int;
            case FORMAT_NUMBER_BIT:
                foreach ($bits_units as $unit => $value) {
                    if ($int >= $value) {
                        return Number::div($int, $value, $precision) . $unit;
                    }
                }
                return $int;
            case FORMAT_NUMBER_UNIT:
                foreach ($units as $unit => $value) {
                    if ($int >= $value) {
                        return Number::div($int, $value, $precision) . $unit;
                    }
                }
                return $int;
            case FORMAT_NUMBER_PRECISION:
                return Number::round($int, $precision);
            default:
                return $int;
        }
    }
}
