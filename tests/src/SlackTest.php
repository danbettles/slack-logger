<?php declare(strict_types=1);

namespace DanBettles\SlackLogger\Tests;

use DanBettles\SlackLogger\AppContext;
use DanBettles\SlackLogger\Slack;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Stringable;

/**
 * @phpstan-import-type MessageArray from Slack
 * @phpstan-import-type ConstructorArgs from Slack
 * @phpstan-import-type CreateMessageArgs from Slack
 */
class SlackTest extends TestCase
{
    private const string VALID_BUT_IMPOTENT_WEBHOOK_URL = 'https://example.com/';

    /** @return array<mixed[]> */
    public static function providesValidConstructorArgs(): array
    {
        return [
            [
                [self::VALID_BUT_IMPOTENT_WEBHOOK_URL],
            ],
        ];
    }

    /**
     * @phpstan-param ConstructorArgs $args
     */
    #[DataProvider('providesValidConstructorArgs')]
    public function testIsInstantiable(array $args): void
    {
        $slack = new Slack(...$args);

        $this->assertInstanceOf(Slack::class, $slack);
    }

    /** @return array<mixed[]> */
    public static function providesInvalidWebhookUrls(): array
    {
        return [
            [
                'foo',
            ],
            [
                '://foo.bar',
            ],
            [
                '//foo.bar',
            ],
            [
                'foo.bar/',
            ],
        ];
    }

    #[DataProvider('providesInvalidWebhookUrls')]
    public function testThrowsAnExceptionIfTheWebhookUrlIsInvalid(string $invalidWebhookUrl): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The webhook URL is invalid');

        new Slack($invalidWebhookUrl);
    }

    /** @return array<mixed[]> */
    public static function providesMessageArrays(): array
    {
        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return "My class implements `\\Stringable`";
            }
        };

        $objectWithTostring = new class {
            public function __toString(): string
            {
                return "My class does not implement `\\Stringable` but does have `__toString()`";
            }
        };

        $plainObject = (object) [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $webAppContext = new AppContext('Webpage', 'host.name', [
            'SCRIPT_NAME' => '/path/to/script',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/index.php?foo=bar',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
            'HTTP_REFERER' => 'https://www.google.co.uk/',
        ], ['username' => 'qux']);

        $cliAppContext = new AppContext('Command-line script', 'host.name', [
            'SCRIPT_NAME' => '/path/to/script',
        ], []);

        return [
            [
                [
                    'blocks' => [
                        [
                            'type' => 'section',
                            'text' => [
                                'type' => 'mrkdwn',
                                'text' => '*:information_source: Debug*: Hello, World :-)',
                            ],
                        ],
                    ],
                ],
                [LogLevel::DEBUG, 'Hello, World :-)'],
            ],
            // #1
            [
                [
                    'blocks' => [
                        [
                            'type' => 'section',
                            'text' => [
                                'type' => 'mrkdwn',
                                'text' => '*:bangbang: Emergency*: Goodbye, cruel World :-(',
                            ],
                        ],
                        [
                            'type' => 'section',
                            'fields' => [
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*foo*\nbar",
                                ],
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*baz*\nqux",
                                ],
                            ],
                        ],
                    ],
                ],
                [LogLevel::EMERGENCY, 'Goodbye, cruel World :-(', [
                    'foo' => 'bar',
                    'baz' => 'qux',
                ]],
            ],
            [
                [
                    'blocks' => [
                        [
                            'type' => 'section',
                            'text' => [
                                'type' => 'mrkdwn',
                                'text' => '*:information_source: Info*: Objects that can be converted to a string',
                            ],
                        ],
                        [
                            'type' => 'section',
                            'fields' => [
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*Stringable*\nMy class implements `\\Stringable`",
                                ],
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*Object With `__toString()`*\nMy class does not implement `\\Stringable` but does have `__toString()`",
                                ],
                            ],
                        ],
                    ],
                ],
                [LogLevel::INFO, 'Objects that can be converted to a string', [
                    'Stringable' => $stringable,
                    'Object With `__toString()`' => $objectWithTostring,
                ]],
            ],
            [
                [
                    'blocks' => [
                        [
                            'type' => 'section',
                            'text' => [
                                'type' => 'mrkdwn',
                                'text' => '*:information_source: Info*: A plain object',
                            ],
                        ],
                        [
                            'type' => 'section',
                            'fields' => [
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*Dump*\n```stdClass Object\n(\n    [foo] => bar\n    [baz] => qux\n)\n```",
                                ],
                            ],
                        ],
                    ],
                ],
                [LogLevel::INFO, 'A plain object', [
                    'Dump' => $plainObject,
                ]],
            ],
            [
                [
                    'blocks' => [
                        [
                            'type' => 'divider',
                        ],
                        [
                            'type' => 'context',
                            'elements' => [
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*App*: Webpage",
                                ],
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*Hostname*: host.name",
                                ],
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*Script*: /path/to/script",
                                ],
                            ],
                        ],
                        [
                            'type' => 'section',
                            'text' => [
                                'type' => 'mrkdwn',
                                'text' => '*:information_source: Info*: With app-context',
                            ],
                        ],
                        [
                            'type' => 'section',
                            'fields' => [
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*Request URI*\n/index.php?foo=bar",
                                ],
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*Request Method*\nPOST",
                                ],
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*`\$_POST`*\n```Array\n(\n    [username] => qux\n)\n```",
                                ],
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*User Agent*\nMozilla/5.0",
                                ],
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*Referrer*\nhttps://www.google.co.uk/",
                                ],
                            ],
                        ],
                    ],
                ],
                [LogLevel::INFO, 'With app-context', [
                    'appContext' => $webAppContext,
                ]],
            ],
            [
                [
                    'blocks' => [
                        [
                            'type' => 'divider',
                        ],
                        [
                            'type' => 'context',
                            'elements' => [
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*App*: Command-line script",
                                ],
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*Hostname*: host.name",
                                ],
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*Script*: /path/to/script",
                                ],
                            ],
                        ],
                        [
                            'type' => 'section',
                            'text' => [
                                'type' => 'mrkdwn',
                                'text' => '*:information_source: Info*: With app-context',
                            ],
                        ],
                    ],
                ],
                [LogLevel::INFO, 'With app-context', [
                    'appContext' => $cliAppContext,
                ]],
            ],
            [
                [
                    'blocks' => [
                        [
                            'type' => 'section',
                            'text' => [
                                'type' => 'mrkdwn',
                                'text' => "*:information_source: Info*: With a `appContext` context element that isn't an `AppContext` object",
                            ],
                        ],
                        [
                            'type' => 'section',
                            'fields' => [
                                [
                                    'type' => 'mrkdwn',
                                    'text' => "*appContext*\nsomething",
                                ],
                            ],
                        ],
                    ],
                ],
                [LogLevel::INFO, "With a `appContext` context element that isn't an `AppContext` object", [
                    'appContext' => 'something',
                ]],
            ],
        ];
    }

    /**
     * @phpstan-param MessageArray $expectedMessage
     * @phpstan-param CreateMessageArgs $createMessageArgs
     */
    #[DataProvider('providesMessageArrays')]
    public function testCreatemessageCreatesAMessageArray(
        array $expectedMessage,
        array $createMessageArgs,
    ): void {
        $slack = new Slack(self::VALID_BUT_IMPOTENT_WEBHOOK_URL);

        $this->assertEquals(
            $expectedMessage,
            $slack->createMessage(...$createMessageArgs),
        );
    }
}
