<?php
/**
 * @noinspection PhpParamsInspection
 */

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Unit\Concern;

use Atymic\Twitter\Concern\SampledStream;
use Exception;

final class SampledStreamTest extends ConcernTestCase
{
    /**
     * @throws Exception
     */
    public function testSampledStream(): void
    {
        $onTweet = fn () => true;
        $params = [];

        $this->querier->getStream('tweets/sample/stream', $onTweet, $params)
            ->shouldBeCalledTimes(1);

        $this->subject->getSampledStream($onTweet, $params);
    }

    protected function getTraitName(): string
    {
        return SampledStream::class;
    }
}
