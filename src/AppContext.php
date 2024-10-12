<?php declare(strict_types=1);

namespace DanBettles\SlackLogger;

use const null;

/**
 * @phpstan-type ServerArray array{
 *   SERVER_NAME?: string,
 *   SCRIPT_NAME: string,
 *   REQUEST_METHOD?: string,
 *   REQUEST_URI?: string,
 *   HTTP_USER_AGENT?:string,
 *   HTTP_REFERER?:string,
 * }
 *
 * @phpstan-type RequestArray array<string,string|string[]>
 *
 * @todo Use `PHP_SAPI`?
 */
class AppContext
{
    /**
     * @phpstan-param ServerArray $server
     * @phpstan-param RequestArray $request
     */
    public function __construct(
        private string $appName,
        private string $hostname,
        private array $server,
        private array $request,  // (On the command line, `$_POST` is an empty array)
    ) {
    }

    public function getAppName(): string
    {
        return $this->appName;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getServerName(): string|null
    {
        return $this->server['SERVER_NAME'] ?? null;
    }

    public function getScriptName(): string
    {
        return $this->server['SCRIPT_NAME'];
    }

    public function getRequestMethod(): string|null
    {
        return $this->server['REQUEST_METHOD'] ?? null;
    }

    public function getRequestUri(): string|null
    {
        return $this->server['REQUEST_URI'] ?? null;
    }

    /**
     * @phpstan-return RequestArray
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    public function getUserAgent(): string|null
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }

    public function getReferrer(): string|null
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }
}
