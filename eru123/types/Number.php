<?php

namespace eru123\types;

/**
 * Number
 * 
 * @method static bool isPrime(string $number) Check if a number is prime
 * @method static string round(string $number, int $precision) Round a number
 * @method static string match_length(string $number, string $number2) Match length of two numbers
 * @method static string add(string $number, string $number2, int $precision) Add two numbers
 * @method static string sub(string $number, string $number2, int $precision) Subtract two numbers
 * @method static string mul(string $number, string $number2, int $precision) Multiply two numbers
 * @method static string div(string $number, string $number2, int $precision) Divide two numbers
 * @method static string mod(string $number, string $number2, int $precision) Modulus of two numbers
 * @method static string pow(string $number, int $exp, int $precision) Power of a number
 * @method static string div_single(string $number, string $number2, int $precision) Divide a number by a single digit number
 * @method static string comp(string $number, string $number2) Compare two numbers
 * 
 * @method bool isPrime() Check if the number is prime
 * @method string round(int $precision) Round the number
 * @method string match_length(string $number2) Match length of number to another number
 * @method string add(string $number2, int $precision) Add number to another number
 * @method string sub(string $number2, int $precision) Subtract to a number
 * @method string mul(string $number2, int $precision) Multiply to a number
 * @method string div(string $number2, int $precision) Divide to a number
 * @method string mod(string $number2, int $precision) Modulus of a number
 * @method string pow(int $exp, int $precision) Get the power of a number
 * @method string div_single(string $number2, int $precision) Divide the number by a single digit number
 * @method string comp(string $number2) Compare the number to another number
 */
class Number
{
    protected $number;

    public function __construct(string $number)
    {
        $this->number = $number;
    }

    public function __toString(): string
    {
        return $this->number;
    }

    public function __invoke(): string
    {
        return $this->number;
    }

    public function __call(string $name, array $arguments): string
    {
        return new static(call_user_func_array([static::class,  '_' . $name], array_merge([$this->number], $arguments)));
    }

    public static function __callStatic(string $name, array $arguments): string
    {
        return call_user_func_array([self::class, '_' . $name], $arguments);
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

    public static function _round(string $number, int $precision): string
    {
        if (function_exists('bcadd')) {
            return bcadd($number, '0', $precision);
        }

        $sign = substr($number, 0, 1) == '-' ? '-' : '';
        $number = ltrim($number, '-+');

        $na = explode('.', $number);

        if (count($na) == 1) {
            return $number;
        }

        if (count($na) < 2) {
            return '0';
        }

        $f = empty($na[1]) ? '0' : $na[1];
        $i = empty($na[0]) ? '0' : $na[0];
        $f = substr(str_pad($f, $precision, '0', STR_PAD_RIGHT), 0, $precision);
        return $sign . (empty($i) ? '0' : $i) . (empty($f) ? '' : '.' . $f);
    }

    public static function _match_length(string $a, string $b): array
    {
        $a = ltrim($a, '-+');
        $b = ltrim($b, '-+');

        list($aI, $aF) = substr_count($a, '.') ? explode('.', $a) : [$a, '0'];
        list($bI, $bF) = substr_count($b, '.') ? explode('.', $b) : [$b, '0'];

        $mil = max(strlen((string)$aI), strlen((string)$bI));
        $fil = max(strlen((string)$aF), strlen((string)$bF));
        $aI = str_pad($aI, $mil, '0', STR_PAD_LEFT);
        $bI = str_pad($bI, $mil, '0', STR_PAD_LEFT);
        $aF = str_pad($aF, $fil, '0', STR_PAD_RIGHT);
        $bF = str_pad($bF, $fil, '0', STR_PAD_RIGHT);

        $a = $aI . '.' . $aF;
        $b = $bI . '.' . $bF;

        return [$a, $b];
    }

    public static function _add(string $a, string $b, int $precision = 14): string
    {
        if (function_exists('gmp_add')) {
            return static::_round(gmp_strval(gmp_add($a, $b)), $precision);
        }

        if (function_exists('bcadd')) {
            return bcadd($a, $b, $precision);
        }

        list($a, $b) = static::_match_length($a, $b);

        $carry = 0;
        $result = '';
        for ($i = strlen($a) - 1; $i >= 0; $i--) {
            if ($a[$i] == '.') {
                $result = '.' . $result;
                continue;
            }

            $sum = $a[$i] + $b[$i] + $carry;
            $carry = 0;
            if ($sum > 9) {
                $carry = 1;
                $sum -= 10;
            }
            $result = $sum . $result;
        }

        if ($carry) {
            $result = $carry . $result;
        }

        return static::_round($result, $precision);
    }

    public static function _sub(string $a, string $b, int $precision = 14): string
    {
        if (function_exists('gmp_sub')) {
            return static::_round(gmp_strval(gmp_sub($a, $b)), $precision);
        }

        if (function_exists('bcsub')) {
            return bcsub($a, $b, $precision);
        }

        list($a, $b) = static::_match_length($a, $b);

        $carry = 0;
        $result = '';
        for ($i = strlen($a) - 1; $i >= 0; $i--) {
            if ($a[$i] == '.') {
                $result = '.' . $result;
                continue;
            }

            $sum = $a[$i] - $b[$i] - $carry;
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

        return static::_round($result, $precision);
    }

    public static function _mul(string $a, string $b, int $precision = 14): string
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

    public static function _div(string $a, string $b, int $precision = 14): string
    {
        if ($a == '0' || $b == '0') {
            return '0';
        }

        if (function_exists('gmp_div')) {
            return static::_round(gmp_strval(gmp_div($a, $b)), $precision);
        }

        if (function_exists('bcdiv')) {
            return bcdiv($a, $b, $precision);
        }

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

    public static function _mod(string $a, string $b, int $precision = 14): string
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

    public static function _pow(string $a, string $b, int $precision = 14): string
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
}
