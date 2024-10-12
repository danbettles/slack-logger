<?php declare(strict_types=1);

namespace DanBettles\SlackLogger;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel as PsrLogLevel;

use function array_key_first;
use function array_keys;
use function array_search;

use const false;
use const null;
use const true;

class LogLevel extends PsrLogLevel
{
    /**
     * N.B.: lowest to highest priority
     *
     * @var array<string,string>
     */
    private const array LEVELS_IN_PRIORITY_ORDER = [
        self::DEBUG => ':information_source:',
        self::INFO => ':information_source:',
        self::NOTICE => ':information_source:',
        self::WARNING => ':warning:',
        self::ERROR => ':bangbang:',
        self::CRITICAL => ':bangbang:',
        self::ALERT => ':bangbang:',
        self::EMERGENCY => ':bangbang:',
    ];

    /**
     * Returns the priority (numeric value) of the specified log-level, or `false` if the level is unknown
     *
     * @return int|false
     */
    public static function getPriorityOfLevel(mixed $level): int|bool
    {
        $levels = array_keys(self::LEVELS_IN_PRIORITY_ORDER);

        return array_search($level, $levels, true);
    }

    /**
     * Returns `true` if the specified level exists, or `false` otherwise
     */
    public static function levelExists(mixed $level): bool
    {
        return false !== self::getPriorityOfLevel($level);
    }

    /**
     * @throws InvalidArgumentException If the log-level does not exist
     */
    public static function assertLevelExists(mixed $level): void
    {
        if (!self::levelExists($level)) {
            $readableLevel = Utils::dump($level);

            throw new InvalidArgumentException("The log-level, `{$readableLevel}`, does not exist");
        }
    }

    /**
     * Returns the name/value (e.g. `"info"`) of the log-level with the lowest priority
     */
    public static function getLevelWithLowestPriority(): string
    {
        return array_key_first(self::LEVELS_IN_PRIORITY_ORDER);
    }

    public static function getEmojiShortcodeForLevel(int|string $level): string|null
    {
        return self::LEVELS_IN_PRIORITY_ORDER[$level] ?? null;
    }
}
