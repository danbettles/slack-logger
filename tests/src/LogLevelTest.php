<?php declare(strict_types=1);

namespace DanBettles\SlackLogger\Tests;

use DanBettles\SlackLogger\LogLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;

use function array_merge;

use const false;
use const null;
use const true;

class LogLevelTest extends TestCase
{
    /** @return array<mixed[]> */
    public static function providesPrioritiesOfLogLevels(): array
    {
        return [
            [
                0,
                LogLevel::DEBUG,
            ],
            [
                7,
                LogLevel::EMERGENCY,
            ],
            [
                false,
                'foo',
            ],
        ];
    }

    #[DataProvider('providesPrioritiesOfLogLevels')]
    public function testGetpriorityoflevelReturnsThePriorityOfTheSpecifiedLogLevel(
        int|bool $expectedPriority,
        string $level,
    ): void {
        $this->assertSame($expectedPriority, LogLevel::getPriorityOfLevel($level));
    }

    public function testGetlevelwithlowestpriorityReturnsTheValueOfTheLogLevelWithTheLowestPriority(): void
    {
        $this->assertSame(LogLevel::DEBUG, LogLevel::getLevelWithLowestPriority());
    }

    /** @return array<mixed[]> */
    public static function providesEmojiShortcodesOfLogLevels(): array
    {
        return [
            [
                ':information_source:',
                LogLevel::DEBUG,
            ],
            [
                ':bangbang:',
                LogLevel::EMERGENCY,
            ],
            [
                null,
                'foo',
            ],
        ];
    }

    #[DataProvider('providesEmojiShortcodesOfLogLevels')]
    public function testGetemojishortcodeforlevelReturnsTheEmojiShortcodeForTheSpecifiedLogLevel(
        string|null $expectedEmojiShortcode,
        string $level,
    ): void {
        $this->assertSame($expectedEmojiShortcode, LogLevel::getEmojiShortcodeForLevel($level));
    }

    /** @return array<mixed[]> */
    public static function providesExistentLogLevels(): array
    {
        return [
            [
                true,
                LogLevel::DEBUG,
            ],
            [
                true,
                LogLevel::WARNING,
            ],
            [
                true,
                LogLevel::EMERGENCY,
            ],
        ];
    }

    /** @return array<mixed[]> */
    public static function providesNonExistentLogLevels(): array
    {
        return [
            [
                false,
                'foo',
                'The log-level, `"foo"`, does not exist',
            ],
            [
                false,
                null,
                'The log-level, `null`, does not exist',
            ],
            [
                false,
                123,
                'The log-level, `123`, does not exist',
            ],
        ];
    }

    /** @return array<mixed[]> */
    public static function providesMixtureOfLogLevels(): array
    {
        return array_merge(
            self::providesExistentLogLevels(),
            self::providesNonExistentLogLevels(),
        );
    }

    #[DataProvider('providesMixtureOfLogLevels')]
    public function testLevelexistsReturnsTrueIfTheSpecifiedLogLevelExists(
        bool $exists,
        mixed $level,
    ): void {
        $this->assertSame($exists, LogLevel::levelExists($level));
    }

    #[DataProvider('providesNonExistentLogLevels')]
    public function testAssertlevelexistsThrowsAnExceptionIfTheSpecifiedLogLevelDoesNotExist(
        bool $ignore,
        mixed $nonExistentLevel,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        LogLevel::assertLevelExists($nonExistentLevel);
    }
}
