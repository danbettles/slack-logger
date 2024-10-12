<?php declare(strict_types=1);

namespace DanBettles\SlackLogger;

use function is_callable;
use function is_object;
use function is_scalar;
use function is_string;
use function print_r;

use const false;
use const null;
use const true;

// @todo Test this!
class Utils
{
    public static function dump(mixed $something): string
    {
        return match (true) {
            null === $something => 'null',
            // true === $something => 'true',
            // false === $something => 'false',
            is_string($something) => "\"{$something}\"",
            // phpcs:ignore
            default => print_r($something, true),
        };
    }

    public static function isStringable(mixed $value): bool
    {
        return (
            is_scalar($value)
            || (is_object($value) && is_callable([$value, '__toString']))
        );
    }
}
