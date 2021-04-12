<?php

declare(strict_types=1);

namespace Atymic\Twitter\Concern;

use Atymic\Twitter\Contract\Querier;
use Atymic\Twitter\Twitter;

trait ApiV2Behavior
{
    abstract protected function getQuerier(): Querier;

    protected function implodeParamValues(array $paramValues): string
    {
        return implode(',', $paramValues);
    }

    /**
     * Apply default settings for API v2 queries.
     */
    protected function withDefaultParams(array $additionalParams = []): array
    {
        $defaults = [Twitter::KEY_RESPONSE_FORMAT => Twitter::RESPONSE_FORMAT_JSON];

        return array_merge($defaults, $additionalParams);
    }
}
