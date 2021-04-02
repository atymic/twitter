<?php

declare(strict_types=1);

namespace Atymic\Twitter\Http\Factory;

use React\EventLoop\LoopInterface;
use React\Http\Browser;

/**
 * @codeCoverageIgnore
 */
class BrowserCreator
{
    public function create(LoopInterface $loop): Browser
    {
        return new Browser($loop);
    }
}
