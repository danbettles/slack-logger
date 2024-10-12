<?php declare(strict_types=1);

namespace DanBettles\SlackLogger\Tests;

use DanBettles\SlackLogger\AppContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const null;

class AppContextTest extends TestCase
{
    /** @return array<mixed[]> */
    public static function providesSomething(): array
    {
        return [
            [
                'Foo App',
                'host.name',
                'foo.com',
                '/path/to/script',
                'POST',
                '/index.php?foo=bar',
                ['username' => 'qux'],
                'Mozilla/5.0',
                'https://www.google.co.uk/',
                new AppContext(
                    'Foo App',
                    'host.name',
                    [
                        'SERVER_NAME' => 'foo.com',
                        'SCRIPT_NAME' => '/path/to/script',
                        'REQUEST_METHOD' => 'POST',
                        'REQUEST_URI' => '/index.php?foo=bar',
                        'HTTP_USER_AGENT' => 'Mozilla/5.0',
                        'HTTP_REFERER' => 'https://www.google.co.uk/',
                    ],
                    ['username' => 'qux'],
                ),
            ],
            [
                'Foo App',
                'host.name',
                null,
                '/path/to/script',
                null,
                null,
                [],
                null,
                null,
                new AppContext(
                    'Foo App',
                    'host.name',
                    [
                        'SCRIPT_NAME' => '/path/to/script',
                    ],
                    [],
                ),
            ],
        ];
    }

    /**
     * @param array<string,string> $expectedRequest
     */
    #[DataProvider('providesSomething')]
    public function testIsInstantiable(
        string $expectedAppName,
        string $expectedHostname,
        string|null $expectedServerName,
        string $expectedScriptName,
        string|null $expectedRequestMethod,
        string|null $expectedRequestUri,
        array $expectedRequest,
        string|null $expectedUserAgent,
        string|null $expectedReferrer,
        AppContext $appContext,
    ): void {
        $this->assertSame($expectedAppName, $appContext->getAppName());
        $this->assertSame($expectedHostname, $appContext->getHostname());
        $this->assertSame($expectedServerName, $appContext->getServerName());
        $this->assertSame($expectedScriptName, $appContext->getScriptName());
        $this->assertSame($expectedRequestMethod, $appContext->getRequestMethod());
        $this->assertSame($expectedRequestUri, $appContext->getRequestUri());
        $this->assertSame($expectedRequest, $appContext->getRequest());
        $this->assertSame($expectedUserAgent, $appContext->getUserAgent());
        $this->assertSame($expectedReferrer, $appContext->getReferrer());
    }
}
