<?php declare(strict_types=1);

namespace DanBettles\SlackLogger;

/**
 * @internal
 */
interface Arrayable
{
    /**
     * @return mixed[]
     */
    public function toArray(): array;
}
