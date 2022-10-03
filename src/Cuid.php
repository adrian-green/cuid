<?php
declare(strict_types=1);

namespace AdrianGreen;

use function array_reduce;
use function base_convert;
use function floor;
use function gethostname;
use function getmypid;
use function microtime;
use function ord;
use function preg_match;
use function random_int;
use function str_pad;
use function str_split;
use function strlen;
use function substr;
use const STR_PAD_LEFT;

/**
 * Cuid is a library to create collision resistant ids optimized for horizontal scaling and performance.
 */
class Cuid
{
    public const REGEX_SHORT_CUID = '/^[0-9a-z]{2}\d{2}[0-9a-z]{4}$/';
    /**
     * Broken down
     * c - h72gsb32 - 0000 - udoc - l363eofy **
     * The groups, in order, are:
     * 'c' - identifies this as a cuid, and allows you to use it in html entity ids.
     * Timestamp
     * Counter - a single process might generate the same random string. The weaker the pseudo-random source, the higher the probability. That problem gets worse as processors get faster. The counter will roll over if the value gets too big.
     * Client fingerprint
     * Random (using cryptographically secure libraries where available).
     */
    public const REGEX_CUID       = '/^c[0-9a-z]{8}\d{4}[0-9a-z]{12}$/';

    /**
     * Base 36 constant
     */
    public const BASE36 = 36;

    /**
     * Decimal constant
     */
    public const DECIMAL = 10;

    /**
     * Normal block size
     */
    public const NORMAL_BLOCK = 4;

    /**
     * Small block size
     */
    public const SMALL_BLOCK = 2;

    private const MAXINT = self::BASE36 ** self::NORMAL_BLOCK;

    private const PREFIX = 'c';

    private static string $hostname;

    private static int $pid;
    /**
     * flag to test if one-time setup has run
     */
    private static bool $initialized = false;

    public static function init(): void
    {
        if(self::$initialized) {
            return;
        }

        static::$hostname = gethostname(); //gethostname() is extremely slow
        static::$pid = getmypid();

        self::$initialized = true;
    }
    /**
     * Counter used to prevent same machine collision
     *
     * @param integer $blockSize Block size
     *
     * @return string Return count generated hash
     */
    protected static function count(int $blockSize = self::NORMAL_BLOCK): string
    {
        static $count = 0;

        return self::pad(
            base_convert(
                (string) ++$count,
                self::DECIMAL,
                self::BASE36
            ),
            $blockSize
        );
    }

    /**
     * Fingerprint are used for process identification
     * It only needs to be computed once, so the result is memoized.
     * 
     * @param integer $blockSize Block size
     *
     * @return string Return fingerprint generated hash
     */
    protected static function fingerprint(int $blockSize = self::NORMAL_BLOCK): string
    {
        static $fingerprint = []; //memoized result

        if(isset($fingerprint[$blockSize])) {
            return $fingerprint[$blockSize];
        }

        // Generate process id based hash
        $pid = self::pad(
            base_convert(
                (string) static::$pid,
                self::DECIMAL,
                self::BASE36
            ),
            self::NORMAL_BLOCK / 2
        );

        // Generate hostname based hash

        $print = self::pad(
            base_convert(
                (string) array_reduce(
                    str_split(static::$hostname),
                    static function ($carry, $char) {
                        return $carry + ord($char);
                    },
                    strlen(static::$hostname) + self::BASE36
                ),
                self::DECIMAL,
                self::BASE36
            ),
            2
        );

        // Return small or normal block of hash
        if ($blockSize === self::SMALL_BLOCK) {
            return $fingerprint[$blockSize]  = $pid[0] . substr($print, -1);
        }

        return $fingerprint[$blockSize] = $pid . $print;
    }

    /**
     * Pad the input string into specific size
     *
     * @param string  $input Input string
     * @param integer $size  Input size
     *
     * @return string Return padded string
     */
    protected static function pad(string $input, int $size): string
    {
        $input = str_pad(
            $input,
            self::BASE36,
            '0',
            STR_PAD_LEFT
        );

        return substr($input, strlen($input) - $size);
    }

    /**
     * Generate random hash
     *
     * @param int $blockSize
     *
     * @return string Return random hash string
     * @throws \Exception
     */
    protected static function random(int $blockSize = self::NORMAL_BLOCK): string
    {
        $hash = self::pad(
            base_convert(
                (string) random_int(0, static::MAXINT),
                self::DECIMAL,
                self::BASE36
            ),
            self::NORMAL_BLOCK
        );

        // Limit hash if small block required
        if ($blockSize === self::SMALL_BLOCK) {
            $hash = substr($hash, -2);
        }

        return $hash;
    }

    /**
     * Generate timestamp based hash
     *
     * @param int $blockSize
     *
     * @return string Return timestamp based hash string
     */
    protected static function timestamp(int $blockSize = self::NORMAL_BLOCK): string
    {
        // Convert current time up to micro second to hash
        $hash = base_convert(
            (string) floor(microtime(true) * 1000),
            self::DECIMAL,
            self::BASE36
        );

        // Limit hash if small block required
        if ($blockSize === self::SMALL_BLOCK) {
            $hash = substr($hash, -2);
        }

        return $hash;
    }

    /**
     * Invoke magic method to allows easy access
     *
     * @return string Return generated cuid string
     * @throws \Exception
     */
    public function __invoke(): string
    {
        return self::cuid();
    }

    /**
     * Generate full version cuid
     *
     * @return string Return generated cuid string
     * @throws \Exception
     */
    public static function cuid(): string
    {
        // initialize to preload expensive vars
        if (!self::$initialized) {
            self::init();
        }

        return
            self::PREFIX .
            self::timestamp() .
            self::count() .
            self::fingerprint() .
            self::random() .
            self::random();
    }

    /**
     * An alias to cuid method
     *
     * @return string Return generate cuid string
     * @throws \Exception
     */
    public static function make(): string
    {
        return self::cuid();
    }

    /**
     * Generate short version cuid
     *
     * It only hase 8 characters and it is a great solution
     * for short urls.
     *
     * Note: Less room for the data also means higher
     * chance of collision
     *
     * @return string Return generated short cuid string
     * @throws \Exception
     */
    public static function slug(): string
    {
        // initialize to preload expensive vars
        if(! self::$initialized) {
            self::init();
        }

        return
            self::timestamp(self::SMALL_BLOCK) .
            self::count(self::SMALL_BLOCK) .
            self::fingerprint(self::SMALL_BLOCK) .
            self::random(self::SMALL_BLOCK);
    }

    /**
     * Check if string is a valid 'cuid'.
     * Checks the cuid is prefixed with a 'c' char then
     * tests the cuid matches on a regex
     * Does not work with a 'slug'
     *
     * @param string $cuid
     *
     * @return boolean
     */

    public static function isCuid(string $cuid): bool
    {
        if ($cuid[0] === self::PREFIX && 25 === strlen($cuid)) {
            return (bool) preg_match(self::REGEX_CUID, $cuid);
        }

        return false;
    }
}
