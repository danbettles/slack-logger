<?php declare(strict_types=1);

namespace DanBettles\SlackLogger\Tests;

use DanBettles\SlackLogger\AppContext;
use DanBettles\SlackLogger\Logger;
use DanBettles\SlackLogger\LogLevel;
use DanBettles\SlackLogger\Slack;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException as PsrLogInvalidArgumentException;

/**
 * @phpstan-import-type OptionsArray from Logger
 * @phpstan-import-type CreateArgs from Logger
 * @phpstan-import-type ConstructorArgs from Logger
 * @phpstan-import-type CreateMessageArgs from Slack
 * @phpstan-import-type LogArgs from Logger
 */
class LoggerTest extends TestCase
{
    private static function createAppContext(): AppContext
    {
        return new AppContext(
            'Test App',
            'host.name',
            ['SCRIPT_NAME' => '/path/to/script'],
            [],
        );
    }

    private static function createSlack(): Slack
    {
        return new Slack('https://example.com/');
    }

    /** @return array<mixed[]> */
    public static function providesValidConstructorArgs(): array
    {
        $appContext = self::createAppContext();
        $slack = self::createSlack();

        return [
            [
                [$appContext, $slack],
            ],
            [
                [$appContext, $slack, []],
            ],
            [
                [$appContext, $slack, ['minLogLevel' => LogLevel::EMERGENCY]],
            ],
            [
                [$appContext, $slack, ['foo' => 'bar']],
            ],
        ];
    }

    /**
     * @phpstan-param ConstructorArgs $validConstructorArgs
     */
    #[DataProvider('providesValidConstructorArgs')]
    public function testIsInstantiable(array $validConstructorArgs): void
    {
        $something = new Logger(...$validConstructorArgs);

        $this->assertInstanceOf(Logger::class, $something);
    }

    public function testThrowsAnExceptionIfTheMinimumLogLevelDoesNotExist(): void
    {
        $this->expectException(PsrLogInvalidArgumentException::class);
        $this->expectExceptionMessage('The log-level, `"non_existent_level"`, does not exist');

        new Logger(self::createAppContext(), self::createSlack(), [
            'minLogLevel' => 'non_existent_level',
        ]);
    }

    /** @return array<mixed[]> */
    public static function providesValidCreateArgs(): array
    {
        return [
            [
                ['Test App', 'https://example.com/'],
            ],
            [
                ['Test App', 'https://example.com/', []],
            ],
            [
                ['Test App', 'https://example.com/', ['minLogLevel' => LogLevel::EMERGENCY]],
            ],
            [
                ['Test App', 'https://example.com/', ['foo' => 'bar']],
            ],
        ];
    }

    /**
     * @phpstan-param CreateArgs $validCreateArgs
     */
    #[DataProvider('providesValidCreateArgs')]
    public function testCreateReturnsANewInstance(array $validCreateArgs): void
    {
        $something = Logger::create(...$validCreateArgs);

        $this->assertInstanceOf(Logger::class, $something);
    }

    /** @return array<mixed[]> */
    public static function providesInvalidCreateArgs(): array
    {
        return [
            [
                InvalidArgumentException::class,
                'The webhook URL is invalid',
                ['Test App', 'foo'],
            ],
            [
                InvalidArgumentException::class,
                'The webhook URL is invalid',
                ['Test App', '://foo.bar'],
            ],
            [
                InvalidArgumentException::class,
                'The webhook URL is invalid',
                ['Test App', '//foo.bar'],
            ],
            [
                InvalidArgumentException::class,
                'The webhook URL is invalid',
                ['Test App', 'foo.bar/'],
            ],
            [
                PsrLogInvalidArgumentException::class,
                'The log-level, `"non_existent_level"`, does not exist',
                ['Test App', 'https://example.com/', ['minLogLevel' => 'non_existent_level']],
            ],
        ];
    }

    /**
     * @phpstan-param class-string<\Exception> $expectedExceptionClassName
     * @param mixed[] $invalidCreateArgs
     */
    #[DataProvider('providesInvalidCreateArgs')]
    public function testCreateThrowsAnExceptionIfOneOrMoreArgsAreInvalid(
        string $expectedExceptionClassName,
        string $expectedExceptionMessage,
        array $invalidCreateArgs,
    ): void {
        $this->expectException($expectedExceptionClassName);
        $this->expectExceptionMessage($expectedExceptionMessage);

        /** @phpstan-ignore-next-line */
        Logger::create(...$invalidCreateArgs);
    }

    public function testLogThrowsAnExceptionIfTheLogLevelDoesNotExist(): void
    {
        $this->expectException(PsrLogInvalidArgumentException::class);
        $this->expectExceptionMessage('The log-level, `"non_existent_level"`, does not exist');

        (new Logger(self::createAppContext(), self::createSlack()))
            ->log('non_existent_level', 'Hello, World!')
        ;
    }

    /** @return array<mixed[]> */
    public static function providesSomething(): array
    {
        $defaultAppContext = self::createAppContext();

        return [
            [
                'timesSendmessageCalled' => 'never',
                'logArgs' => [LogLevel::DEBUG, 'Ignored'],
                'createMessageArgs' => [LogLevel::DEBUG, 'Ignored', ['appContext' => $defaultAppContext]],
                'minLogLevel' => LogLevel::ERROR,
            ],
            [
                'timesSendmessageCalled' => 'never',
                'logArgs' => [LogLevel::INFO, 'Ignored'],
                'createMessageArgs' => [LogLevel::INFO, 'Ignored', ['appContext' => $defaultAppContext]],
                'minLogLevel' => LogLevel::ERROR,
            ],
            [
                'timesSendmessageCalled' => 'never',
                'logArgs' => [LogLevel::NOTICE, 'Ignored'],
                'createMessageArgs' => [LogLevel::NOTICE, 'Ignored', ['appContext' => $defaultAppContext]],
                'minLogLevel' => LogLevel::ERROR,
            ],
            [
                'timesSendmessageCalled' => 'never',
                'logArgs' => [LogLevel::WARNING, 'Ignored'],
                'createMessageArgs' => [LogLevel::WARNING, 'Ignored', ['appContext' => $defaultAppContext]],
                'minLogLevel' => LogLevel::ERROR,
            ],
            // #4
            [
                'timesSendmessageCalled' => 'once',
                'logArgs' => [LogLevel::ERROR, 'Sent'],
                'createMessageArgs' => [LogLevel::ERROR, 'Sent', ['appContext' => $defaultAppContext]],
                'minLogLevel' => LogLevel::ERROR,
            ],
            [
                'timesSendmessageCalled' => 'once',
                'logArgs' => [LogLevel::CRITICAL, 'Sent'],
                'createMessageArgs' => [LogLevel::CRITICAL, 'Sent', ['appContext' => $defaultAppContext]],
                'minLogLevel' => LogLevel::ERROR,
            ],
            [
                'timesSendmessageCalled' => 'once',
                'logArgs' => [LogLevel::ALERT, 'Sent'],
                'createMessageArgs' => [LogLevel::ALERT, 'Sent', ['appContext' => $defaultAppContext]],
                'minLogLevel' => LogLevel::ERROR,
            ],
            [
                'timesSendmessageCalled' => 'once',
                'logArgs' => [LogLevel::EMERGENCY, 'Sent'],
                'createMessageArgs' => [LogLevel::EMERGENCY, 'Sent', ['appContext' => $defaultAppContext]],
                'minLogLevel' => LogLevel::ERROR,
            ],
        ];
    }

    /**
     * @phpstan-param CreateMessageArgs $createMessageArgs
     * @phpstan-param LogArgs $logArgs
     */
    #[DataProvider('providesSomething')]
    public function testLogSendsAMessageIfTheLogLevelIsHighEnough(
        string $timesSendmessageCalled,
        string $minLogLevel,
        array $createMessageArgs,
        array $logArgs,
    ): void {
        $mockSlack = $this
            ->getMockBuilder(Slack::class)
            ->onlyMethods([
                'createMessage',
                'sendMessage',
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $pretendMessage = [];

        $mockSlack
            ->expects($this->{$timesSendmessageCalled}())
            ->method('createMessage')
            ->with(...$createMessageArgs)
            ->willReturn($pretendMessage)
        ;

        $mockSlack
            ->expects($this->{$timesSendmessageCalled}())
            ->method('sendMessage')
            ->with($pretendMessage)
        ;

        (new Logger(self::createAppContext(), $mockSlack, ['minLogLevel' => $minLogLevel]))
            ->log(...$logArgs)
        ;
    }
}
