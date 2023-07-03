<?php

declare(strict_types=1);

namespace eru123\types;

use BadMethodCallException;
use Exception;
use InvalidArgumentException;

define('TYPES_NUMBER_PRECISION', 14);
define('TYPES_NUMBER_DECIMAL', '/^([-+])?([0-9]+)((\.)([0-9]+))?$/');
define('TYPES_NUMBER_SCIENTIFIC', '/^([-+])?([0-9]+)((\.)([0-9]+))?([eE]([-+])?([0-9]+))?$/');

/**
 * Number
 * 
 * @method static bool isPrime(string $number) Check if a number is prime
 * @method static string round(string $number, int $precision = TYPES_NUMBER_PRECISION) Round a number
 * @method static string match_length(string $number, string $number2) Match length of two numbers
 * @method static string add(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Add two numbers
 * @method static string sub(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Subtract two numbers
 * @method static string mul(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Multiply two numbers
 * @method static string div(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Divide two numbers
 * @method static string mod(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Modulus of two numbers
 * @method static string pow(string $number, int $exp, int $precision = TYPES_NUMBER_PRECISION) Power of a number
 * @method static string div_single(string $number, string $number2, int $precision = TYPES_NUMBER_PRECISION) Divide a number by a single digit number
 * @method static string comp(string $number, string $number2) Compare two numbers
 * @method static string parse(string $number, int $precision = TYPES_NUMBER_PRECISION) Parse a number to a proper format
 * 
 * @method bool isPrime() Check if the number is prime
 * @method string round(int $precision = TYPES_NUMBER_PRECISION) Round the number
 * @method string match_length(string $number2) Match length of number to another number
 * @method string add(string $number2, int $precision = TYPES_NUMBER_PRECISION) Add number to another number
 * @method string sub(string $number2, int $precision = TYPES_NUMBER_PRECISION) Subtract to a number
 * @method string mul(string $number2, int $precision = TYPES_NUMBER_PRECISION) Multiply to a number
 * @method string div(string $number2, int $precision = TYPES_NUMBER_PRECISION) Divide to a number
 * @method string mod(string $number2, int $precision = TYPES_NUMBER_PRECISION) Modulus of a number
 * @method string pow(int $exp, int $precision = TYPES_NUMBER_PRECISION) Get the power of a number
 * @method string div_single(string $number2, int $precision = TYPES_NUMBER_PRECISION) Divide the number by a single digit number
 * @method string comp(string $number2) Compare the number to another number
 * @method string parse(int $precision = TYPES_NUMBER_PRECISION) Parse the number to a proper format
 */
class Number
{
    protected $number;

    public function __construct(string $number)
    {
        $this->number = static::_parse($number);
    }

    public function __toString(): string
    {
        return $this->number;
    }

    public function __invoke(): string
    {
        return $this->number;
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists(self::class, '_' . $name)) {
            return call_user_func_array([self::class, '_' . $name], array_merge([$this->number], $arguments));
        }

        throw new BadMethodCallException('Call to undefined method ' . self::class . '::' . $name . '()');
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (method_exists(self::class, '_' . $name)) {
            return call_user_func_array([self::class, '_' . $name], $arguments);
        }

        throw new BadMethodCallException('Call to undefined method ' . self::class . '::' . $name . '()');
    }

    public static function _isPrime(string $number): bool
    {
        if ($number == 1 || empty($number)) {
            return false;
        }

        if (function_exists('gmp_prob_prime')) {
            return gmp_prob_prime($number) == 2;
        }

        $n = (int) $number;
        if ($n <= 3) {
            return $n > 1;
        } elseif ($n % 2 == 0 || $n % 3 == 0) {
            return false;
        }

        for ($i = 5; $i * $i <= $n; $i += 6) {
            if ($n % $i == 0 || $n % ($i + 2) == 0) {
                return false;
            }
        }

        return true;
    }

    public static function _round(string $number, int $precision = TYPES_NUMBER_PRECISION): string
    {
        if (!preg_match(TYPES_NUMBER_DECIMAL, $number, $matches)) {
            return static::_parse($number, $precision);
        }

        $sign = $matches[1] ?? '';
        $sign = $sign == '-' ? '-' : '';
        $int = ltrim($matches[2], '0') ?? '0';
        $dec = rtrim($matches[5] ?? '', '0') ?? '0';
        $dec = str_pad($dec, $precision, '0', STR_PAD_RIGHT);
        $dec = substr($dec, 0, $precision);

        return $precision >= 1 ? $sign . $int . '.' . $dec : $sign . $int;
    }

    public static function _match_length(string $a, string $b): array
    {
        preg_match(TYPES_NUMBER_DECIMAL, $a, $amts);
        preg_match(TYPES_NUMBER_DECIMAL, $b, $bmts);

        $as = $amts[1] ?? '+';
        $bs = $bmts[1] ?? '+';
        $ss = $as == $bs ? true : false;
        $sc = static::_comp($a, $b);
        $ms = $sc == 1 ? $amts[1] : $bmts[1];

        $ai = $amts[2] ?? '0';
        $bi = $bmts[2] ?? '0';
        $mi = max(strlen($ai), strlen($bi));

        $ai = str_pad($ai, $mi, '0', STR_PAD_LEFT);
        $bi = str_pad($bi, $mi, '0', STR_PAD_LEFT);

        $af = $amts[5] ?? '';
        $bf = $bmts[5] ?? '';
        $mf = max(strlen($af), strlen($bf));

        $af = str_pad($af, $mf, '0', STR_PAD_RIGHT);
        $bf = str_pad($bf, $mf, '0', STR_PAD_RIGHT);

        $a = $ai . ($af ? '.' . $af : '');
        $b = $bi . ($bf ? '.' . $bf : '');
        return [$a, $b, $ss, $ms];
    }

    public static function _add(string $a, string $b, int $precision = TYPES_NUMBER_PRECISION): string
    {
        $a = static::_parse($a, $precision);
        $b = static::_parse($b, $precision);

        // if (function_exists('gmp_add')) {
        //     return static::_round(gmp_strval(gmp_add($a, $b)), $precision);
        // }

        // if (function_exists('bcadd')) {
        //     return bcadd($a, $b, $precision);
        // }

        list($a, $b, $ss, $ms) = static::_match_length($a, $b);
        if ($ss) {
            $carry = 0;
            $result = '';
            for ($i = strlen($a) - 1; $i >= 0; $i--) {
                if ($a[$i] == '.') {
                    $result = '.' . $result;
                    continue;
                }

                $sum = $a[$i] + $b[$i] + $carry;
                $carry = 0;
                if ($sum >= 10) {
                    $carry = 1;
                    $sum -= 10;
                }
                $result = $sum . $result;
            }
            
            if ($carry) {
                $result = $carry . $result;
            }

            return $ms . $result;
        }

        $carry = 0;
        $result = '';
        for ($i = strlen($a) - 1; $i >= 0; $i--) {
            if ($a[$i] == '.') {
                $result = '.' . $result;
                continue;
            }

            $sum = (int) $a[$i] - (int) $b[$i] - $carry;
            $carry = 0;
            if ($sum < 0) {
                $carry = 1;
                $sum += 10;
            }
            $result = $sum . $result;
        }

        if ($carry) {
            $result = $carry . $result;
        }

        return $ms . $result;
    }

    public static function _sub(string $a, string $b, int $precision = TYPES_NUMBER_PRECISION): string
    {
        // if (function_exists('gmp_sub')) {
        //     return static::_round(gmp_strval(gmp_sub($a, $b)), $precision);
        // }

        // if (function_exists('bcsub')) {
        //     return bcsub($a, $b, $precision);
        // }

        $s = $b[0] == '-' ? '' : '-';
        $b = $s . substr($b, 1);
        return static::_add($a, $b, $precision);
    }

    public static function _mul(string $a, string $b, int $precision = TYPES_NUMBER_PRECISION): string
    {
        if (function_exists('gmp_mul')) {
            return static::_round(gmp_strval(gmp_mul($a, $b)), $precision);
        }

        if (function_exists('bcmul')) {
            return bcmul($a, $b, $precision);
        }

        list($a, $b) = static::_match_length($a, $b);

        $result = '0';
        for ($i = strlen($b) - 1; $i >= 0; $i--) {
            if ($b[$i] == '.') {
                continue;
            }

            $carry = 0;
            $temp = '';
            for ($j = strlen($a) - 1; $j >= 0; $j--) {
                if ($a[$j] == '.') {
                    continue;
                }

                $sum = $a[$j] * $b[$i] + $carry;
                $carry = 0;
                if ($sum > 9) {
                    $carry = (int) ($sum / 10);
                    $sum -= $carry * 10;
                }
                $temp = $sum . $temp;
            }

            if ($carry) {
                $temp = $carry . $temp;
            }

            $result = static::_add($result, $temp);
        }

        return static::_round($result, $precision);
    }

    public static function _div(string $a, string $b, int $precision = TYPES_NUMBER_PRECISION): string
    {
        $a = static::_parse($a);
        $b = static::_parse($b);

        if ($a == '0' || $b == '0') {
            return '0';
        }

        // if (function_exists('gmp_div')) {
        //     return static::_round(gmp_strval(gmp_div($a, $b)), $precision);
        // }

        // if (function_exists('bcdiv')) {
        //     return bcdiv($a, $b, $precision);
        // }

        list($a, $b) = static::_match_length($a, $b);

        $result = '0';
        $temp = '';
        $a = ltrim($a, '0');
        for ($i = 0; $i < strlen($a); $i++) {
            $temp .= $a[$i];
            if (static::_comp($temp, $b) >= 0) {
                $result .= floor(static::_div_single($temp, $b));
                $temp = static::_sub($temp, static::_mul($b, floor(static::_div_single($temp, $b))));
            } else {
                $result .= '0';
            }
        }

        return static::_round($result, $precision);
    }

    public static function _div_single(string $a, string $b): string
    {
        if ($a == '0' || $b == '0') {
            return '0';
        }

        if (function_exists('gmp_div')) {
            return gmp_strval(gmp_div($a, $b));
        }

        if (function_exists('bcdiv')) {
            return bcdiv($a, $b);
        }

        $a = ltrim($a, '0');
        $b = ltrim($b, '0');

        $result = '0';
        $temp = '';
        for ($i = 0; $i < strlen($a); $i++) {
            $temp .= $a[$i];
            if (static::_comp($temp, $b) >= 0) {
                $result++;
                $temp = static::_sub($temp, $b);
            }
        }

        return $result;
    }

    public static function _mod(string $a, string $b, int $precision = TYPES_NUMBER_PRECISION): string
    {
        if (function_exists('gmp_mod')) {
            return static::_round(gmp_strval(gmp_mod($a, $b)), $precision);
        }

        if (function_exists('bcmod')) {
            return bcmod($a, $b, $precision);
        }

        list($a, $b) = static::_match_length($a, $b);

        $a = ltrim($a, '0');
        $b = ltrim($b, '0');

        $temp = '';
        for ($i = 0; $i < strlen($a); $i++) {
            $temp .= $a[$i];
            if (static::_comp($temp, $b) >= 0) {
                $temp = static::_sub($temp, $b);
            }
        }

        return static::_round($temp, $precision);
    }

    public static function _comp(string $a, string $b): int
    {
        if (function_exists('gmp_cmp')) {
            return gmp_cmp($a, $b);
        }

        if (function_exists('bccomp')) {
            return bccomp($a, $b);
        }

        list($a, $b) = static::_match_length($a, $b);

        $a = ltrim($a, '0');
        $b = ltrim($b, '0');

        if (strlen($a) > strlen($b)) {
            return 1;
        } elseif (strlen($a) < strlen($b)) {
            return -1;
        } else {
            for ($i = 0; $i < strlen($a); $i++) {
                if ($a[$i] > $b[$i]) {
                    return 1;
                } elseif ($a[$i] < $b[$i]) {
                    return -1;
                }
            }
        }

        return 0;
    }

    public static function _pow(string $a, string $b, int $precision = TYPES_NUMBER_PRECISION): string
    {
        if (function_exists('gmp_pow')) {
            return static::_round(gmp_strval(gmp_pow($a, $b)), $precision);
        }

        if (function_exists('bcpow') && $b <= PHP_INT_MAX) {
            return bcpow($a, $b, $precision);
        }

        $result = '1';
        $b = static::_round($b, 0);

        while (static::_comp($b, '0') > 0) {
            $result = static::_mul($result, $a);
            $b = static::_sub($b, '1');
        }

        return static::_round($result, $precision);
    }

    public static function _parse(string $number, int $precision = TYPES_NUMBER_PRECISION): string
    {
        if (preg_match(TYPES_NUMBER_DECIMAL, $number)) {
            return static::_round($number, $precision);
        }

        if (preg_match(TYPES_NUMBER_SCIENTIFIC, $number, $matches)) {
            return static::_mul($matches[1] . $matches[2] . $matches[3], static::_pow('10', $matches[8], $precision));
        }

        throw new InvalidArgumentException("Invalid number format for '$number'");
    }
}
