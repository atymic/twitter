<?php

declare(strict_types=1);

namespace Atymic\Twitter\ApiV1\Service;

use Atymic\Twitter\ApiV1\Contract\Twitter as TwitterContract;
use Atymic\Twitter\ApiV1\Traits\AccountTrait;
use Atymic\Twitter\ApiV1\Traits\AuthTrait;
use Atymic\Twitter\ApiV1\Traits\BlockTrait;
use Atymic\Twitter\ApiV1\Traits\DirectMessageTrait;
use Atymic\Twitter\ApiV1\Traits\FavoriteTrait;
use Atymic\Twitter\ApiV1\Traits\FormattingHelpers;
use Atymic\Twitter\ApiV1\Traits\FriendshipTrait;
use Atymic\Twitter\ApiV1\Traits\GeoTrait;
use Atymic\Twitter\ApiV1\Traits\HelpTrait;
use Atymic\Twitter\ApiV1\Traits\ListTrait;
use Atymic\Twitter\ApiV1\Traits\MediaTrait;
use Atymic\Twitter\ApiV1\Traits\SearchTrait;
use Atymic\Twitter\ApiV1\Traits\StatusTrait;
use Atymic\Twitter\ApiV1\Traits\TrendTrait;
use Atymic\Twitter\ApiV1\Traits\UserTrait;
use Atymic\Twitter\Contract\Configuration;
use Atymic\Twitter\Contract\Querier;
use Atymic\Twitter\Exception\ClientException as TwitterClientException;
use InvalidArgumentException;

class Twitter implements TwitterContract
{
    use FormattingHelpers;
    use AccountTrait;
    use BlockTrait;
    use DirectMessageTrait;
    use FavoriteTrait;
    use FriendshipTrait;
    use GeoTrait;
    use HelpTrait;
    use ListTrait;
    use MediaTrait;
    use SearchTrait;
    use StatusTrait;
    use TrendTrait;
    use UserTrait;
    use AuthTrait;

    private const DEFAULT_EXTENSION = 'json';
    private const URL_FORMAT = 'https://%s/%s/%s.%s';

    protected Configuration $config;
    protected Querier $querier;
    protected bool $debug;

    public function __construct(Querier $querier)
    {
        $config = $querier->getConfiguration();
        $this->config = $config;
        $this->querier = $querier;
        $this->debug = $config->isDebugMode();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function usingCredentials(
        string $accessToken,
        string $accessTokenSecret,
        ?string $consumerKey = null,
        ?string $consumerSecret = null
    ): self {
        return new self(
            $this->querier->usingCredentials($accessToken, $accessTokenSecret, $consumerKey, $consumerSecret)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function usingConfiguration(Configuration $configuration): self
    {
        return new self($this->querier->usingConfiguration($configuration));
    }

    /**
     * @return mixed
     * @throws TwitterClientException
     */
    public function query(
        string $endpoint,
        string $requestMethod = self::REQUEST_METHOD_GET,
        array $parameters = [],
        bool $multipart = false,
        string $extension = self::DEFAULT_EXTENSION
    ) {
        return $this->querier->query($endpoint, $requestMethod, $parameters, $multipart, $extension);
    }

    /**
     * @return mixed
     * @throws TwitterClientException
     */
    public function directQuery(
        string $url,
        string $requestMethod = self::REQUEST_METHOD_GET,
        array $parameters = []
    ) {
        return $this->querier->directQuery($url, $requestMethod, $parameters);
    }

    /**
     * @param array $parameters
     * @param bool $multipart
     * @param string $extension
     *
     * @return mixed|string
     * @throws TwitterClientException
     */
    public function get(string $endpoint, $parameters = [], $multipart = false, $extension = self::DEFAULT_EXTENSION)
    {
        return $this->query($endpoint, self::REQUEST_METHOD_GET, $parameters, $multipart, $extension);
    }

    /**
     * @return mixed
     * @throws TwitterClientException
     */
    public function post(string $endpoint, array $parameters = [], bool $multipart = false)
    {
        return $this->query($endpoint, self::REQUEST_METHOD_POST, $parameters, $multipart);
    }

    /**
     * @return mixed
     * @throws TwitterClientException
     */
    public function delete(string $endpoint, array $parameters = [])
    {
        return $this->query($endpoint, self::REQUEST_METHOD_DELETE, $parameters);
    }
}
