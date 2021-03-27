<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Contract\Querier;

trait ApiV2Behavior
{
    abstract protected function getQuerier(): Querier;

    abstract protected function implodeParamValues(array $paramValues): string;
}
